<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleBooking;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleBookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bookings = VehicleBooking::with('vehicle')
            ->orderBy('start_date', 'desc')
            ->get();

        $vehicles = Vehicle::orderBy('vehicle_name')->get();

        return view(
            'layouts.admin.vehicles_booking.index',
            compact('bookings', 'vehicles')
        );
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $vehicles = Vehicle::all();
        $start = $request->start;
        $end   = $request->end;
        return view('layouts.admin.vehicles_booking.create',  compact('vehicles', 'start', 'end'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required',
            'customer_name' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable',
            'from_destination' => 'nullable',
            'to_destination' => 'nullable',
            'no_of_people' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        VehicleBooking::create($request->all());

        return redirect()->route('admin.vehicle_bookings.index')
            ->with('success', 'Booking created successfully.');
    }

    public function edit(VehicleBooking $vehicleBooking)
    {
        $vehicles = Vehicle::all();

        $booking = $vehicleBooking;

        return view(
            'layouts.admin.vehicles_booking.create',
            compact('booking', 'vehicles')
        );
    }

    public function update(Request $request, VehicleBooking $vehicleBooking)
    {
        $vehicleBooking->update($request->all());

        return redirect()->route('admin.vehicle_bookings.index')
            ->with('success', 'Booking updated successfully.');
    }

    public function show(VehicleBooking $vehicleBooking)
    {
        $vehicles = Vehicle::all();
        return view('layouts.admin.vehicles_booking.show', compact('vehicleBooking', 'vehicles'));
    }

    public function destroy(VehicleBooking $vehicleBooking)
    {
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


    public function fetchEvents(Request $request)
    {
        $query = VehicleBooking::with('vehicle');

        if ($request->vehicle_id) {
            $query->where('vehicle_id', $request->vehicle_id);
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
                'title' => $booking->vehicle->vehicle_name . ' - ' . $booking->customer_name,
                'start' => $booking->start_date,
                'end' => \Carbon\Carbon::parse($booking->end_date)->addDay()->format('Y-m-d'),
                'color' => $vehicleColor, // Use vehicle color
                'textColor' => '#ffffff', // White text for better visibility
                'borderColor' => $statusColor, // Border shows status
                'extendedProps' => [
                    'vehicle_id' => $booking->vehicle_id,
                    'vehicle_name' => $booking->vehicle->vehicle_name,
                    'customer_name' => $booking->customer_name,
                    'customer_email' => $booking->customer_email,
                    'customer_phone' => $booking->customer_phone,
                    'from_destination' => $booking->from_destination,
                    'to_destination' => $booking->to_destination,
                    'status' => $booking->status,
                    'notes' => $booking->notes,
                ]
            ];
        }

        return response()->json($events);
    }
}
