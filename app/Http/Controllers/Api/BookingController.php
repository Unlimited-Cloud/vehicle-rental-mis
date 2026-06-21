<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TripCategory;
use App\Models\TripRoute;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\ProformaService;
use App\Imports\VehicleBookingImport;
use App\Models\Customer;
use App\Models\Review;


use Maatwebsite\Excel\Facades\Excel;
use App\Events\EmailEvent;
use App\Models\VehicleReceipt;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use App\Helpers\NepaliDateHelper;
use App\Models\Brand;
use App\Models\District;
use App\Models\EstimateBill;
use App\Models\FuelType;
use App\Models\ProformaInvoice;
use App\Models\Province;
use App\Models\Seater;
use App\Models\Splashscreen;
use App\Models\VDC;
use App\Models\VehicleAssignment;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use App\Models\BasicTable;
use App\Models\ContactUs;
use App\Models\PaymentMode;
use App\Models\CustomerLocation;
use App\Models\Payment;
use App\Models\Passenger;


use Illuminate\Support\Facades\Http;

class BookingController extends Controller
{
    protected $service;

    public function __construct(ProformaService $service)
    {
        $this->service = $service;
    }
    public function GetVehicle()
    {
        $vehicles = Vehicle::with([
            'vehicleDetail',
            'fuelPurchases',
            'permits',
            'services',
            'repairs',
            'tyreChanges'
        ])->withAvg('reviews', 'rating')->where('status', 1)->get();

        return response()->json([
            'status' => true,
            'message' => 'Vehicle list fetched successfully',
            'data' => $vehicles
        ]);
    }

    public function getDrivers()
    {
        $helpers = DB::table('crew_profiles')
            ->join('users', 'crew_profiles.user_id', '=', 'users.id')
            ->where('crew_profiles.role', 'driver')
            ->select(
                'crew_profiles.id as crew_profile_id',
                'crew_profiles.role',
                'users.id as user_id',
                'users.name',
                'users.email'
            )
            ->get();

        return response()->json([
            'success' => true,
            'data' => $helpers
        ]);
    }

    public function getHelpers()
    {
        $helpers = DB::table('crew_profiles')
            ->join('users', 'crew_profiles.user_id', '=', 'users.id')
            ->where('crew_profiles.role', 'helper')
            ->select(
                'crew_profiles.id as crew_profile_id',
                'crew_profiles.role',
                'users.id as user_id',
                'users.name',
                'users.email'
            )
            ->get();

        return response()->json([
            'success' => true,
            'data' => $helpers
        ]);
    }

    public function getCustomerBookings($customerUUId)
    {
        $customers = Customer::where('customer_uuid', $customerUUId)->first();
        $customerId = $customers->id;
        $bookings = VehicleBooking::where('customer_id', $customerId)
            ->with([
                'vehicle',
                'customer',
                'driver.user',
                'helper.user',
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    public function tripcategory()
    {
        $category = TripCategory::where('status', 1)->whereNull('deleted_at')->get();

        return response()->json([
            'status' => true,
            'message' => 'Trip category fetched successfully',
            'data' => $category
        ]);
    }

    public function tripRoutes($category_id)
    {
        $routes = TripRoute::with('category')->whereNull('deleted_at')->where('trip_category_id', $category_id)->get();

        return response()->json([
            'status' => true,
            'message' => 'Trip routes fetched successfully',
            'data' => $routes
        ]);
    }



    public function createBooking(Request $request)
    {
        try {
            Log::info("Vehicle Bokking Request", ["body" => $request->all()]);
            //  Validation
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,customer_uuid',
                'vehicle_id' => 'required|exists:vehicles,id',
                'driver_id' => 'nullable|exists:crew_profiles,id',
                'trip_category_id' => 'required|exists:trip_categories,id',
                'trip_route_id' => 'required|exists:trip_routes,id',

                'start_datetime' => 'required|date|after_or_equal:now',
                'end_datetime' => 'required|date|after:start_datetime',

                'discount_amount_type' => 'nullable|in:flat,percent',
                'discount' => 'nullable|numeric|min:0',

                'from_destination' => 'nullable|string',
                'to_destination' => 'nullable|string',
                'notes' => 'nullable|string',
                'no_of_people' => 'nullable|string',
                'signage_information' => 'nullable|string',

                'contact_person' => 'nullable|string',
                'contact_email' => 'nullable|string',
                'contact_number' => 'nullable|string',
                'agent_code' => 'nullable|exists:agents,agent_code',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            //  Parse DateTime objects
            $startDateTime = \Carbon\Carbon::parse($request->start_datetime);
            $endDateTime = \Carbon\Carbon::parse($request->end_datetime);

            // Apply buffer for overlap (30 mins)
            $bufferMinutes = 30;
            $startWithBuffer = $startDateTime->copy()->subMinutes($bufferMinutes);
            $endWithBuffer = $endDateTime->copy()->addMinutes($bufferMinutes);

            //  Prevent Double Booking
            $conflict = VehicleBooking::where('vehicle_id', $request->vehicle_id)
                ->where('status', '!=', 'cancelled')
                ->whereRaw("
                CONCAT(start_date, ' ', start_time) <= ?
                AND CONCAT(end_date, ' ', end_time) >= ?
            ", [
                    $endWithBuffer->format('Y-m-d H:i'),
                    $startWithBuffer->format('Y-m-d H:i')
                ])
                ->exists();

            if ($conflict) {
                return response()->json([
                    'status' => false,
                    'message' => 'Vehicle is already booked for the selected time range'
                ], 409);
            }

            // Get Vehicle & TripRoute
            $vehicle = Vehicle::findOrFail($request->vehicle_id);
            $tripRoute = TripRoute::findOrFail($request->trip_route_id);

            if (
                $request->filled('no_of_people') &&
                (int) $request->no_of_people > (int) $vehicle->seater
            ) {
                return response()->json([
                    'status' => false,
                    'message' => "Selected vehicle capacity is {$vehicle->seater} passengers. You entered {$request->no_of_people} passengers."
                ], 422);
            }

            $vehicle_type = strtolower($vehicle->vehicle_type);
            $rate_field = $vehicle_type . '_price';

            if (!isset($tripRoute->$rate_field)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Rate not defined for this vehicle type'
                ], 400);
            }

            $rate_per_day = $tripRoute->$rate_field;

            // Calculate number of days (include start day)
            $days = $startDateTime->copy()->startOfDay()
                ->diffInDays($endDateTime->copy()->startOfDay()) + 1;

            // Calculate pricing
            $sub_total = $rate_per_day * $days;
            $discount_amount = 0;

            if ($request->discount && $request->discount_amount_type) {
                if ($request->discount_amount_type === 'flat') {
                    $discount_amount = $request->discount;
                } elseif ($request->discount_amount_type === 'percent') {
                    $discount_amount = ($sub_total * $request->discount) / 100;
                }
            }

            // Taxable amount after discount
            $taxable_amount = max(0, $sub_total - $discount_amount);

            // VAT calculation (13%)
            $tax_amount = 0;
            $tax_amount_type = null;


            $tax_amount_type = 'percentage';
            $tax_amount = ($taxable_amount * 13) / 100;

            // Final total
            $total_amount = $taxable_amount + $tax_amount;

            // Extract separate date & time for DB
            $start_date = $startDateTime->format('Y-m-d');
            $start_time = $startDateTime->format('H:i');
            $end_date = $endDateTime->format('Y-m-d');
            $end_time = $endDateTime->format('H:i');

            $customers = Customer::where('customer_uuid', $request->customer_id)->first();
            $customerId = $customers->id;

            $customerName = strtoupper(
                preg_replace('/[^A-Za-z0-9]/', '', $customers->name)
            );

            $customerName = substr($customerName, 0, 5);
            $file_no = 'FILE-' .
                $customerName . '-' .
                $startDateTime->format('Ymd') . '-' .
                strtoupper(Str::random(4));

            $driver_id = $request->driver_id ?? null;

            Log::info("Vehicle Bokking Request Start end", ["start_time" => $start_time, "end_time" => $end_time, "driver_id" => $driver_id]);


            //  Create booking
            $booking = VehicleBooking::create([
                'customer_id' => $customerId,
                'vehicle_id' => $request->vehicle_id,
                'trip_category_id' => $request->trip_category_id,
                'trip_route_id' => $request->trip_route_id,
                'driver_id' => $driver_id,

                'start_date' => $start_date,
                'start_time' => $start_time,
                'end_date' => $end_date,
                'end_time' => $end_time,

                'from_destination' => $request->from_destination,
                'to_destination' => $request->to_destination,
                'notes' => $request->notes,
                'no_of_people' => $request->no_of_people,
                'signage_information' => $request->signage_information,

                'rate_per_day' => $rate_per_day,
                'sub_total' => $sub_total,
                'discount' => $discount_amount,
                'tax_amount_type' => $tax_amount_type,
                'tax' => $tax_amount,
                'vat' => '1',
                'total_amount' => $total_amount,


                'status' => 'pending',
                'call_type' => 'api',

                'contact_person' => $request->contact_person,
                'contact_email' => $request->contact_email,
                'contact_number' => $request->contact_number,
                'agent_code' => $request->agent_code ?? null,
                'file_no' => $file_no ?? null,
                'pickup_latitude' => $request->pickup_latitude ?? null,
                'pickup_longitude' => $request->pickup_longitude ?? null,
            ]);

            Passenger::create([
                'contact_person' => $request->contact_person ?? null,
                'contact_email' => $request->contact_email ?? null,
                'contact_number' => $request->contact_number ?? null,
                'customer_id' => $customerId,
                'booking_id' => $booking->id,
            ]);


            //  Generate Proforma
            // $this->service->generateFinalInvoice($file_no);
            event(new EmailEvent($customers->email, 'create_booking', 'success', 'customer'));

            // Return response
            return response()->json([
                'status' => true,
                'message' => 'Booking created successfully',
                'data' => $booking
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function importBooking(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv'
        ]);

        Excel::import(new VehicleBookingImport, $request->file('file'));

        return response()->json([
            'message' => 'Data imported successfully'
        ]);
    }


    public function apiGenerateInvoice(Request $request)
    {
        $request->validate([
            'file_no' => 'required|string',
            'invoice_due_date' => 'nullable|date',
            'download' => 'sometimes|boolean' // Optional: true to download, false to view in browser
        ]);

        $bookings = VehicleBooking::with(['vehicle', 'customer', 'tripRoute'])
            ->where('file_no', $request->file_no)
            ->where('status', 'confirmed')
            ->orderBy('start_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        if ($bookings->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No bookings found for this file number'
                ], 404);
            }
            abort(404, 'No bookings found');
        }

        $customer = optional($bookings->first())->customer;
        $sub_total = $bookings->sum('rate_per_day');
        $discount = $bookings->sum('discount');
        $tax = $bookings->sum('tax');
        $net_amount = $sub_total - $discount + $tax;

        // $receipt_number = $this->generateReceiptNumber();

        $existingReceipt = VehicleReceipt::where('file_no', $request->file_no)->first();
        $receipt_number = $existingReceipt
            ? $existingReceipt->receipt_number
            : $this->generateReceiptNumber();

        // Prepare data for view
        $data = [
            'receipt' => null, // We'll create after view if needed
            'bookings' => $bookings,
            'customer' => $customer,
            'file_no' => $request->file_no,
            'invoice_due_date' => $request->invoice_due_date ?? null,
            'invoice_date' => Carbon::now('Asia/Kathmandu')->format('m/d/Y'),
            'miti_date' => $this->convertToNepaliDate(now()),
            'amount_in_words' => $this->convertNumberToWords($net_amount),
            'items' => $this->prepareInvoiceItems($bookings),
            'sub_total' => $sub_total,
            'discount' => $discount,
            'tax' => $tax,
            'net_amount' => $net_amount,
            'vat_percentage' => 13,
            'receipt_number' => $receipt_number,
            'prepared_by' => auth()->user() ? auth()->user()->name : 'BIR',
            'company_name' => 'ASHIVANA VEHICLE SERVICE PVT.LTD.',
            'company_address' => 'Jwagal-10 Lalitpur, Nepal',
            'company_phone' => '602439925',
            'company_email' => 'e-account@ashivana.com.np',
            'printing_time' => Carbon::now('Asia/Kathmandu')->format('m/d/Y h:i:s A'),
        ];

        // Save receipt to database
        $receipt = VehicleReceipt::create([
            'vehicle_booking_id' => null,
            'vehicle_moment_id' => null,
            'vehicle_id' => null,
            'file_no' => $request->file_no,
            'customer_id' => $customer ? $customer->id : null,
            'receipt_number' => $receipt_number,
            'invoice_type' => 'vat',
            'rate_per_day' => $sub_total,
            'sub_total' => $sub_total,
            'discount' => $discount,
            'tax' => $tax,
            'total_amount' => $net_amount,
            'invoice_due_date' => $request->invoice_due_date ?? null,
        ]);

        $data['receipt'] = $receipt;

        // Generate PDF
        $pdf = PDF::loadView('layouts.admin.invoices.vehicle_invoice', $data);

        $pdf->setPaper('A4', 'portrait');

        /* Folder path */
        $folderPath = public_path('uploads/invoices');

        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0755, true);
        }

        $fileName = $receipt->receipt_number . '.pdf';
        $fullPath = $folderPath . '/' . $fileName;

        $pdf->save($fullPath);

        $receipt->update([
            'pdf_path' => 'uploads/invoices/' . $fileName
        ]);

        return response()->download($fullPath, $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Prepare invoice items from bookings
     */
    private function prepareInvoiceItems($bookings)
    {
        $items = [];
        foreach ($bookings as $index => $booking) {
            $routeName = $booking->tripRoute ? $booking->tripRoute->title : 'Transportation Service';
            $vehicleName = $booking->vehicle ? $booking->vehicle->vehicle_name : 'Vehicle';
            $date = $booking->start_date
                ? \Carbon\Carbon::parse($booking->start_date)->format('jS M Y')
                : '';

            $time = $booking->start_time
                ? \Carbon\Carbon::parse($booking->start_time)->format('h:i A')
                : '';

            // Get the actual service description from booking notes if available
            $description = "{$routeName} By {$vehicleName}";
            if ($date) {
                $description .= " on {$date}";
            }
            if ($time) {
                $description .= " at {$time}";
            }

            $items[] = [
                'sn' => $index + 1,
                'hs_code' => 'Transportation Services',
                'particular' => $description,
                'vehicle_name' => $vehicleName,
                'route_name' => $routeName,
                'date' => $date,
                'qty' => 1,
                'qty_type' => 'PAX',
                'rate' => $booking->rate_per_day,
                'amount' => $booking->rate_per_day,
            ];
        }
        return $items;
    }


    private function generateReceiptNumber()
    {
        $year = date('y');
        $month = date('m');
        $lastReceipt = VehicleReceipt::whereYear('created_at', date('Y'))
            ->orderBy('id', 'desc')
            ->first();

        if ($lastReceipt) {
            $lastNumber = intval(substr($lastReceipt->receipt_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "ASB-{$month}{$year}-{$newNumber}";
    }

    private function convertNumberToWords($number)
    {
        $number = round($number, 2);
        $decimal = round(($number - floor($number)) * 100);
        $number = floor($number);

        $words = $this->convertToWords($number);

        if ($decimal > 0) {
            $words .= " And {$this->convertToWords($decimal)} Palsa";
        } else {
            $words .= " Only";
        }

        return "Rs. " . ucwords($words);
    }

    private function convertToWords($number)
    {
        $ones = [
            0 => '',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen'
        ];

        $tens = [
            2 => 'Twenty',
            3 => 'Thirty',
            4 => 'Forty',
            5 => 'Fifty',
            6 => 'Sixty',
            7 => 'Seventy',
            8 => 'Eighty',
            9 => 'Ninety'
        ];

        if ($number < 20) {
            return $ones[$number];
        }

        if ($number < 100) {
            return $tens[floor($number / 10)] . ($number % 10 ? ' ' . $ones[$number % 10] : '');
        }

        if ($number < 1000) {
            return $ones[floor($number / 100)] . ' Hundred' . ($number % 100 ? ' ' . $this->convertToWords($number % 100) : '');
        }

        if ($number < 100000) {
            return $this->convertToWords(floor($number / 1000)) . ' Thousand' . ($number % 1000 ? ' ' . $this->convertToWords($number % 1000) : '');
        }

        if ($number < 10000000) {
            return $this->convertToWords(floor($number / 100000)) . ' Lakh' . ($number % 100000 ? ' ' . $this->convertToWords($number % 100000) : '');
        }

        return $this->convertToWords(floor($number / 10000000)) . ' Crore' . ($number % 10000000 ? ' ' . $this->convertToWords($number % 10000000) : '');
    }

    /**
     * Convert to Nepali date (simplified - you may want to use a proper library)
     */
    private function convertToNepaliDate($date)
    {
        if (!$date) {
            return '';
        }

        // Ensure it's a string date (Y-m-d)
        $englishDate = $date instanceof \Carbon\Carbon
            ? $date->format('Y-m-d')
            : $date;

        $nepaliDate = NepaliDateHelper::convertToNepali($englishDate);
        $devanagariNumbers = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];
        $englishNumbers   = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $day   = str_replace($devanagariNumbers, $englishNumbers, $nepaliDate['day'] ?? '');
        $monthName = $nepaliDate['month'] ?? '';
        $year  = str_replace($devanagariNumbers, $englishNumbers, $nepaliDate['year'] ?? '');
        $monthMap = [
            'वैशाख' => '01',
            'जेठ'   => '02',
            'असार'  => '03',
            'साउन'  => '04',
            'भदौ'  => '05',
            'असोज'  => '06',
            'कात्तिक' => '07',
            'मंसिर' => '08',
            'पुस'   => '09',
            'माघ'   => '10',
            'फागुन' => '11',
            'चैत'   => '12',
        ];

        $month = $monthMap[$monthName] ?? '00';

        return "{$day}/{$month}/{$year}";
    }

    /**
     * API endpoint to regenerate invoice
     */
    public function apiRegenerateInvoice(Request $request)
    {
        $request->validate([
            'file_no' => 'required|string',
        ]);

        // Delete existing receipt
        $existingReceipt = VehicleReceipt::where('file_no', $request->file_no)->first();
        if ($existingReceipt) {
            // Delete old PDF file from public folder
            if ($existingReceipt->pdf_path && file_exists(public_path($existingReceipt->pdf_path))) {
                unlink(public_path($existingReceipt->pdf_path));
            }
            $existingReceipt->delete();
        }

        // Generate new invoice
        return $this->apiGenerateInvoice($request);
    }






    public function apiGenerateProforma(Request $request)
    {
        $request->validate([
            'file_no' => 'required|string',
            'download' => 'sometimes|boolean' // Optional: true to download, false to view in browser
        ]);

        $bookings = VehicleBooking::with(['vehicle', 'customer', 'tripRoute'])
            ->where('file_no', $request->file_no)
            ->where('status', 'confirmed')
            ->orderBy('start_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        if ($bookings->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No bookings found for this file number'
                ], 404);
            }
            abort(404, 'No bookings found');
        }

        $customer = optional($bookings->first())->customer;
        $sub_total = $bookings->sum('rate_per_day');
        $discount = $bookings->sum('discount');
        $tax = $bookings->sum('tax');
        $net_amount = $sub_total - $discount + $tax;

        $existingReceipt = ProformaInvoice::where('file_no', $request->file_no)->first();

        $receipt_number = $existingReceipt
            ? $existingReceipt->invoice_number
            : $this->generateProformaNumber();

        // Prepare data for view
        $data = [
            'receipt' => null, // We'll create after view if needed
            'bookings' => $bookings,
            'customer' => $customer,
            'file_no' => $request->file_no,
            'invoice_date' => Carbon::now('Asia/Kathmandu')->format('m/d/Y'),
            'miti_date' => $this->convertToNepaliDate(now()),
            'amount_in_words' => $this->convertNumberToWords($net_amount),
            'items' => $this->prepareInvoiceItems($bookings),
            'sub_total' => $sub_total,
            'discount' => $discount,
            'tax' => $tax,
            'net_amount' => $net_amount,
            'vat_percentage' => 13,
            'receipt_number' => $receipt_number,
            'prepared_by' => auth()->user() ? auth()->user()->name : 'BIR',
            'company_name' => 'ASHIVANA VEHICLE SERVICE PVT.LTD.',
            'company_address' => 'Jwagal-10 Lalitpur, Nepal',
            'company_phone' => '602439925',
            'company_email' => 'e-account@ashivana.com.np',
            'printing_time' => Carbon::now('Asia/Kathmandu')->format('m/d/Y h:i:s A'),
        ];

        // Save receipt to database
        $receipt = ProformaInvoice::updateOrCreate(
            ['file_no' => $request->file_no],
            [
                'vehicle_booking_id' => null,
                'vehicle_moment_id' => null,
                'vehicle_id' => null,
                'customer_id' => $customer ? $customer->id : null,
                'invoice_number' => $receipt_number,
                'rate_per_day' => $sub_total,
                'sub_total' => $sub_total,
                'discount' => $discount,
                'tax' => $tax,
                'total_amount' => $net_amount,
            ]
        );

        $data['receipt'] = $receipt;

        // Generate PDF
        $pdf = PDF::loadView('layouts.admin.invoices.proforma_pdf', $data);

        $pdf->setPaper('A4', 'portrait');

        /* Folder path */
        $folderPath = public_path('uploads/proforma_invoices');

        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0755, true);
        }

        $fileName = $receipt->invoice_number . '.pdf';
        $fullPath = $folderPath . '/' . $fileName;

        $pdf->save($fullPath);

        $receipt->update([
            'pdf_path' => 'uploads/proforma_invoices/' . $fileName
        ]);

        return response()->download($fullPath, $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function generateProformaNumber()
    {
        $year = date('y');
        $month = date('m');
        $lastReceipt = ProformaInvoice::whereYear('created_at', date('Y'))
            ->orderBy('id', 'desc')
            ->first();

        if ($lastReceipt) {
            $lastNumber = intval(substr($lastReceipt->invoice_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "PF-{$month}{$year}-{$newNumber}";
    }

    /**
     * API endpoint to regenerate Proforma
     */
    public function apiRegenerateProforma(Request $request)
    {
        $request->validate([
            'file_no' => 'required|string',
        ]);

        // Delete existing receipt
        $existingReceipt = ProformaInvoice::where('file_no', $request->file_no)->first();
        if ($existingReceipt) {
            // Delete old PDF file from public folder
            if ($existingReceipt->pdf_path && file_exists(public_path($existingReceipt->pdf_path))) {
                unlink(public_path($existingReceipt->pdf_path));
            }
            $existingReceipt->delete();
        }

        // Generate new invoice
        return $this->apiGenerateProforma($request);
    }



    public function apiGenerateEstimate(Request $request)
    {
        $request->validate([
            'file_no' => 'required|string',
            'download' => 'sometimes|boolean' // Optional: true to download, false to view in browser
        ]);


        $bookings = VehicleBooking::with(['vehicle', 'customer', 'tripRoute'])
            ->where('file_no', $request->file_no)
            ->where('status', 'confirmed')
            ->orderBy('start_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        if ($bookings->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No bookings found for this file number'
                ], 404);
            }
            abort(404, 'No bookings found');
        }

        $customer = optional($bookings->first())->customer;
        $sub_total = $bookings->sum('rate_per_day');
        $discount = $bookings->sum('discount');
        $tax = $bookings->sum('tax');
        $net_amount = $sub_total - $discount + $tax;

        // $receipt_number = $this->generateEstimateNumber();

        $existingReceipt = EstimateBill::where('file_no', $request->file_no)->first();

        $receipt_number = $existingReceipt
            ? $existingReceipt->estimate_number
            : $this->generateEstimateNumber();

        // Prepare data for view
        $data = [
            'receipt' => null, // We'll create after view if needed
            'bookings' => $bookings,
            'customer' => $customer,
            'file_no' => $request->file_no,
            'invoice_date' => Carbon::now('Asia/Kathmandu')->format('m/d/Y'),
            'miti_date' => $this->convertToNepaliDate(now()),
            'amount_in_words' => $this->convertNumberToWords($net_amount),
            'items' => $this->prepareInvoiceItems($bookings),
            'sub_total' => $sub_total,
            'discount' => $discount,
            'tax' => $tax,
            'net_amount' => $net_amount,
            'vat_percentage' => 13,
            'estimate_number' => $receipt_number,
            'prepared_by' => auth()->user() ? auth()->user()->name : 'BIR',
            'company_name' => 'ASHIVANA VEHICLE SERVICE PVT.LTD.',
            'company_address' => 'Jwagal-10 Lalitpur, Nepal',
            'company_phone' => '602439925',
            'company_email' => 'e-account@ashivana.com.np',
            'printing_time' => Carbon::now('Asia/Kathmandu')->format('m/d/Y h:i:s A'),
        ];

        // Save receipt to database
        $receipt = EstimateBill::updateOrCreate(
            ['file_no' => $request->file_no],
            [
                'vehicle_booking_id' => null,
                'vehicle_moment_id' => null,
                'vehicle_id' => null,
                'file_no' => $request->file_no,
                'customer_id' => $customer ? $customer->id : null,
                'estimate_number' => $receipt_number,
                'rate_per_day' => $sub_total,
                'sub_total' => $sub_total,
                'discount' => $discount,
                'tax' => $tax,
                'total_amount' => $net_amount,
            ]
        );

        $data['receipt'] = $receipt;

        // Generate PDF
        $pdf = PDF::loadView('layouts.admin.invoices.estimate_pdf', $data);

        $pdf->setPaper('A4', 'portrait');

        /* Folder path */
        $folderPath = public_path('uploads/estimate_invoices');

        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0755, true);
        }

        $fileName = $receipt->estimate_number . '.pdf';
        $fullPath = $folderPath . '/' . $fileName;

        $pdf->save($fullPath);

        $receipt->update([
            'pdf_path' => 'uploads/estimate_invoices/' . $fileName
        ]);

        return response()->download($fullPath, $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function generateEstimateNumber()
    {
        $year = date('y');
        $month = date('m');
        $lastReceipt = EstimateBill::whereYear('created_at', date('Y'))
            ->orderBy('id', 'desc')
            ->first();

        if ($lastReceipt) {
            $lastNumber = intval(substr($lastReceipt->estimate_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "EST-{$month}{$year}-{$newNumber}";
    }

    /**
     * API endpoint to regenerate Proforma
     */
    public function apiRegenerateEstimate(Request $request)
    {
        $request->validate([
            'file_no' => 'required|string',
        ]);

        // Delete existing receipt
        $existingReceipt = EstimateBill::where('file_no', $request->file_no)->first();
        if ($existingReceipt) {
            // Delete old PDF file from public folder
            if ($existingReceipt->pdf_path && file_exists(public_path($existingReceipt->pdf_path))) {
                unlink(public_path($existingReceipt->pdf_path));
            }
            $existingReceipt->delete();
        }

        // Generate new invoice
        return $this->apiGenerateEstimate($request);
    }



    public function brands()
    {
        $brands = Brand::select('id', 'name', 'logo')->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'name' => $b->name,
                    'logo' => $b->logo
                        ? asset('uploads/brands/' . $b->logo)
                        : null,
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $brands
        ]);
    }

    //  Vehicles grouped by brand name
    public function BrandWithVehicle()
    {
        $brands = Brand::all();

        $data = [];

        foreach ($brands as $brand) {

            $vehicles = Vehicle::where('brand', $brand->name)->get();

            $data[] = [
                'brand' => $brand->name,
                'logo' => $brand->logo ? asset('uploads/brands/' . $brand->logo) : null,
                'vehicles' => $vehicles
            ];
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function vehiclesByBrand(Request $request)
    {
        $request->validate([
            'brand_id' => 'required'
        ]);

        // get brand
        $brand = Brand::where('id', $request->brand_id)->first();

        if (!$brand) {
            return response()->json([
                'status' => false,
                'message' => 'Brand not found'
            ], 404);
        }

        // match with vehicle.brand (string)
        $vehicles = Vehicle::withAvg('reviews', 'rating')->whereRaw('LOWER(brand) = ?', [strtolower($brand->name)])
            ->get();

        if ($vehicles->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No vehicles found for this brand'
            ]);
        }

        return response()->json([
            'status' => true,
            'brand_name' => $brand->name,
            'logo' => $brand->logo ? asset('uploads/brands/' . $brand->logo) : null,
            'vehicles' => $vehicles
        ]);
    }



    public function seaters()
    {
        $seaters = Seater::select('id', 'name', 'logo')->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'name' => $b->name,
                    'logo' => $b->logo
                        ? asset('uploads/seaters/' . $b->logo)
                        : null,
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $seaters
        ]);
    }


    public function vehiclesBySeaters(Request $request)
    {
        $request->validate([
            'seater' => 'required'
        ]);

        // get seater
        $seater = Seater::where('name', $request->seater)->first();

        if (!$seater) {
            return response()->json([
                'status' => false,
                'message' => 'Seater not found'
            ], 404);
        }

        // match with vehicle.brand (string)
        $vehicles = Vehicle::withAvg('reviews', 'rating')->whereRaw('LOWER(seater) = ?', [strtolower($seater->name)])
            ->get();



        if ($vehicles->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No vehicles found for this seater'
            ]);
        }




        return response()->json([
            'status' => true,
            'seater_name' => $seater->name,
            'logo' => $seater->logo ? asset('uploads/seaters/' . $seater->logo) : null,
            'vehicles' => $vehicles,
        ]);
    }




    public function transmission()
    {
        $transmission = FuelType::where('status', 1)
            ->select('id', 'name', 'status', 'logo')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'status' => $item->status,
                    'logo' => $item->logo
                        ? asset('uploads/fuel-types/' . $item->logo)
                        : null,
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $transmission
        ]);
    }


    public function vehiclesByTransmission(Request $request)
    {
        $request->validate([
            'transmission_id' => 'required'
        ]);

        // get brand
        $transmission = FuelType::where('id', $request->transmission_id)->first();

        if (!$transmission) {
            return response()->json([
                'status' => false,
                'message' => 'Transmission not found'
            ], 404);
        }

        // match with vehicle.brand (string)
        $vehicles = Vehicle::withAvg('reviews', 'rating')->whereRaw('LOWER(fuel_type) = ?', [strtolower($transmission->name)])
            ->get();

        if ($vehicles->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No vehicles found for this transmission'
            ]);
        }

        $brands = Brand::pluck('logo', 'name');

        $vehicles->transform(function ($vehicle) use ($brands) {
            $logo = $brands[$vehicle->brand] ?? null;

            $vehicle->brand_logo = $logo
                ? asset('uploads/brands/' . $logo)
                : null;

            return $vehicle;
        });

        return response()->json([
            'status' => true,
            'transmission_name' => $transmission->name,
            'transmission_logo' => $transmission->logo ? asset('uploads/fuel-types/' . $transmission->logo) : null,
            'vehicles' => $vehicles
        ]);
    }


    // public function seaters()
    // {
    //     $seaters = Vehicle::select('seater')
    //         ->whereNotNull('seater')
    //         ->distinct()
    //         ->orderBy('seater')
    //         ->get()
    //         ->map(function ($s) {

    //             // get one random vehicle for this seater
    //             $vehicle = Vehicle::where('seater', $s->seater)
    //                 ->whereNotNull('image')
    //                 ->inRandomOrder()
    //                 ->first();

    //             return [
    //                 'seater' => $s->seater,
    //                 'image' => $vehicle && $vehicle->image
    //                     ? asset($vehicle->image)
    //                     : null,
    //             ];
    //         });

    //     return response()->json([
    //         'status' => true,
    //         'data' => $seaters
    //     ]);
    // }

    public function vehiclesBySeater(Request $request)
    {
        $request->validate([
            'seater' => 'required|numeric'
        ]);

        // get vehicles by seater
        $vehicles = Vehicle::withAvg('reviews', 'rating')->where('seater', $request->seater)->get();

        if ($vehicles->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No vehicles found for this seater'
            ]);
        }

        return response()->json([
            'status' => true,
            'seater' => $request->seater,
            'vehicles' => $vehicles,
        ]);
    }





    public function mostPopularVehicles()
    {
        $vehicles = VehicleBooking::selectRaw('vehicle_id, COUNT(*) as total')
            ->with([
                'vehicle' => function ($query) {
                    $query->withAvg('reviews', 'rating');
                }
            ])
            ->groupBy('vehicle_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $vehicles
        ]);
    }

    public function VehicleDetailById($id)
    {
        $vehicle = Vehicle::with('securityFeature')
            ->withAvg('reviews', 'rating')
            ->where('id', $id)
            ->where('status', 1)
            ->first();

        if (!$vehicle) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vehicle not found'
            ], 404);
        }



        $data = $vehicle->toArray();

        $imageFields = [
            'dash_cam_image',
            'ebs_image',
            'air_conditioning_image',
            'reverse_camera_image',
            'camera_360_image',
            'emergency_braking_system_image',
            'hillside_braking_system_image',
            'hill_descent_control_image',
        ];
        $booleanFields = [
            'dash_cam',
            'ebs',
            'air_conditioning',
            'reverse_camera',
            'camera_360',
            'emergency_braking_system',
            'hillside_braking_system',
            'hill_descent_control',
        ];


        if (!empty($data['security_feature'])) {
            foreach ($imageFields as $field) {
                if (!empty($data['security_feature'][$field])) {
                    $data['security_feature'][$field] = asset(
                        'uploads/vehicle-security-features/' . $data['security_feature'][$field]
                    );
                }
            }
        }
        foreach ($booleanFields as $field) {
            if (isset($data['security_feature'][$field])) {
                $data['security_feature'][$field] =
                    $data['security_feature'][$field] ? 'Yes' : 'No';
            }
        }


        return response()->json([
            'status' => 'success',
            'message' => 'Vehicle Fetched Successfully',
            'data' => $data
        ]);
    }
    public function getTripPrice(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'vehicle_id' => 'required|exists:vehicles,id',
                'trip_category_id' => 'required|exists:trip_categories,id',
                'trip_route_id' => 'required|exists:trip_routes,id',
            ]
        );

        if ($validator->fails()) {

            return response()->json([
                'status' => 'error', // Name of the status
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        if (!$vehicle) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vehicle not found'
            ], 404);
        }
        $tripCategory = TripCategory::findOrFail($request->trip_category_id);
        $route = TripRoute::findOrFail($request->trip_route_id);

        if (!$route) {
            return response()->json([
                'status' => 'error',
                'message' => 'route not found'
            ], 404);
        }

        // Map vehicle_type to price column
        $priceColumn = match (strtolower($vehicle->vehicle_type)) {
            'car' => 'car_price',
            'hiace' => 'hiace_price',
            'coaster' => 'coaster_price',
            'bus' => 'bus_price',
            'van' => 'van_price',
            default => 'other_price',
        };

        $price = round($route->$priceColumn, 2);
        $vatPercentage = 13;
        $vatPrice = round(($price * $vatPercentage) / 100, 2);
        $totalPrice = round($price + $vatPrice, 2);



        return response()->json([
            'vehicle_name' => $vehicle->vehicle_name,
            'vehicle_type' => $vehicle->vehicle_type,
            'trip_category' => $tripCategory->name,
            'route_name' => $route->title,
            'price' => $price,
            'vat_price' => $vatPrice,
            'total_price' => $totalPrice,
        ]);
    }

    public function splashscreens()
    {
        $data = Splashscreen::orderBy('order_by', 'asc')->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'header' => $item->header,
                    'description' => $item->description,
                    'order' => $item->order_by,
                    'image' => $item->image
                        ? asset('uploads/splashscreens/' . $item->image)
                        : null,
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function getVehicleDrivers($vehicle_id)
    {
        $assignments = VehicleAssignment::with(['driver.user'])
            ->where('vehicle_id', $vehicle_id)
            ->get();

        if ($assignments->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No drivers found for this vehicle',
                'data' => []
            ]);
        }

        $drivers = $assignments->map(function ($assignment) {

            $user = $assignment->driver->user ?? null;

            return [
                'vehicle_name' => $assignment->vehicle->vehicle_name ?? null,

                'driver' => $assignment->driver ? [
                    'id' => $assignment->driver->id,
                    'role' => $assignment->driver->role,
                    'contact_number' => $assignment->driver->contact_number,
                    'experience' => $assignment->driver->experience,
                    'age' => $assignment->driver->age,
                    'license_expiry' => $assignment->driver->license_expiry ?? 'N/A',

                    'user' => $user ? [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'img' => $user->img
                            ? asset('uploads/users/' . $user->img)
                            : null,
                    ] : null,
                ] : null,

                'helper_name' => $assignment->helper->user->name ?? 'N/A',
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Drivers fetched successfully',
            'data' => $drivers
        ]);
    }

    public function BookingbyStatus($status, $customer_id)
    {
        $validStatuses = ['pending', 'confirmed', 'cancelled', 'started', 'completed', 'paid'];

        if (!in_array($status, $validStatuses)) {
            return response()->json([
                'message' => 'Invalid status'
            ], 400);
        }


        $customer = Customer::where('customer_uuid', $customer_id)->first();

        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Customer not found',
                'data' => []
            ], 404);
        }
        $customer_id = $customer->id;
        $query = VehicleBooking::query()->where('customer_id', $customer_id);


        if ($status === 'completed') {
            $query->whereHas('vehicleMoment', function ($q) {
                $q->whereNotNull('end_datetime');
            });
        } elseif ($status === 'started') {
            $query->whereHas('vehicleMoment', function ($q) {
                $q->whereNotNull('start_datetime')
                    ->whereNull('end_datetime');
            });
        } elseif ($status === 'paid') {
            $query->where('payment_status', 1);
        } else {
            $query->where('status', $status);
        }

        $bookings = $query->with([
            'tripRoute:id,title',
            'vehicle:id,vehicle_name,image,car_images',
            'driver:id,user_id,experience,age',
            'driver.user:id,name',
            'vehicleMoment:id,booking_id,end_datetime,start_datetime'
        ])
            ->get([
                'id',
                'file_no',
                'status',
                'trip_route_id',
                'vehicle_id',
                'driver_id',
                'start_date',
                'start_time',
                'end_date',
                'rate_per_day',
                'tax',
                'discount',
                'total_amount',
                'payment_status'
            ]);

        $bookings->each(function ($booking) {
            if ($booking->vehicleMoment) {

                if (
                    !empty($booking->vehicleMoment->start_datetime) &&
                    empty($booking->vehicleMoment->end_datetime)
                ) {
                    $booking->status = 'started';
                }

                if (!empty($booking->vehicleMoment->end_datetime)) {
                    $booking->status = 'completed';
                }
            }
            if ($booking->payment_status == 1) {
                $booking->status = 'paid';
            }
        });

        return response()->json($bookings);
    }



    public function vehicleBookings($vehicle_id)
    {
        $bookings = VehicleBooking::where('vehicle_id', $vehicle_id)
            ->whereDoesntHave('vehicleMoment', function ($q) {
                $q->whereNotNull('end_datetime');
            })
            ->with([
                'tripRoute:id,title',
                'vehicle:id,vehicle_name,image,car_images',
                'driver:id,user_id,experience,age',
                'driver.user:id,name',
                'vehicleMoment:id,booking_id,start_datetime,end_datetime'
            ])
            ->get();

        $bookings->each(function ($booking) {
            if ($booking->vehicleMoment) {
                if (
                    !empty($booking->vehicleMoment->start_datetime) &&
                    empty($booking->vehicleMoment->end_datetime)
                ) {
                    $booking->status = 'started';
                }
            }

            if ($booking->payment_status == 1) {
                $booking->status = 'paid';
            }
        });

        return response()->json($bookings);
    }

    public function completedVehicleBookings($vehicle_id)
    {
        $bookings = VehicleBooking::where('vehicle_id', $vehicle_id)
            ->whereHas('vehicleMoment', function ($q) {
                $q->whereNotNull('end_datetime');
            })
            ->with([
                'tripRoute:id,title',
                'vehicle:id,vehicle_name,image,car_images',
                'driver:id,user_id,experience,age',
                'driver.user:id,name',
                'vehicleMoment:id,booking_id,start_datetime,end_datetime'
            ])
            ->get();

        $bookings->each(function ($booking) {
            $booking->status = 'completed';
        });

        return response()->json($bookings);
    }


    public function getBasicSetting(Request $request)
    {
        $field = $request->query('field');
        $basic = BasicTable::first();
        if (!$basic) {
            return response()->json([
                'status' => false,
                'message' => 'No data found.'
            ], 404);
        }

        $imageFields = [
            'login_logo',
            'logo',
        ];

        if (!$field) {

            $data = $basic->toArray();

            foreach ($imageFields as $imgField) {
                if (!empty($data[$imgField])) {
                    $data[$imgField] = asset($data[$imgField]);
                }
            }

            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        }

        // Check if field exists in table
        if (!Schema::hasColumn('basic_tables', $field)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid field name.'
            ], 400);
        }

        $value = $basic->$field;
        if (in_array($field, $imageFields) && !empty($value)) {
            $value = asset($value);
        }

        return response()->json([
            'status' => true,
            'field' => $field,
            'value' => $basic->$field
        ]);
    }


    public function contactus()
    {
        $contact = ContactUs::where('status', 'active')->first();

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'No active contact found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $contact->id,
                'full_name' => $contact->full_name,
                'email' => $contact->email,
                'mobile_number' => $contact->mobile_number,
                'address' => $contact->address,
                'website_url' => $contact->website_url,
                'whatsapp_number' => $contact->whatsapp_number,
                'facebook_url' => $contact->facebook_url,
                'instagram_url' => $contact->instagram_url,
                'linkedin_url' => $contact->linkedin_url,
                'tiktok_url' => $contact->tiktok_url,
                'twitter_url' => $contact->twitter_url,
                'youtube_url' => $contact->youtube_url
            ]
        ]);
    }


    // public function storeLatLng(Request $request)
    // {
    //     $validated = $request->validate([
    //         'customer_uuid' => 'required|uuid',
    //         'lat' => 'required|numeric',
    //         'lng' => 'required|numeric',
    //     ]);


    //     // Reverse Geocoding API
    //     $response = Http::withHeaders([
    //         'User-Agent' => 'LaravelApp/1.0'
    //     ])->get('https://nominatim.openstreetmap.org/reverse', [
    //         'format' => 'json',
    //         'lat' => $validated['lat'],
    //         'lon' => $validated['lng'],
    //     ]);

    //     \Log::info('Reverse Geocoding Response', ['response' => $response->body()]);

    //     $address = null;

    //     if ($response->successful()) {
    //         $data = $response->json();
    //         $address = $data['display_name'] ?? null;
    //     }

    //     $location = CustomerLocation::updateOrCreate(
    //         ['customer_uuid' => $validated['customer_uuid']],
    //         [
    //             'lat' => $validated['lat'],
    //             'lng' => $validated['lng'],
    //             'address' => $address,
    //         ]
    //     );

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Customer location saved successfully',
    //         'data' => $location,
    //     ]);
    // }


    public function storeLatLng(Request $request)
    {
        $validated = $request->validate([
            'customer_uuid' => 'required|uuid',
            'lat'           => 'required|numeric',
            'lng'           => 'required|numeric',
        ]);

        $apiKey  = "AIzaSyDjTXcdHBg0C2XEdVFP8R7sFDz2o0RzIYw";
        $lat     = $validated['lat'];
        $lng     = $validated['lng'];
        $address   = null;
        $placeName = null;

        try {
            // Reverse Geocoding (street address)
            $geoResponse = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'latlng' => "$lat,$lng",
                'key'    => $apiKey,
            ]);

            \Log::info('Geocoding Response', ['response' => $geoResponse->json()]);

            if (
                $geoResponse->successful() &&
                ($geoData = $geoResponse->json()) &&
                ($geoData['status'] ?? '') === 'OK' &&
                !empty($geoData['results'])
            ) {
                $address = $geoData['results'][0]['formatted_address'] ?? null;
            }

            //  Nearby Places Search
            $placesResponse = Http::get('https://maps.googleapis.com/maps/api/place/nearbysearch/json', [
                'location' => "$lat,$lng",
                'rankby'   => 'distance',
                'key'      => $apiKey,
            ]);

            \Log::info('Places Response', ['response' => $placesResponse->json()]);

            if (
                $placesResponse->successful() &&
                ($placesData = $placesResponse->json()) &&
                ($placesData['status'] ?? '') === 'OK' &&
                !empty($placesData['results'])
            ) {
                $nearest   = $placesData['results'][0];
                $placeName = $nearest['name'] ?? null;

                if (empty($address)) {
                    $address = $nearest['vicinity'] ?? null;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Location API Error', ['message' => $e->getMessage()]);
        }

        $location = CustomerLocation::updateOrCreate(
            ['customer_uuid' => $validated['customer_uuid']],
            [
                'lat'        => $lat,
                'lng'        => $lng,
                'address'    => $address,
                'place_name' => $placeName,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Customer location saved successfully',
            'data'    => $location,
        ]);
    }


    public function showLatlng($customer_uuid)
    {
        $location = CustomerLocation::where('customer_uuid', $customer_uuid)->latest()->first();

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Customer location not found',
            ], 404);
        }

        $data = $location->toArray();

        $data['full_address'] = trim(
            ($location->place_name ?? '') . ', ' . ($location->address ?? ''),
            ', '
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }


    public function paymentModes()
    {
        $paymentmodes = PaymentMode::select('id', 'name', 'logo', 'status')
            ->where('status', 1)
            ->get()
            ->map(function ($item) {

                $item->logo = $item->logo
                    ? asset('uploads/payment_modes/' . $item->logo)
                    : null;

                return $item;
            });

        return response()->json([
            'status' => true,
            'data' => $paymentmodes
        ]);
    }



    public function getReceipt($booking_id)
    {
        $booking = VehicleBooking::find($booking_id);

        if (!$booking) {
            return response()->json([
                'status' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        $receipt = VehicleReceipt::where('file_no', $booking->file_no)->first();

        if (!$receipt) {
            return response()->json([
                'status' => false,
                'message' => 'Receipt not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'booking_id' => $booking->id,
            'file_no' => $booking->file_no,
            'pdf_url' => asset($receipt->receipt_path)
        ]);
    }

    public function getReceiptBlob($booking_id)
    {
        $booking = VehicleBooking::find($booking_id);

        if (!$booking) {
            return response()->json([
                'status' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        $receipt = VehicleReceipt::where('file_no', $booking->file_no)->first();

        if (!$receipt) {
            return response()->json([
                'status' => false,
                'message' => 'Receipt not found'
            ], 404);
        }

        // Full file path from public folder
        $filePath = public_path($receipt->receipt_path);

        // Check file exists
        if (!file_exists($filePath)) {
            return response()->json([
                'status' => false,
                'message' => 'PDF file not found'
            ], 404);
        }

        // Open PDF directly in browser
        return response()->file($filePath);

        // OR force download
        // return response()->download($filePath);
    }


    public function codPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:vehicle_bookings,id',
            'customer_id' => 'required|exists:customers,customer_uuid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {

            $booking = VehicleBooking::findOrFail($request->booking_id);

            $customer = Customer::where(
                'customer_uuid',
                $request->customer_id
            )->firstOrFail();

            $payment = Payment::create([
                'vehicle_booking_id'    => $booking->id,
                'amount'                => $booking->total_amount,
                'payment_method'        => 'cash',
                'payment_type'          => 'booking',
                'transaction_reference' => 'CASH-' . strtoupper(uniqid()),
                'payment_date'          => now(),
                'direction' => 'in',
                'gateway' => "cash",
                'status'                => 'pending',
                'created_by'            => $customer->id,
                'created_user_type'     => 'customer',
                'notes'                 => 'Vehicle rental payment via Cash',
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Cash booking completed successfully.',
                'payment_id' => $payment->id,
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    public function vehicleSorting(Request $request)
    {
        $sortBy = $request->get('sort_by');
        $sortOrder = $request->get('sort_order', 'asc');

        $vehicles = Vehicle::query();

        switch ($sortBy) {

            // Rating 
            case 'rating':
                $vehicles->withAvg('reviews', 'rating')
                    ->orderBy('reviews_avg_rating', $sortOrder);
                break;

            // Seater
            case 'seater':
                $vehicles->orderBy('seater', $sortOrder);
                break;

            // Brand
            case 'brand':
                $vehicles->orderBy('brand', $sortOrder);
                break;

            // Vehicle Age
            case 'age':
                if ($sortOrder === 'asc') {
                    $vehicles->orderBy('year', 'asc');
                } else {
                    $vehicles->orderBy('year', 'desc');
                }
                break;

            // Plate Color
            case 'plate_color':

                $plateColor = $request->get('plate_color');

                if ($plateColor) {
                    $vehicles->where('number_plate_color', $plateColor);
                }

                break;
        }

        return response()->json([
            'success' => true,
            'data' => $vehicles->paginate(10)
        ]);
    }




    public function checkAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after_or_equal:start_datetime',
            'booking_id' => 'nullable|integer'
        ], [
            'end_datetime.after_or_equal' => 'End datetime must be greater than or equal to start datetime.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $start = Carbon::parse($request->start_datetime);
        $end = Carbon::parse($request->end_datetime);

        $query = VehicleBooking::where('vehicle_id', $request->vehicle_id)
            ->whereNotIn('status', ['cancelled'])
            ->when($request->booking_id, function ($q) use ($request) {
                $q->where('id', '!=', $request->booking_id);
            })

            ->where(function ($q) use ($start, $end) {
                $q->where(function ($sub) use ($start, $end) {
                    $sub->whereRaw("TIMESTAMP(start_date, start_time) < ?", [$end])
                        ->whereRaw("TIMESTAMP(end_date, end_time) > ?", [$start]);
                });
            });

        $existingBooking = $query->first();

        if ($existingBooking) {
            return response()->json([
                'success' => false,
                'available' => false,
                'message' => 'Vehicle is already booked for selected time range.',
                'booking_id' => $existingBooking->id
            ]);
        }

        return response()->json([
            'success' => true,
            'available' => true,
            'message' => 'Vehicle is available.'
        ]);
    }
}
