<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VehicleBooking;
use App\Models\Vehicle;
use App\Models\CrewProfile;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use App\Models\Payment;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VehicleBookingExport;
use App\Helpers\NepaliDateHelper;
use App\Models\TripCategory;
use App\Models\TripRoute;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\ProformaService;
use App\Repositories\Interfaces\VehicleRepositoryInterface;

class VehicleBookingController extends Controller
{

    protected $service;
    protected $vehicleRepository;

    private $currentUserId;
    private $currentUserCustomerId;

    private $currentUserIsCustomer;

    public function __construct(ProformaService $service, VehicleRepositoryInterface $vehicleRepository)
    {
        $this->service = $service;
        $this->vehicleRepository = $vehicleRepository;
        $this->middleware(function ($request, $next) {
            $this->currentUserId = Auth::user()->id;
            $this->currentUserCustomerId = Auth::user()->customer_id;
            $this->currentUserIsCustomer = !empty(Auth::user()->customer_id) ? 'Y' : 'N';
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('index_vehicle_bookings');

        if ($this->currentUserIsCustomer == 'Y') {
            $bookings = $this->vehicleRepository->getVehicleBookingsByCustomerId($request, $this->currentUserCustomerId);
        } else {
            $bookings = $this->vehicleRepository->getAllVehicleBookings($request);
        }

        $vehicles  = Vehicle::orderBy('vehicle_name')->get();
        $customers = Customer::orderBy('name')->get();
        $drivers   = CrewProfile::whereHas('user', function ($q) {
            $q->where('role', 'driver');
        })->with('user')->get();
        $currentUserIsCustomer = $this->currentUserIsCustomer;
        return view(
            'layouts.admin.vehicles_booking.index',
            compact('bookings', 'vehicles', 'customers', 'drivers', 'currentUserIsCustomer')
        );
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        Gate::authorize('create_vehicle_bookings');
        $vehicles = Vehicle::all();
        $drivers = CrewProfile::whereHas('user', function ($q) {
            $q->where('role', 'driver');
        })->with('user')->get();

        // Fetch helpers
        $helpers = CrewProfile::whereHas('user', function ($q) {
            $q->where('role', 'helper');
        })->with('user')->get();

        $tripCategories = TripCategory::where('status', 1)->get();
        // Customers dropdown
        $customers = Customer::all();
        $start = $request->start;
        $end   = $request->end;
        $currentUserIsCustomer = $this->currentUserIsCustomer;
        return view('layouts.admin.vehicles_booking.create',  compact('vehicles', 'start', 'end', 'drivers', 'helpers', 'customers', 'currentUserIsCustomer', 'tripCategories'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create_vehicle_bookings');
        $request->validate([
            'vehicle_id' => 'required',
            'driver_id' => 'nullable|exists:crew_profiles,id',
            'helper_id' => 'nullable|exists:crew_profiles,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_km' => 'nullable|integer',
            'end_km' => 'nullable|integer|gte:start_km',
            'approx_fuel_litre' => 'nullable|numeric',
            // 'customer_name' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable',
            'from_destination' => 'nullable',
            'to_destination' => 'nullable',
            'no_of_people' => 'nullable|integer',
            'notes' => 'nullable|string',
            'trip_category_id' => 'nullable',
            'trip_route_id' => 'nullable'
        ]);

        $addData = $request->all();
        $addData['vat'] = $request->vat;
        $addData['passenger'] = $request->passenger;
        $addData['file_no'] = $request->file_no;


        $addData['start_time'] = $request->start_time;
        $addData['end_time'] = $request->end_time;
        $addData['signage_information'] = $request->signage_information;

        $no_of_hours = $request->no_of_hours;

        $startDateTime = Carbon::parse($request->start_date . ' ' . $request->start_time);
        $endDateTime   = Carbon::parse($request->end_date . ' ' . $request->end_time);
        // Check if end is before start
        // if ($endDateTime->lessThan($startDateTime)) {
        //     return redirect()->route('admin.vehicle_bookings.create')
        //         ->with('warning_message', 'To date and time should be greater than start date.')
        //         ->with('end_date', $request->end_date);
        // }

        if (empty($no_of_hours)) {
            $no_of_hours = $startDateTime->diffInHours($endDateTime);
        }
        $addData['no_of_hours'] = (int) $no_of_hours;
        $addData['rate_per_day'] = $request->rate_per_day;
        $addData['sub_total'] = $request->sub_total;
        $addData['remaining_balance'] = $request->remaining_balance;


        $addData['tax_amount_type'] = $request->tax_amount_type ?? 'percentage';
        $addData['tax'] = $request->tax ?? '0';
        $addData['discount_amount_type'] = $request->discount_amount_type;
        $addData['discount'] = $request->discount;
        $addData['payment_status'] = $request->payment_status == '' ? 0 : $request->payment_status;
        $addData['customer_id'] = $this->currentUserIsCustomer == 'N' ? $request->customer_id : $this->currentUserCustomerId;
        $vehicleBooking = VehicleBooking::create($addData);
        $this->service->createProforma($vehicleBooking);
        $vehicleBookingId = $vehicleBooking->id;

        if (!empty($request->paid_amount) && $request->paid_amount > 0) {

            $paymentData['vehicle_booking_id'] = $vehicleBookingId;
            $paymentData['amount'] = $request->paid_amount;
            $paymentData['payment_method'] = $request->payment_method;
            $paymentData['transaction_reference'] = (string) Str::uuid();
            $paymentData['payment_date'] = $request->payment_date . ' ' . $request->payment_time;
            $paymentData['notes'] = $request->payment_note;

            Payment::create($paymentData);
        }

        return redirect()->route('admin.vehicle_bookings.index')
            ->with('success_message', 'Booking created successfully.');
    }

    public function edit(VehicleBooking $vehicleBooking)
    {
        Gate::authorize('update_vehicle_bookings');
        $vehicles = Vehicle::all();
        $drivers = CrewProfile::whereHas('user', function ($q) {
            $q->where('role', 'driver');
        })->with('user')->get();

        // Fetch helpers
        $helpers = CrewProfile::whereHas('user', function ($q) {
            $q->where('role', 'helper');
        })->with('user')->get();

        $tripCategories = TripCategory::where('status', 1)->get();
        // Customers dropdown
        $customers = Customer::all();

        $booking = VehicleBooking::select(
            'vehicle_bookings.*',
            'payments.amount as paid_amount',
            'payments.payment_method as payment_method',
            'payments.transaction_reference',
            'payments.payment_date',
            'payments.notes as payment_note',
            'payments.deleted_by'
        )
            ->leftJoin('payments', 'payments.vehicle_booking_id', '=', 'vehicle_bookings.id')
            ->where('vehicle_bookings.id', $vehicleBooking->id)
            ->first();
        $currentUserIsCustomer = $this->currentUserIsCustomer;
        return view(
            'layouts.admin.vehicles_booking.create',
            compact('booking', 'vehicles', 'drivers', 'helpers', 'customers', 'currentUserIsCustomer', 'tripCategories')
        );
    }

    public function update(Request $request, VehicleBooking $vehicleBooking)
    {
        Gate::authorize('update_vehicle_bookings');

        $updateData = $request->all();
        $updateData['start_time'] = $request->start_time;
        $updateData['end_time'] = $request->end_time;
        $no_of_hours = $request->no_of_hours;

        $startDateTime = Carbon::parse($request->start_date . ' ' . $request->start_time);
        $endDateTime   = Carbon::parse($request->end_date . ' ' . $request->end_time);
        // Check if end is before start
        // if ($endDateTime->lessThan($startDateTime)) {
        //     return redirect()->route('admin.vehicle_bookings.edit', $vehicleBooking)
        //         ->with('warning_message', 'To date and time should be greater than start date.')
        //         ->with('end_date', $request->end_date);
        // }

        if (empty($no_of_hours)) {
            $no_of_hours = $startDateTime->diffInHours($endDateTime);
        }
        $updateData['no_of_hours'] = (int) $no_of_hours;
        $updateData['signage_information'] = $request->signage_information;
        $updateData['rate_per_day'] = $request->rate_per_day;
        $updateData['sub_total'] = $request->sub_total;
        $updateData['vat'] = $request->vat;
        $updateData['passenger'] = $request->passenger;
        $updateData['file_no'] = $request->file_no;
        $updateData['tax_amount_type'] = $request->tax_amount_type ?? 'percentage';
        $updateData['tax'] = $request->tax;
        $updateData['discount_amount_type'] = $request->discount_amount_type;
        $updateData['discount'] = $request->discount;
        $updateData['remaining_balance'] = $request->remaining_balance;
        $updateData['trip_category_id'] = $request->trip_category_id;
        $updateData['trip_route_id'] = $request->trip_route_id;
        $updateData['payment_status'] = $request->payment_status == '' ? 0 : $request->payment_status;
        $updateData['customer_id'] = $this->currentUserIsCustomer == 'N' ? $request->customer_id : $this->currentUserCustomerId;
        $oldRate = $vehicleBooking->rate_per_day;
        $oldTotal = $vehicleBooking->sub_total;
        $vehicleBooking->update($updateData);
        if (
            $oldRate != $vehicleBooking->rate_per_day ||
            $oldTotal != $vehicleBooking->sub_total
        ) {
            $this->service->createProforma($vehicleBooking);
        }

        $vehicleBookingId = $vehicleBooking->id;

        $paymentData['vehicle_booking_id'] = $vehicleBookingId;
        $paymentData['amount'] = $request->paid_amount;
        $paymentData['payment_method'] = $request->payment_method;
        $paymentData['payment_date'] = $request->payment_date . ' ' . $request->payment_time;
        $paymentData['notes'] = $request->payment_note;
        Payment::where('vehicle_booking_id', $vehicleBookingId)->update($paymentData);

        return redirect()->route('admin.vehicle_bookings.index')
            ->with('success', 'Booking updated successfully.');
    }

    public function show(VehicleBooking $vehicleBooking)
    {
        Gate::authorize('read_vehicle_bookings');
        $vehicleBooking->load([
            'vehicle',
            'customer',
            'driver.user',
            'helper.user'
        ]);
        if (request()->ajax()) {
            return response()->json($vehicleBooking);
        }

        return view('layouts.admin.vehicles_booking.show', compact('vehicleBooking'));
    }

    public function destroy(VehicleBooking $vehicleBooking)
    {
        Gate::authorize('delete_vehicle_bookings');
        try {
            $vehicleBooking->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Booking deleted successfully.'
                ]);
            }

            return back()->with('success', 'Booking deleted successfully.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting booking: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error deleting booking.');
        }
    }


    public function export(Request $request)
    {
        $fileName = 'vehicle_bookings_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new VehicleBookingExport($request),
            $fileName
        );
    }

    public function getRoutes($category_id)
    {
        $routes = TripRoute::where('trip_category_id', $category_id)
            ->where('status', 1)
            ->get();

        return response()->json($routes);
    }


    public function fetchEvents(Request $request)
    {
        $query = VehicleBooking::with(['vehicle', 'customer', 'driver.user']);

        if ($request->vehicle_id) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->driver_id) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->file_no) {
            $query->where('file_no', 'LIKE', '%' . $request->file_no . '%');
        }

        // NEW: Filter by passenger name
        if ($request->passenger) {
            $query->where('passenger_name', 'LIKE', '%' . $request->passenger . '%');
        }

        $bookings = $query->get();

        $events = [];

        // Generate colors for vehicles (you can customize these)
        $vehicleColors = [
            '#3498db', // Blue
            '#e74c3c', // Red
            '#2ecc71', // Green
            '#f39c12', // Orange
            '#9b59b6', // Purple
            '#1abc9c', // Turquoise
            '#e67e22', // Carrot
            '#34495e', // Dark Blue
            '#16a085', // Green Sea
            '#27ae60', // Nephritis
            '#2980b9', // Belize Hole
            '#8e44ad', // Wisteria
            '#2c3e50', // Midnight Blue
            '#d35400', // Pumpkin
            '#c0392b', // Pomegranate
        ];

        foreach ($bookings as $index => $booking) {
            // Assign color based on vehicle_id to ensure same vehicle gets same color
            $colorIndex = $booking->vehicle_id % count($vehicleColors);
            $vehicleColor = $vehicleColors[$colorIndex];

            // Determine status color overlay (optional)
            $statusColor = $booking->status == 'confirmed' ? '#28a745' : ($booking->status == 'pending' ? '#ffc107' : '#dc3545');

            $events[] = [
                'id' => $booking->id,
                'title' => $booking->vehicle?->vehicle_name . ' - ' . $booking->customer?->name,
                'start' => $booking->start_date,
                'end' => \Carbon\Carbon::parse($booking->end_date)->addDay()->format('Y-m-d'),
                'color' => $vehicleColor, // Use vehicle color
                'textColor' => '#ffffff', // White text for better visibility
                'borderColor' => $statusColor, // Border shows status
                'extendedProps' => [
                    'vehicle_id' => $booking->vehicle_id,
                    'vehicle_name' => $booking->vehicle?->vehicle_name,
                    'customer_name' => $booking->customer?->name,
                    'customer_email' => $booking->customer?->email,
                    'customer_phone' => $booking->customer?->phone,
                    'from_destination' => $booking->from_destination,
                    'to_destination' => $booking->to_destination,
                    'status' => $booking->status,
                    'notes' => $booking->notes,
                ]
            ];
        }

        return response()->json($events);
    }

    public function convertAdToBs(Request $request)
    {
        $date = $request->date;
        $cacheKey = 'nepali_date_' . $date;

        $converted = Cache::remember($cacheKey, now()->addDays(30), function () use ($date) {
            return NepaliDateHelper::convertToNepali($date);
        });

        return response()->json([
            'success' => true,
            'nepali'  => $converted['nepali'],
            'year'    => $converted['year'],
            'month'   => $converted['month'],
            'day'     => $converted['day'],
        ]);
    }

    public function convertMultipleAdToBs(Request $request)
    {
        $results = [];

        foreach ($request->dates as $date) {
            $cacheKey = 'nepali_date_' . $date;

            $converted = Cache::remember($cacheKey, now()->addDays(30), function () use ($date) {
                return NepaliDateHelper::convertToNepali($date);
            });

            $results[$date] = [
                'day'   => $converted['day'],
                'month' => $converted['month'],
                'year'  => $converted['year'],
            ];

            //cache


        }

        return response()->json([
            'success' => true,
            'data'    => $results
        ]);
    }


    /**
     * Show form for creating multiple bookings with same base details
     */
    public function createMultiple(Request $request)
    {
        Gate::authorize('create_vehicle_bookings');

        $vehicles = Vehicle::all();
        $drivers = CrewProfile::whereHas('user', function ($q) {
            $q->where('role', 'driver');
        })->with('user')->get();

        $helpers = CrewProfile::whereHas('user', function ($q) {
            $q->where('role', 'helper');
        })->with('user')->get();

        $tripCategories = TripCategory::where('status', 1)->get();
        $customers = Customer::all();
        $currentUserIsCustomer = $this->currentUserIsCustomer;

        return view('layouts.admin.vehicles_booking.multiple_create', compact(
            'vehicles',
            'drivers',
            'helpers',
            'customers',
            'currentUserIsCustomer',
            'tripCategories'
        ));
    }

    /**
     * Store multiple bookings
     */
    public function storeMultiple(Request $request)
    {
        Gate::authorize('create_vehicle_bookings');

        $request->validate([
            // Common fields
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'nullable|exists:crew_profiles,id',
            'helper_id' => 'nullable|exists:crew_profiles,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required',
            'end_time' => 'required',
            'no_of_hours' => 'nullable|numeric',
            'rate_per_day' => 'nullable|numeric',
            'sub_total' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'vat' => 'nullable',
            'signage_information' => 'nullable|string',

            // Individual booking details
            'bookings' => 'required|array|min:1',
            'bookings.*.passenger' => 'nullable|string',
            'bookings.*.file_no' => 'nullable|string',
            'bookings.*.trip_category_id' => 'nullable|exists:trip_categories,id',
            'bookings.*.trip_route_id' => 'nullable|exists:trip_routes,id',
            'bookings.*.from_destination' => 'nullable|string',
            'bookings.*.to_destination' => 'nullable|string',
            'bookings.*.no_of_people' => 'nullable|integer',
            'bookings.*.status' => 'required|in:pending,confirmed,cancelled',
            'bookings.*.paid_amount' => 'nullable|numeric',
            'bookings.*.payment_method' => 'nullable|string',
            'bookings.*.payment_date' => 'nullable|date',
            'bookings.*.payment_note' => 'nullable|string',
            'bookings.*.payment_status' => 'nullable|in:0,1,2',
            'bookings.*.notes' => 'nullable|string',
        ]);

        $createdBookings = [];
        $errors = [];

        DB::beginTransaction();

        try {
            // Common data for all bookings
            $commonData = [
                'customer_id' => $request->customer_id,
                'vehicle_id' => $request->vehicle_id,
                'driver_id' => $request->driver_id,
                'helper_id' => $request->helper_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'no_of_hours' => $request->no_of_hours,
                'rate_per_day' => $request->rate_per_day,
                'tax_amount_type' => 'percentage',
                'discount_amount_type' => $request->discount_amount_type ?? 'amount',
                'vat' => $request->vat,
                'signage_information' => $request->signage_information,
            ];

            // Calculate days for common data
            $startDateTime = Carbon::parse($request->start_date . ' ' . $request->start_time);
            $endDateTime = Carbon::parse($request->end_date . ' ' . $request->end_time);
            $days = $startDateTime->diffInDays($endDateTime);
            if ($days == 0) $days = 1;

            foreach ($request->bookings as $index => $bookingData) {
                // Get rate based on trip route and vehicle type if rate_per_day is not set manually
                $ratePerDay = $request->rate_per_day;

                if (empty($ratePerDay) && !empty($bookingData['trip_route_id'])) {
                    $tripRoute = TripRoute::find($bookingData['trip_route_id']);
                    $vehicleType = Vehicle::find($request->vehicle_id)->vehicle_type ?? 'car';

                    if ($tripRoute) {
                        switch (strtolower($vehicleType)) {
                            case 'car':
                                $ratePerDay = $tripRoute->car_price;
                                break;
                            case 'hiace':
                                $ratePerDay = $tripRoute->hiace_price;
                                break;
                            case 'coaster':
                                $ratePerDay = $tripRoute->coaster_price;
                                break;
                            case 'bus':
                                $ratePerDay = $tripRoute->bus_price;
                                break;
                            default:
                                $ratePerDay = 0;
                        }
                    }
                }

                // Calculate sub_total
                $subTotal = $days * ($ratePerDay ?? 0);

                // Calculate discount
                $discount = $request->discount ?? 0;
                $discountAmount = 0;
                if ($discount > 0) {
                    if (($request->discount_amount_type ?? 'amount') === 'percentage') {
                        $discountAmount = $subTotal * ($discount / 100);
                    } else {
                        $discountAmount = $discount;
                    }
                }

                $afterDiscount = $subTotal - $discountAmount;

                // Calculate VAT
                $vatAmount = 0;
                if (($request->vat ?? 0) == 1 && $afterDiscount > 0) {
                    $vatAmount = $afterDiscount * 0.13;
                }

                $totalAmount = $afterDiscount + $vatAmount;
                $paidAmount = $bookingData['paid_amount'] ?? 0;
                $remainingBalance = $totalAmount - $paidAmount;

                // Create booking data
                $bookingToCreate = array_merge($commonData, [
                    'rate_per_day' => $ratePerDay,
                    'sub_total' => $subTotal,
                    'tax' => $vatAmount,
                    'discount' => $request->discount ?? 0,
                    'total_amount' => $totalAmount,
                    'remaining_balance' => $remainingBalance,
                    'passenger' => $bookingData['passenger'] ?? null,
                    'file_no' => $bookingData['file_no'] ?? null,
                    'trip_category_id' => $bookingData['trip_category_id'] ?? null,
                    'trip_route_id' => $bookingData['trip_route_id'] ?? null,
                    'from_destination' => $bookingData['from_destination'] ?? null,
                    'to_destination' => $bookingData['to_destination'] ?? null,
                    'no_of_people' => $bookingData['no_of_people'] ?? null,
                    'status' => $bookingData['status'],
                    'payment_status' => $bookingData['payment_status'] ?? 0,
                    'notes' => $bookingData['notes'] ?? null,
                ]);

                // Create the booking
                $vehicleBooking = VehicleBooking::create($bookingToCreate);
                $createdBookings[] = $vehicleBooking->id;

                // Create proforma
                $this->service->createProforma($vehicleBooking);

                // Create payment if paid amount exists
                if (!empty($paidAmount) && $paidAmount > 0) {
                    $paymentData = [
                        'vehicle_booking_id' => $vehicleBooking->id,
                        'amount' => $paidAmount,
                        'payment_method' => $bookingData['payment_method'] ?? 'cash',
                        'transaction_reference' => (string) Str::uuid(),
                        'payment_date' => ($bookingData['payment_date'] ?? date('Y-m-d')) . ' ' . ($bookingData['payment_time'] ?? '00:00:00'),
                        'notes' => $bookingData['payment_note'] ?? null,
                    ];

                    Payment::create($paymentData);
                }
            }

            DB::commit();

            return redirect()->route('admin.vehicle_bookings.index')
                ->with('success_message', count($createdBookings) . ' bookings created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('admin.vehicle_bookings.multiple.create')
                ->with('error_message', 'Error creating bookings: ' . $e->getMessage())
                ->withInput();
        }
    }
}
