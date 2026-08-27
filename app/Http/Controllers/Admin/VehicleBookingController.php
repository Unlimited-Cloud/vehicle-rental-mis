<?php

namespace App\Http\Controllers\Admin;

use App\Events\EmailEvent;
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
use App\Models\BookingLog;
use App\Models\Brand;
use App\Models\FuelType;
use App\Models\Seater;
use App\Models\TripCategory;
use App\Models\TripRoute;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\ProformaService;
use App\Repositories\Interfaces\VehicleRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;

class VehicleBookingController extends Controller
{

    protected $service;
    protected $vehicleRepository;
    protected $userRepository;

    private $currentUserId;
    private $currentUserCustomerId;
    private $currentUserDriverId;
    private $currentUserVehicleOwnerId;
    private $currentUserRoleId;

    private $currentUserIsCustomer;
    private $currentUserIsDriver;
    private $currentUserIsOwner;

    public function __construct(
        ProformaService $service,
        VehicleRepositoryInterface $vehicleRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->service = $service;
        $this->vehicleRepository = $vehicleRepository;
        $this->userRepository = $userRepository;
        $this->middleware(function ($request, $next) {
            $this->currentUserId = Auth::user()->id;
            $this->currentUserCustomerId = Auth::user()->customer_id;
            $this->currentUserRoleId = Auth::user()->role_id;
            $this->currentUserDriverId = $this->userRepository->getCrewProfileByUserId($this->currentUserId) ? $this->userRepository->getCrewProfileByUserId($this->currentUserId)->id : NULL;
            $this->currentUserVehicleOwnerId = $this->userRepository->getVehicleOwnerByUserId($this->currentUserId) ? $this->userRepository->getVehicleOwnerByUserId($this->currentUserId)->id : NULL;
            $this->currentUserIsCustomer = !empty(Auth::user()->customer_id) ? 'Y' : 'N';
            $this->currentUserIsDriver = $this->currentUserRoleId == 3 ? 'Y' : 'N';
            $this->currentUserIsOwner = $this->currentUserRoleId == 10 ? 'Y' : 'N';
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
            if ($this->currentUserIsDriver == 'Y') {
                $bookings = $this->vehicleRepository->getVehicleBookingsByDriverId($request, $this->currentUserDriverId);
            } elseif ($this->currentUserIsOwner == 'Y') {
                $bookings = $this->vehicleRepository->getVehicleBookingsByVehicleOwnerId(
                    $request,
                    $this->currentUserVehicleOwnerId
                );
            } else {
                $bookings = $this->vehicleRepository->getAllVehicleBookings($request);
            }
        }

        if ($this->currentUserIsOwner == 'Y') {
            $vehicles = Vehicle::where('vehicle_owner_id', $this->currentUserVehicleOwnerId)->orderBy('vehicle_name')->get();
        } else {
            $vehicles = Vehicle::all();
        }
        $customers = Customer::orderBy('name')->get();
        $drivers   = CrewProfile::whereHas('user', function ($q) {
            $q->where('role', 'driver');
        })->with('user')->get();
        $brands = Brand::orderBy('name', 'asc')->get();
        $seaters = Seater::orderByRaw('CAST(name AS UNSIGNED) ASC')->get();
        $vehicle_types = FuelType::orderBy('name', 'asc')->get();
        $currentUserIsCustomer = $this->currentUserIsCustomer;
        $currentUserIsDriver = $this->currentUserIsDriver;
        $currentUserIsOwner = $this->currentUserIsOwner;

        return view(
            'layouts.admin.vehicles_booking.index',
            compact('bookings', 'vehicles', 'customers', 'drivers', 'currentUserIsCustomer', 'currentUserIsDriver', 'currentUserIsOwner', 'brands', 'seaters', 'vehicle_types')
        );
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        Gate::authorize('create_vehicle_bookings');

        if ($this->currentUserIsOwner == 'Y') {
            $vehicles = Vehicle::where('vehicle_owner_id', $this->currentUserVehicleOwnerId)->get();
        } else {
            $vehicles = Vehicle::all();
        }

        $vehicle_types = Vehicle::select('fuel_type')->distinct()->get();
        $brands = Vehicle::select('brand')->distinct()->get();
        $seaters = Vehicle::select('seater')->distinct()->get();
        $drivers = CrewProfile::whereHas('user', function ($q) {
            $q->where('role', 'driver');
        })->with('user')->get();

        $helpers = CrewProfile::whereHas('user', function ($q) {
            $q->where('role', 'helper');
        })->with('user')->get();

        $tripCategories = TripCategory::where('status', 1)->whereNull('deleted_at')->get();
        $customers = Customer::all();
        $start = $request->start;
        $end = $request->end;
        $currentUserIsCustomer = $this->currentUserIsCustomer;
        $currentUserIsOwner = $this->currentUserIsOwner;

        // Check if we should show multiple booking form
        if ($request->has('multiple') && $request->multiple == 'true') {
            return view('layouts.admin.vehicles_booking.multiple_create', compact(
                'vehicles',
                'start',
                'end',
                'drivers',
                'helpers',
                'customers',
                'currentUserIsCustomer',
                'currentUserIsOwner',
                'tripCategories',
            ));
        }

        return view('layouts.admin.vehicles_booking.create', compact(
            'vehicles',
            'vehicle_types',
            'seaters',
            'brands',
            'start',
            'end',
            'drivers',
            'helpers',
            'customers',
            'currentUserIsCustomer',
            'tripCategories'
        ));
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
        $addData['sub_total'] = $request->rate_per_day;
        $addData['remaining_balance'] = $request->remaining_balance;
        $addData['vehicle_type'] = $request->vehicle_type;

        $addData['tax_amount_type'] = $request->tax_amount_type ?? 'percentage';
        $addData['tax'] = $request->tax ?? '0';
        $addData['discount_amount_type'] = $request->discount_amount_type;
        $addData['discount'] = $request->discount;
        $addData['payment_status'] = $request->payment_status == '' ? 0 : $request->payment_status;
        $addData['customer_id'] = $this->currentUserIsCustomer == 'N' ? $request->customer_id : $this->currentUserCustomerId;
        $vehicleBooking = VehicleBooking::create($addData);
        // $this->service->createProforma($vehicleBooking);
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
        if ($request->has('itineraries')) {
            $this->saveItineraries($vehicleBooking, $request->itineraries);
        }

        return redirect()->route('admin.vehicle_bookings.index')
            ->with('success_message', 'Booking created successfully.');
    }

    public function edit(VehicleBooking $vehicleBooking)
    {
        Gate::authorize('update_vehicle_bookings');
        $vehicles = Vehicle::all();
        $vehicle_types = Vehicle::select('fuel_type')->distinct()->get();
        $brands = Vehicle::select('brand')->distinct()->get();
        $seaters = Vehicle::select('seater')->distinct()->get();

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
        if ($booking) {
            $booking->setRelation('itineraries', \App\Models\Itinerary::where('booking_id', $booking->id)->orderBy('day_number')->get());
        }


        if (!empty($booking->vehicle_type)) {
            $vehicleQuery = Vehicle::where('vehicle_type', $booking->vehicle_type);

            if (!empty($booking->seater)) {
                $vehicleQuery->where('seater', $booking->seater);
            }
            $vehicles = $vehicleQuery->orderBy('vehicle_name')->get();
            if ($booking->vehicle_id && !$vehicles->contains('id', $booking->vehicle_id)) {
                $currentVehicle = Vehicle::find($booking->vehicle_id);
                if ($currentVehicle) {
                    $vehicles->push($currentVehicle);
                }
            }
        } else {
            $vehicles = Vehicle::all();
        }

        $currentUserIsCustomer = $this->currentUserIsCustomer;
        return view(
            'layouts.admin.vehicles_booking.create',
            compact('booking', 'vehicles', 'vehicle_types', 'brands', 'seaters', 'drivers', 'helpers', 'customers', 'currentUserIsCustomer', 'tripCategories')
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
        // $updateData['customer_id'] = $this->currentUserIsCustomer == 'N' ? $request->customer_id : $this->currentUserCustomerId;
        $oldRate = $vehicleBooking->rate_per_day;
        $oldTotal = $vehicleBooking->sub_total;
        $oldVehicleId = $vehicleBooking->vehicle_id;
        $oldStatus = $vehicleBooking->status;
        $vehicleBooking->update($updateData);

        if ($request->has('itineraries')) {
            $this->saveItineraries($vehicleBooking, $request->itineraries);
        }

        // if (
        //     $oldRate != $vehicleBooking->rate_per_day ||
        //     $oldTotal != $vehicleBooking->sub_total
        // ) {
        //     $this->service->createProforma($vehicleBooking);
        // }

        $vehicleBookingId = $vehicleBooking->id;

        $paymentData['vehicle_booking_id'] = $vehicleBookingId;
        $paymentData['amount'] = $request->paid_amount;
        $paymentData['payment_method'] = $request->payment_method;
        $paymentData['payment_date'] = $request->payment_date . ' ' . $request->payment_time;
        $paymentData['notes'] = $request->payment_note;
        Payment::where('vehicle_booking_id', $vehicleBookingId)->update($paymentData);
        $this->handleStatusTransition($vehicleBooking, $oldStatus, $oldVehicleId, $request->file_no);

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
            'helper.user',
            'itineraries' => function ($q) {
                $q->orderBy('day_number');
            }
        ]);
        if (request()->ajax()) {
            return response()->json($vehicleBooking);
        }

        return view('layouts.admin.vehicles_booking.show', compact('vehicleBooking'));
    }

    public function destroy($id)
    {
        Gate::authorize('delete_vehicle_bookings');

        try {
            VehicleBooking::where('id', $id)->update([
                'deleted_by' => Auth::id(),
                'deleted_at' => now(),
            ]);

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
        $query = VehicleBooking::with(['vehicle', 'customer', 'driver.user', 'itineraries']);

        if ($this->currentUserIsCustomer == 'Y') {
            $query->where('customer_id', $this->currentUserCustomerId);
        }

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
    public function multipleStore(Request $request)
    {
        Gate::authorize('create_vehicle_bookings');

        // Validate common fields
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'nullable|exists:crew_profiles,id',
            'helper_id' => 'nullable|exists:crew_profiles,id',
            'bookings' => 'required|array|min:1',
            'bookings.*.trip_category_id' => 'nullable',
            'bookings.*.trip_route_id' => 'nullable',
            'bookings.*.start_date' => 'required|date',
            'bookings.*.end_date' => 'required|date|after_or_equal:bookings.*.start_date',
            'bookings.*.start_time' => 'required',
            'bookings.*.rate_per_day' => 'nullable|numeric',
            'bookings.*.sub_total' => 'nullable|numeric',
            'bookings.*.discount' => 'nullable|numeric',
            'bookings.*.tax' => 'nullable|numeric',
            'bookings.*.status' => 'nullable|string',
            'bookings.*.total_amount' => 'nullable|numeric',
            'bookings.*.paid_amount' => 'nullable|numeric',
        ]);

        $createdBookings = [];
        $errors = [];

        foreach ($request->bookings as $key => $bookingData) {
            try {
                // Calculate hours if not provided
                $startDateTime = Carbon::parse($bookingData['start_date'] . ' ' . $bookingData['start_time']);
                $endDateTime = Carbon::parse($bookingData['end_date'] . ' ' . ($bookingData['end_time'] ?? '23:59:59'));
                $noOfHours = $startDateTime->diffInHours($endDateTime);

                $addData = [
                    'customer_id' => $request->customer_id,
                    'vehicle_id' => $request->vehicle_id,
                    'driver_id' => $request->driver_id,
                    'helper_id' => $request->helper_id,
                    'passenger' => $request->passenger,
                    'file_no' =>  $request->file_no ?? null,
                    'signage_information' => $request->signage_information,
                    'trip_category_id' => $bookingData['trip_category_id'] ?? null,
                    'trip_route_id' => $bookingData['trip_route_id'] ?? null,
                    'from_destination' => $bookingData['from_destination'] ?? null,
                    'to_destination' => $bookingData['to_destination'] ?? null,
                    'no_of_people' => $bookingData['no_of_people'] ?? null,
                    'start_date' => $bookingData['start_date'],
                    'end_date' => $bookingData['end_date'],
                    'start_time' => $bookingData['start_time'],
                    'end_time' => $bookingData['end_time'] ?? null,
                    'no_of_hours' => $noOfHours,
                    'rate_per_day' => $bookingData['rate_per_day'] ?? 0,
                    'sub_total' => $bookingData['sub_total'] ?? 0,
                    'discount_amount_type' => $bookingData['discount_amount_type'] ?? 'amount',
                    'discount' => $bookingData['discount'] ?? 0,
                    'vat' => $bookingData['vat'] ?? 0,
                    'tax' => $bookingData['tax'] ?? 0,
                    'total_amount' => $bookingData['total_amount'] ?? 0,
                    'paid_amount' => $bookingData['paid_amount'] ?? 0,
                    'payment_method' => $bookingData['payment_method'] ?? null,
                    'payment_status' => ($bookingData['paid_amount'] ?? 0) > 0 ? 1 : 0,
                    'notes' => $bookingData['notes'] ?? null,
                    'status' => $bookingData['status'] ?? 'pending',
                ];

                $vehicleBooking = VehicleBooking::create($addData);

                // Create payment record if paid amount exists
                if (!empty($bookingData['paid_amount']) && $bookingData['paid_amount'] > 0) {
                    $paymentData = [
                        'vehicle_booking_id' => $vehicleBooking->id,
                        'amount' => $bookingData['paid_amount'],
                        'payment_method' => $bookingData['payment_method'] ?? 'cash',
                        'transaction_reference' => (string) Str::uuid(),
                        'payment_date' => now(),
                        'notes' => $bookingData['notes'] ?? null,
                    ];
                    Payment::create($paymentData);
                }

                // Create proforma invoice
                // $this->service->createProforma($vehicleBooking);

                $createdBookings[] = $vehicleBooking->id;
            } catch (\Exception $e) {
                $errors[] = "Booking #" . ($key + 1) . " failed: " . $e->getMessage();
            }
        }

        if (count($createdBookings) > 0) {
            $message = count($createdBookings) . " booking(s) created successfully.";
            if (count($errors) > 0) {
                $message .= " But " . count($errors) . " failed.";
                return redirect()->route('admin.vehicle_bookings.index')
                    ->with('warning_message', $message)
                    ->with('errors', $errors);
            }
            return redirect()->route('admin.vehicle_bookings.index')
                ->with('success_message', $message);
        }

        return redirect()->route('admin.vehicle_bookings.index')
            ->with('error_message', 'Failed to create any bookings. ' . implode(', ', $errors));
    }

    // Add these methods to your VehicleBookingController

    public function getCustomersList()
    {
        $customers = Customer::all(['id', 'name']);
        return response()->json($customers);
    }

    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string'
        ]);

        $customer = Customer::create($request->all());

        return response()->json([
            'success' => true,
            'id' => $customer->id,
            'name' => $customer->name
        ]);
    }

    public function getTripCategoriesList()
    {
        $categories = TripCategory::where('status', 1)->get(['id', 'name']);
        return response()->json($categories);
    }

    public function storeTripCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $category = TripCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => 1
        ]);

        return response()->json([
            'success' => true,
            'id' => $category->id,
            'name' => $category->name
        ]);
    }

    public function getTripRoutesList()
    {
        $routes = TripRoute::all(['id', 'title']);
        return response()->json($routes);
    }

    public function storeTripRoute(Request $request)
    {
        $request->validate([
            'trip_category_id' => 'required|exists:trip_categories,id',
            'title' => 'required|string|max:255',
            'km' => 'nullable|numeric',
            'car_price' => 'nullable|numeric',
            'hiace_price' => 'nullable|numeric',
            'coaster_price' => 'nullable|numeric',
            'bus_price' => 'nullable|numeric'
        ]);

        $route = TripRoute::create($request->all());

        return response()->json([
            'success' => true,
            'id' => $route->id,
            'title' => $route->title
        ]);
    }

    public function storeDriver(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'license_number' => 'nullable|string|max:50'
        ]);

        // Create user first
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'driver',
            'password' => bcrypt(Str::random(8))
        ]);

        $driver = CrewProfile::create([
            'user_id' => $user->id,
            'license_number' => $request->license_number,
            'status' => 'active'
        ]);

        $drivers = CrewProfile::whereHas('user', function ($q) {
            $q->where('role', 'driver');
        })->with('user')->get();

        return response()->json([
            'success' => true,
            'id' => $driver->id,
            'drivers' => $drivers
        ]);
    }

    public function storeHelper(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255'
        ]);

        // Create user first
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'helper',
            'password' => bcrypt(Str::random(8))
        ]);

        $helper = CrewProfile::create([
            'user_id' => $user->id,
            'status' => 'active'
        ]);

        $helpers = CrewProfile::whereHas('user', function ($q) {
            $q->where('role', 'helper');
        })->with('user')->get();

        return response()->json([
            'success' => true,
            'id' => $helper->id,
            'helpers' => $helpers
        ]);
    }


    private function saveItineraries(VehicleBooking $vehicleBooking, array $itineraries)
    {
        \App\Models\Itinerary::where('booking_id', $vehicleBooking->id)->delete();

        $rows = [];
        foreach ($itineraries as $index => $item) {
            if (empty($item['itinerary_date']) && empty($item['from_destination']) && empty($item['to_destination']) && empty($item['est_km']) && empty($item['est_hours'])) {
                continue;
            }

            $rows[] = [
                'booking_id' => $vehicleBooking->id,
                'file_no' => $vehicleBooking->file_no,
                'day_number' => $index + 1,
                'itinerary_date' => $item['itinerary_date'] ?? null,
                'from_destination' => $item['from_destination'] ?? null,
                'to_destination' => $item['to_destination'] ?? null,
                'est_km' => $item['est_km'] ?? 0,
                'est_hours' => $item['est_hours'] ?? 0,
                'is_overnight' => !empty($item['is_overnight']) && $item['is_overnight'] == 1,
                'per_km_rate' => $item['per_km_rate'] ?? 0,
                'per_hour_rate' => $item['per_hour_rate'] ?? 0,
                'overnight_charge' => $item['overnight_charge'] ?? 0,
                'est_price' => $item['est_price'] ?? 0,
                'notes' => $item['notes'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($rows)) {
            \App\Models\Itinerary::insert($rows);
        }
    }

    public function getVehicleRate($vehicleType)
    {
        $rate = DB::table('trip_route_vehicle_type_prices')
            ->where('vehicle_type', $vehicleType)
            ->first();

        return response()->json([
            'per_km_rate' => $rate->per_km ?? 0,
            'per_hour_rate' => $rate->per_hour ?? 0,
            'overnight_price' => $rate->overnight_price ?? 0,
        ]);
    }

    public function getVehiclesByType(Request $request, $type = null)
    {
        $query = Vehicle::query();

        // Optional — only applied if a fuel type was actually saved on the booking
        $fuelType = $type ?? $request->fuel_type;
        if (!empty($fuelType)) {
            $query->where('fuel_type', $fuelType);
        }

        if ($this->currentUserIsOwner == 'Y') {
            $query->where('vehicle_owner_id', $this->currentUserVehicleOwnerId);
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->filled('seater')) {
            $query->where('seater', $request->seater);
        }

        $vehicles = $query->orderBy('vehicle_name')->get(['id', 'vehicle_name', 'fuel_type', 'seater', 'brand']);

        return response()->json($vehicles);
    }


    public function assignVehicle(Request $request, $id)
    {
        Gate::authorize('update_vehicle_bookings');

        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'status' => 'nullable|string|in:pending,confirmed,cancelled',
        ]);

        $vehicleBooking = VehicleBooking::findOrFail($id);
        $oldVehicleId = $vehicleBooking->vehicle_id;
        $oldStatus = $vehicleBooking->status;

        $vehicleBooking->vehicle_id = $request->vehicle_id;
        if ($request->filled('status')) {
            $vehicleBooking->status = $request->status;
        }
        $vehicleBooking->save();

        $this->handleStatusTransition($vehicleBooking, $oldStatus, $oldVehicleId);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle assigned successfully.'
        ]);
    }




    private function handleStatusTransition(VehicleBooking $vehicleBooking, string $oldStatus, $oldVehicleId, $fileNo = null)
    {
        $newStatus = $vehicleBooking->status;
        $customer = Customer::find($vehicleBooking->customer_id);

        // Only fire once — on the transition INTO confirmed, not every save while already confirmed
        if ($newStatus === 'confirmed' && $oldStatus !== 'confirmed' && $vehicleBooking->call_type === 'api') {
            $this->service->generateFinalInvoice($fileNo ?? $vehicleBooking->file_no);

            BookingLog::create([
                'booking_id' => $vehicleBooking->id,
                'status' => 'confirmed',
                'remarks' => 'Booking confirmed by admin',
                'created_by' => Auth::user() ? Auth::user()->id : 0,
            ]);

            if ($customer && $customer->email) {
                event(new EmailEvent($customer->email, 'confirmed_booking', 'success', 'customer'));
            }
        }

        // Same idea — only on the transition INTO cancelled
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled' && $vehicleBooking->call_type === 'api') {
            BookingLog::create([
                'booking_id' => $vehicleBooking->id,
                'status' => 'cancelled',
                'remarks' => 'Booking cancelled by admin',
                'created_by' => Auth::user() ? Auth::user()->id : 0,
            ]);
        }

        // Vehicle reassignment notice — unchanged behavior, just consolidated here
        if ($oldVehicleId != $vehicleBooking->vehicle_id && $customer && $customer->email) {
            event(new EmailEvent(
                $customer->email,
                'booking_changed',
                'success',
                'customer',
                '',
                '',
                $vehicleBooking->id
            ));
        }
    }
}
