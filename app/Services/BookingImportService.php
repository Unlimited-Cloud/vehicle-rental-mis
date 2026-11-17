<?php

namespace App\Services;

use App\Imports\BookingImport;
use App\Models\CrewProfile;
use App\Models\Customer;
use App\Models\Itinerary;
use App\Models\Payment;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class BookingImportService
{
    const VAT_RATE = 0.13;

    const STATUSES        = ['confirmed', 'pending', 'cancelled'];
    const PAYMENT_METHODS = ['cash', 'bank_transfer', 'card', 'online', 'cheque'];

    private $cache = [];

    /**
     * @param int|null $forcedCustomerId  set for customer-role users (their own customer id)
     * @param int|null $ownerId           set for vehicle-owner users (limits vehicles)
     */
    public function run(BookingImport $data, $forcedCustomerId = null, $ownerId = null): array
    {
        $result = ['created' => 0, 'skipped' => [], 'errors' => [], 'total' => 0];

        $itinByRef = $data->itineraries->groupBy(function ($r) {
            return $this->str($r['ref'] ?? null);
        }, true);

        $seenRefs = [];

        foreach ($data->bookings as $idx => $row) {
            $line = $idx + 2;
            $ref  = $this->str($row['ref'] ?? null);

            // sample rows from the template are ignored
            if ($ref !== '' && Str::startsWith(strtoupper($ref), 'EXAMPLE')) {
                continue;
            }

            $result['total']++;
            $label = "Row {$line}" . ($ref !== '' ? " (ref {$ref})" : '');

            try {
                if ($ref === '') {
                    throw new \InvalidArgumentException('ref is required (it links the booking to its itinerary rows).');
                }
                if (isset($seenRefs[$ref])) {
                    throw new \InvalidArgumentException('duplicate ref in the Bookings sheet.');
                }
                $seenRefs[$ref] = true;

                $status = null;
                DB::transaction(function () use ($row, $itinByRef, $ref, $forcedCustomerId, $ownerId, &$status) {
                    $status = $this->importRow($row, $itinByRef->get($ref, collect()), $forcedCustomerId, $ownerId);
                });

                if ($status === 'created') {
                    $result['created']++;
                } else {
                    $result['skipped'][] = "{$label}: {$status}";
                }
            } catch (\InvalidArgumentException $e) {
                $result['errors'][] = "{$label}: " . $e->getMessage();
            } catch (\Throwable $e) {
                report($e);
                $result['errors'][] = "{$label}: unexpected error - " . $e->getMessage();
            }
        }

        // itinerary rows pointing to a ref that is not on the Bookings sheet
        foreach ($itinByRef->keys() as $ref) {
            if ($ref === '' || Str::startsWith(strtoupper($ref), 'EXAMPLE')) {
                continue;
            }
            if (!isset($seenRefs[$ref])) {
                $result['errors'][] = "Itineraries sheet: ref \"{$ref}\" does not match any row on the Bookings sheet.";
            }
        }

        return $result;
    }

    /** @return string 'created' or a skip reason; throws InvalidArgumentException on bad data */
    private function importRow($row, $itinRows, $forcedCustomerId, $ownerId): string
    {
        // ---------- required lookups ----------
        if ($forcedCustomerId) {
            $customerId = $forcedCustomerId;
        } else {
            $customerKey = $this->str($row['customer'] ?? null);
            if ($customerKey === '') {
                throw new \InvalidArgumentException('customer is required.');
            }
            $customer = $this->findCustomer($customerKey);
            if (!$customer) {
                throw new \InvalidArgumentException("customer \"{$customerKey}\" not found (use name, email or phone of an existing customer).");
            }
            $customerId = $customer->id;
        }

        $vehicleKey = $this->str($row['vehicle'] ?? null);
        if ($vehicleKey === '') {
            throw new \InvalidArgumentException('vehicle is required.');
        }
        $vehicle = $this->findVehicle($vehicleKey, $ownerId);
        if (!$vehicle) {
            throw new \InvalidArgumentException("vehicle \"{$vehicleKey}\" not found (use the vehicle name).");
        }

        $driverId = $this->findCrew($this->str($row['driver'] ?? null), 'driver');
        $helperId = $this->findCrew($this->str($row['helper'] ?? null), 'helper');

        // ---------- dates ----------
        $startDate = $this->parseDate($row['start_date'] ?? null, 'start_date', true);
        $startTime = $this->parseTime($row['start_time'] ?? null, 'start_time', true);
        $endDate   = $this->parseDate($row['end_date'] ?? null, 'end_date', true);
        $endTime   = $this->parseTime($row['end_time'] ?? null, 'end_time', false);

        if ($endDate < $startDate) {
            throw new \InvalidArgumentException('end_date is before start_date.');
        }
        $start   = Carbon::parse("{$startDate} {$startTime}:00");
        $end     = Carbon::parse("{$endDate} " . ($endTime ?: '23:59') . ':00');
        $noHours = $end->greaterThan($start) ? (int) $start->diffInHours($end) : 0;

        // ---------- simple fields ----------
        $status = strtolower($this->str($row['status'] ?? null)) ?: 'pending';
        if (!in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException('status must be one of: ' . implode(', ', self::STATUSES) . '.');
        }

        $priceType = strtolower($this->str($row['pricetype'] ?? null)) ?: 'dynamic';
        if (!in_array($priceType, ['dynamic', 'static'], true)) {
            throw new \InvalidArgumentException('pricetype must be dynamic or static.');
        }

        $paymentMethod = strtolower($this->str($row['payment_method'] ?? null));
        if ($paymentMethod !== '' && !in_array($paymentMethod, self::PAYMENT_METHODS, true)) {
            throw new \InvalidArgumentException('payment_method must be one of: ' . implode(', ', self::PAYMENT_METHODS) . '.');
        }

        $fileNo = $this->str($row['file_no'] ?? null);

        // duplicate guard (only when a file_no is given)
        if ($fileNo !== '') {
            $dup = VehicleBooking::where('file_no', $fileNo)
                ->where('vehicle_id', $vehicle->id)
                ->where('customer_id', $customerId)
                ->where('start_date', $startDate)
                ->exists();
            if ($dup) {
                return 'skipped - a booking with the same file_no, vehicle, customer and start date already exists.';
            }
        }

        // ---------- itineraries + amount ----------
        $rates      = $this->vehicleRates($vehicle->vehicle_type);
        $itinInsert = [];
        $itinSum    = 0;
        $day        = 0;

        foreach ($itinRows as $iRow) {
            $day++;
            $km    = $this->num($iRow['est_km'] ?? null, 'est_km');
            $hrs   = $this->num($iRow['est_hours'] ?? null, 'est_hours');
            $night = $this->bool($iRow['overnight'] ?? null);
            $price = ($km * $rates['km']) + ($hrs * $rates['hr']) + ($night ? $rates['night'] : 0);
            $itinSum += $price;

            $itinInsert[] = [
                'day_number'       => $day,
                'itinerary_date'   => $this->parseDate($iRow['itinerary_date'] ?? null, 'itinerary_date', false) ?: $startDate,
                'from_destination' => $this->str($iRow['from_destination'] ?? null) ?: null,
                'to_destination'   => $this->str($iRow['to_destination'] ?? null) ?: null,
                'est_km'           => $km,
                'est_hours'        => $hrs,
                'is_overnight'     => $night,
                'per_km_rate'      => $rates['km'],
                'per_hour_rate'    => $rates['hr'],
                'overnight_charge' => $night ? $rates['night'] : 0,
                'est_price'        => round($price, 2),
                'notes'            => $this->str($iRow['notes'] ?? null) ?: null,
            ];
        }

        $typedAmount = $this->num($row['amount'] ?? null, 'amount');

        if ($priceType === 'dynamic') {
            if (count($itinInsert) > 0) {
                $amount = round($itinSum, 2);
            } elseif ($typedAmount > 0) {
                $amount = $typedAmount;   // same fallback as resolvePricing()
            } else {
                throw new \InvalidArgumentException('pricetype is dynamic but there are no itinerary rows and no amount.');
            }
        } else {
            $amount = $typedAmount;       // static: the typed amount is the price
        }

        // ---------- discount, VAT, totals (same maths as the form) ----------
        $discountType = strtolower($this->str($row['discount_type'] ?? null));
        $discountType = in_array($discountType, ['percentage', 'percent', '%'], true) ? 'percentage' : 'amount';
        $discount     = $this->num($row['discount'] ?? null, 'discount');

        $discountAmount = 0;
        if ($discount > 0) {
            $discountAmount = $discountType === 'percentage' ? $amount * $discount / 100 : $discount;
        }
        $afterDiscount = max(0, $amount - $discountAmount);
        $applyVat      = $this->bool($row['vat'] ?? null);
        $vatAmount     = ($applyVat && $afterDiscount > 0) ? $afterDiscount * self::VAT_RATE : 0;
        $total         = round($afterDiscount + $vatAmount, 2);

        // ---------- payment ----------
        $paid = $this->num($row['paid_amount'] ?? null, 'paid_amount');
        $payStatusRaw = strtolower($this->str($row['payment_status'] ?? null));
        $map = ['pending' => 0, '0' => 0, 'paid' => 1, '1' => 1, 'partial' => 2, '2' => 2];
        if ($payStatusRaw === '') {
            $paymentStatus = $paid <= 0 ? 0 : ($paid >= $total ? 1 : 2);
        } elseif (isset($map[$payStatusRaw])) {
            $paymentStatus = $map[$payStatusRaw];
        } else {
            throw new \InvalidArgumentException('payment_status must be pending, paid or partial.');
        }
        $paymentDate = $this->parseDate($row['payment_date'] ?? null, 'payment_date', false) ?: now()->format('Y-m-d');

        // ---------- save ----------
        $booking = VehicleBooking::create([
            'customer_id'         => $customerId,
            'vehicle_id'          => $vehicle->id,
            'driver_id'           => $driverId,
            'helper_id'           => $helperId,
            'passenger'           => $this->str($row['passenger'] ?? null) ?: null,
            'file_no'             => $fileNo ?: null,
            'signage_information' => $this->str($row['signage_information'] ?? null) ?: null,

            'from_destination'    => $this->str($row['from_destination'] ?? null) ?: null,
            'to_destination'      => $this->str($row['to_destination'] ?? null) ?: null,
            'no_of_people'        => $this->str($row['no_of_people'] ?? null) !== '' ? (int) $row['no_of_people'] : null,
            'start_date'          => $startDate,
            'end_date'            => $endDate,
            'start_time'          => $startTime,
            'end_time'            => $endTime ?: null,
            'no_of_hours'         => $noHours,

            'pricetype'           => $priceType,
            'rate_per_day'        => $amount,
            'sub_total'           => $amount,
            'discount_amount_type' => $discountType,
            'discount'            => $discount,
            'vat'                 => $applyVat ? 1 : 0,
            'tax_amount_type'     => 'percentage',
            'tax'                 => round($vatAmount, 2),
            'total_amount'        => $total,
            'remaining_balance'   => round($total - $paid, 2),

            'paid_amount'         => $paid,
            'payment_method'      => $paymentMethod ?: null,
            'payment_status'      => $paymentStatus,

            'notes'               => $this->str($row['notes'] ?? null) ?: null,
            'status'              => $status,
        ]);

        if ($paid > 0) {
            Payment::create([
                'vehicle_booking_id'    => $booking->id,
                'amount'                => $paid,
                'payment_method'        => $paymentMethod ?: 'cash',
                'transaction_reference' => (string) Str::uuid(),
                'payment_date'          => $paymentDate . ' ' . now()->format('H:i:s'),
                'notes'                 => $this->str($row['payment_note'] ?? null) ?: null,
            ]);
        }

        if (!empty($itinInsert)) {
            $now  = now();
            $rows = array_map(function ($i) use ($booking, $now) {
                return $i + [
                    'booking_id' => $booking->id,
                    'file_no'    => $booking->file_no,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $itinInsert);
            Itinerary::insert($rows);
        }

        return 'created';
    }

    // ======================= lookups =======================

    private function findCustomer(string $key)
    {
        if (!array_key_exists($key, $this->cache['customer'] ?? [])) {
            $this->cache['customer'][$key] = Customer::where('name', $key)
                ->orWhere('email', $key)
                ->orWhere('phone', $key)
                ->first();
        }
        return $this->cache['customer'][$key];
    }

    private function findVehicle(string $key, $ownerId)
    {
        if (!array_key_exists($key, $this->cache['vehicle'] ?? [])) {
            $q = Vehicle::query()->where(function ($q) use ($key) {
                $q->where('vehicle_name', $key);
                if (is_numeric($key)) {
                    $q->orWhere('id', $key);
                }
            });
            if ($ownerId) {
                $q->where('vehicle_owner_id', $ownerId);
            }
            $this->cache['vehicle'][$key] = $q->first();
        }
        return $this->cache['vehicle'][$key];
    }

    private function findCrew(string $name, string $role)
    {
        if ($name === '') {
            return null;
        }
        $k = $role . '|' . $name;
        if (!array_key_exists($k, $this->cache['crew'] ?? [])) {
            $this->cache['crew'][$k] = CrewProfile::whereHas('user', function ($q) use ($name, $role) {
                $q->where('role', $role)->where('name', $name);
            })->first();
        }
        $crew = $this->cache['crew'][$k];
        if (!$crew) {
            throw new \InvalidArgumentException("{$role} \"{$name}\" not found.");
        }
        return $crew->id;
    }

    private function vehicleRates($type): array
    {
        $type = (string) $type;
        if (!isset($this->cache['rates'][$type])) {
            $r = DB::table('trip_route_vehicle_type_prices')->where('vehicle_type', $type)->first();
            $this->cache['rates'][$type] = [
                'km'    => (float) ($r->per_km ?? 0),
                'hr'    => (float) ($r->per_hour ?? 0),
                'night' => (float) ($r->overnight_price ?? 0),
            ];
        }
        return $this->cache['rates'][$type];
    }

    // ======================= parsers =======================

    private function str($v): string
    {
        return $v === null ? '' : trim((string) $v);
    }

    private function num($v, string $label): float
    {
        $s = str_replace(',', '', $this->str($v));
        if ($s === '') {
            return 0.0;
        }
        if (!is_numeric($s)) {
            throw new \InvalidArgumentException("{$label} must be a number.");
        }
        return (float) $s;
    }

    private function bool($v): bool
    {
        return in_array(strtolower($this->str($v)), ['1', 'yes', 'y', 'true'], true);
    }

    private function parseDate($v, string $label, bool $required): ?string
    {
        $s = $this->str($v);
        if ($s === '') {
            if ($required) {
                throw new \InvalidArgumentException("{$label} is required.");
            }
            return null;
        }
        try {
            if (is_numeric($s)) {   // Excel serial date
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $s))->format('Y-m-d');
            }
            return Carbon::parse($s)->format('Y-m-d');
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException("{$label} \"{$s}\" is not a valid date (use YYYY-MM-DD).");
        }
    }

    private function parseTime($v, string $label, bool $required): ?string
    {
        $s = $this->str($v);
        if ($s === '') {
            if ($required) {
                throw new \InvalidArgumentException("{$label} is required.");
            }
            return null;
        }
        try {
            if (is_numeric($s)) {   // Excel time = fraction of a day
                $secs = (int) round(((float) $s - floor((float) $s)) * 86400);
                return gmdate('H:i', $secs);
            }
            return Carbon::parse($s)->format('H:i');
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException("{$label} \"{$s}\" is not a valid time (use HH:MM, 24-hour).");
        }
    }
}
