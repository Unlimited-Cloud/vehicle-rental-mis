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
        $category = TripCategory::where('status', 1)->get();

        return response()->json([
            'status' => true,
            'message' => 'Trip category fetched successfully',
            'data' => $category
        ]);
    }

    public function tripRoutes($category_id)
    {
        $routes = TripRoute::with('category')->where('trip_category_id', $category_id)->get();

        return response()->json([
            'status' => true,
            'message' => 'Trip routes fetched successfully',
            'data' => $routes
        ]);
    }



    public function createBooking(Request $request)
    {
        try {
            //  Validate basic input
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,customer_uuid',
                'vehicle_id' => 'required|exists:vehicles,id',
                'trip_category_id' => 'required|exists:trip_categories,id',
                'trip_route_id' => 'required|exists:trip_routes,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'discount_amount_type' => 'nullable|string|in:flat,percent',
                'discount' => 'nullable|numeric|min:0',
                'from_destination' => 'nullable',
                'to_destination' => 'nullable',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get vehicle type
            $vehicle = Vehicle::find($request->vehicle_id);
            $vehicle_type = strtolower($vehicle->vehicle_type); // e.g., car, hiace, bus

            //  Get TripRoute
            $tripRoute = TripRoute::find($request->trip_route_id);

            // Get rate per day dynamically based on vehicle type
            $rate_field = $vehicle_type . '_price'; // car_price, hiace_price, etc.
            if (!isset($tripRoute->$rate_field)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Rate not found for this vehicle type'
                ], 400);
            }

            $rate_per_day = $tripRoute->$rate_field;

            // Calculate number of days
            $start_date = \Carbon\Carbon::parse($request->start_date);
            $end_date = \Carbon\Carbon::parse($request->end_date);
            $days = $start_date->diffInDays($end_date) + 1; // Include start day

            //  Calculate sub_total
            $sub_total = $rate_per_day * $days;

            //  Apply discount if exists
            $discount_amount = 0;
            if ($request->discount_amount_type && $request->discount) {
                if ($request->discount_amount_type == 'amount') {
                    $discount_amount = $request->discount;
                } elseif ($request->discount_amount_type == 'percentage') {
                    $discount_amount = ($sub_total * $request->discount) / 100;
                }
            }

            //  Calculate final total_amount
            $total_amount = $sub_total - $discount_amount;

            //  Create Booking
            $booking = VehicleBooking::create(array_merge($request->all(), [
                'rate_per_day' => $rate_per_day,
                'sub_total' => $sub_total,
                'discount' => $discount_amount,
                'total_amount' => $total_amount,
                'status' => "pending",
            ]));
            $this->service->createProforma($booking);

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
}
