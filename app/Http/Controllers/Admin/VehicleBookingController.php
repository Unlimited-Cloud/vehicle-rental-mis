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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VehicleBookingController extends Controller
{

    public function __construct() {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('index_vehicle_bookings');
        $query = VehicleBooking::with([
            'vehicle',
            'customer',
            'payment',
            'driver.user'
        ]);

        // Filter by vehicle
        if ($request->vehicle_id) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        // Filter by customer
        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by driver
        if ($request->driver_id) {
            $query->where('driver_id', $request->driver_id);
        }

        $bookings = $query->orderBy('start_date', 'desc')->get();

        $vehicles  = Vehicle::orderBy('vehicle_name')->get();
        $customers = Customer::orderBy('name')->get();
        $drivers   = CrewProfile::whereHas('user', function ($q) {
            $q->where('role', 'driver');
        })->with('user')->get();

        return view(
            'layouts.admin.vehicles_booking.index',
            compact('bookings', 'vehicles', 'customers', 'drivers')
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

        // Customers dropdown
        $customers = Customer::all();
        $start = $request->start;
        $end   = $request->end;
        return view('layouts.admin.vehicles_booking.create',  compact('vehicles', 'start', 'end', 'drivers', 'helpers', 'customers'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create_vehicle_bookings');
        $request->validate([
            'vehicle_id' => 'required',
            'customer_id' => 'required|exists:customers,id',
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
        ]);

        $addData = $request->all();
        $addData['start_time'] = $request->start_time;
        $addData['end_time'] = $request->end_time;
        $addData['signage_information'] = $request->signage_information;

        $no_of_hours = $request->no_of_hours;

        $startDateTime = Carbon::parse($request->start_date . ' ' . $request->start_time);
        $endDateTime   = Carbon::parse($request->end_date . ' ' . $request->end_time);
        // Check if end is before start
        if ($endDateTime->lessThan($startDateTime)) {
            return redirect()->route('admin.vehicle_bookings.create')
                ->with('warning_message', 'To date and time should be greater than start date.')
                ->with('end_date', $request->end_date);
        }

        if (empty($no_of_hours)) {
            $no_of_hours = $startDateTime->diffInHours($endDateTime);
        }
        $addData['no_of_hours'] = (int) $no_of_hours;
        $addData['rate_per_day'] = $request->rate_per_day;
        $addData['sub_total'] = $request->sub_total;
        $addData['tax_amount_type'] = $request->tax_amount_type;
        $addData['tax'] = $request->tax;
        $addData['discount_amount_type'] = $request->discount_amount_type;
        $addData['discount'] = $request->discount;
        $addData['payment_status'] = $request->payment_status == '' ? 0 : $request->payment_status;

        $vehicleBooking = VehicleBooking::create($addData);
        $vehicleBookingId = $vehicleBooking->id;

        $paymentData['vehicle_booking_id'] = $vehicleBookingId;
        $paymentData['amount'] = $request->paid_amount;
        $paymentData['payment_method'] = $request->payment_method;
        $paymentData['transaction_reference'] = (string) Str::uuid();
        $paymentData['payment_date'] = $request->payment_date . ' ' . $request->payment_time;
        $paymentData['notes'] = $request->payment_note;
        Payment::create($paymentData);

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

        return view(
            'layouts.admin.vehicles_booking.create',
            compact('booking', 'vehicles', 'drivers', 'helpers', 'customers')
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
        if ($endDateTime->lessThan($startDateTime)) {
            return redirect()->route('admin.vehicle_bookings.edit', $vehicleBooking)
                ->with('warning_message', 'To date and time should be greater than start date.')
                ->with('end_date', $request->end_date);
        }

        if (empty($no_of_hours)) {
            $no_of_hours = $startDateTime->diffInHours($endDateTime);
        }
        $updateData['no_of_hours'] = (int) $no_of_hours;
        $updateData['signage_information'] = $request->signage_information;
        $updateData['rate_per_day'] = $request->rate_per_day;
        $updateData['sub_total'] = $request->sub_total;
        $updateData['tax_amount_type'] = $request->tax_amount_type;
        $updateData['tax'] = $request->tax;
        $updateData['discount_amount_type'] = $request->discount_amount_type;
        $updateData['discount'] = $request->discount;
        $updateData['payment_status'] = $request->payment_status == '' ? 0 : $request->payment_status;
        $vehicleBooking->update($updateData);

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
        Gate::authorize('view_vehicle_bookings');
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
                'title' => $booking->vehicle->vehicle_name . ' - ' . $booking->customer->name,
                'start' => $booking->start_date,
                'end' => \Carbon\Carbon::parse($booking->end_date)->addDay()->format('Y-m-d'),
                'color' => $vehicleColor, // Use vehicle color
                'textColor' => '#ffffff', // White text for better visibility
                'borderColor' => $statusColor, // Border shows status
                'extendedProps' => [
                    'vehicle_id' => $booking->vehicle_id,
                    'vehicle_name' => $booking->vehicle->vehicle_name,
                    'customer_name' => isset($booking->customer) ? $booking->customer->name : '',
                    'customer_email' => isset($booking->customer) ? $booking->customer->email : '',
                    'customer_phone' => isset($booking->customer) ? $booking->customer->phone : '',
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
}
