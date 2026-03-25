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
            //  Validation
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,customer_uuid',
                'vehicle_id' => 'required|exists:vehicles,id',
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
            ]);

            //  Generate Proforma
            $this->service->createProforma($booking);
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
}
