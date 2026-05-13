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
use Maatwebsite\Excel\Facades\Excel;
use App\Events\EmailEvent;
use App\Models\VehicleReceipt;

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
        ])->where('status', 1)->get();

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
                'agent_code' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation Error',
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

            $total_amount = max(0, $sub_total - $discount_amount);

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




            //  Create booking
            $booking = VehicleBooking::create([
                'customer_id' => $customerId,
                'vehicle_id' => $request->vehicle_id,
                'trip_category_id' => $request->trip_category_id,
                'trip_route_id' => $request->trip_route_id,

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
                'total_amount' => $total_amount,

                'status' => 'pending',

                'contact_person' => $request->contact_person,
                'contact_email' => $request->contact_email,
                'contact_number' => $request->contact_number,
                'agent_code' => $request->agent_code ?? null,
                'file_no' => $file_no ?? null,
            ]);



            //  Generate Proforma
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
        $vehicles = Vehicle::whereRaw('LOWER(brand) = ?', [strtolower($brand->name)])
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
        $vehicles = Vehicle::whereRaw('LOWER(seater) = ?', [strtolower($seater->name)])
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
            'vehicles' => $vehicles
        ]);
    }




    public function transmission()
    {
        $transmission = FuelType::select('id', 'name', 'status')->where('status', 1)->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'name' => $b->name,
                    'status' => $b->status
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
        $vehicles = Vehicle::whereRaw('LOWER(fuel_type) = ?', [strtolower($transmission->name)])
            ->get();

        if ($vehicles->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No vehicles found for this transmission'
            ]);
        }

        return response()->json([
            'status' => true,
            'transmission_name' => $transmission->name,
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
        $vehicles = Vehicle::where('seater', $request->seater)->get();

        if ($vehicles->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No vehicles found for this seater'
            ]);
        }

        return response()->json([
            'status' => true,
            'seater' => $request->seater,
            'vehicles' => $vehicles
        ]);
    }





    public function mostPopularVehicles()
    {
        $vehicles = VehicleBooking::selectRaw('vehicle_id, COUNT(*) as total')
            ->with('vehicle')
            ->groupBy('vehicle_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'vehicle' => $item->vehicle
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $vehicles
        ]);
    }

    public function VehicleDetailById($id)
    {
        $vehicle = Vehicle::where('id', $id)
            ->where('status', 1)
            ->first();

        if (!$vehicle) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vehicle not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Vehicle Fetched Successfully',
            'data' => $vehicle
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
                'message' => $validator->errors()
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

        $price = $route->$priceColumn;



        return response()->json([
            'vehicle_name' => $vehicle->vehicle_name,
            'vehicle_type' => $vehicle->vehicle_type,
            'trip_category' => $tripCategory->name,
            'route_name' => $route->title,
            'price' => $price,
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

    public function provinces()
    {
        $provinces = Province::select('id', 'pname', 'pnumber', 'headquarter', 'pname_np', 'status', 'map_index')
            ->orderBy('pnumber', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $provinces
        ]);
    }

    public function districtsByProvince(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'province_id' => 'required|exists:province,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $districts = District::where('province_id', $request->province_id)
            ->select('id', 'name', 'province_id', 'name_np', 'district_index')
            ->orderBy('name', 'asc')
            ->get();

        if ($districts->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No districts found for this province'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $districts
        ]);
    }

    public function vdcsByDistrict(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'district_id' => 'required|exists:district,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $vdcs = VDC::where('DISTRICT_ID', $request->district_id)
            ->select('id', 'NAME')
            ->orderBy('NAME', 'asc')
            ->get();

        if ($vdcs->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No VDC found for this district'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $vdcs
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
        $validStatuses = ['pending', 'confirmed', 'cancelled', 'completed'];

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
                'total_amount'
            ]);

        $bookings->each(function ($booking) {
            if ($booking->vehicleMoment) {
                $booking->status = 'completed';
            }
        });

        return response()->json($bookings);
    }
}
