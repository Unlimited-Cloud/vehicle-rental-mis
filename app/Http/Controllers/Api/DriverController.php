<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\CrewProfile;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use Illuminate\Support\Facades\DB;
use App\Models\Questionnaire;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Services\VehicleMomentService;

class DriverController extends Controller
{
    protected $vehicleMomentService;
    public function __construct(VehicleMomentService $vehicleMomentService)
    {
        $this->vehicleMomentService = $vehicleMomentService;
    }
    public function login(Request $request)
    {
        $users = User::where('email', $request->email)->first();

        // Check if user exists
        if (!$users) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Credentials'
            ], 401);
        }

        // Check if the user has a crew profile
        $crewProfile = CrewProfile::where('user_id', $users->id)->first();

        if (!$crewProfile) {
            return response()->json([
                'status' => false,
                'message' => 'Crew profile not found'
            ], 401);
        }

        // Check password from users table
        if (!Hash::check($request->password, $users->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Credentials'
            ], 401);
        }

        // Generate token
        $token = $users->createToken('driver-token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'token' => $token,
            'crewProfile' => $crewProfile
        ], 200);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ]);
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

    public function getDriverBookings($driverId)
    {
        $bookings = VehicleBooking::where('driver_id', $driverId)
            ->with([
                'vehicle',
                'driver.user',
                'helper.user',
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    public function getDriverUpcomingBookings($driverId)
    {
        $bookings = VehicleBooking::where('driver_id', $driverId)
            ->where(function ($query) {
                $query->where('start_date', '>', date('Y-m-d'))
                    ->orWhere(function ($query) {
                        $query->where('start_date', date('Y-m-d'))
                            ->where('start_time', '>', date('H:i:s'));
                    });
            })
            ->with([
                'vehicle',
                'driver.user',
                'helper.user',
            ])
            ->orderBy('start_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->limit(3)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }


    public function getAllQuestionnaire()
    {
        $questionnaires = Questionnaire::where('is_active', '1')->get();

        return response()->json([
            'success' => true,
            'data' => $questionnaires
        ]);
    }

    public function getQuestionnaire($id)
    {
        $questionnaire = Questionnaire::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $questionnaire
        ]);
    }


    public function storeMovements(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id'          => 'required|exists:vehicle_bookings,id',
            'driver_id'           => 'nullable|exists:crew_profiles,id',
            'helper_id'           => 'nullable|exists:crew_profiles,id',
            'vehicle_no'          => 'nullable|string',
            'signage_information' => 'nullable|string',
            'trip_category_id'    => 'nullable|exists:trip_categories,id',
            'trip_route_id'       => 'nullable|exists:trip_routes,id',

            // Depot Departure
            'depot_departure_datetime' => 'required|date',
            'depot_departure_km'       => 'required|numeric|min:0',
            'depot_departure_image'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'depot_departure_comments' => 'nullable|string',

            // Pickup Arrival
            'pickup_arrival_datetime' => 'nullable|date|after_or_equal:depot_departure_datetime',
            'pickup_arrival_km'       => 'nullable|numeric|gte:depot_departure_km',
            'pickup_arrival_image'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'pickup_arrival_comments' => 'nullable|string',

            // Drop Off
            'dropoff_datetime' => 'nullable|date|after_or_equal:depot_departure_datetime',
            'dropoff_km'       => 'nullable|numeric|gte:depot_departure_km',
            'dropoff_image'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'dropoff_comments' => 'nullable|string',

            // Estimated return
            'estimated_return_to_depot_km'       => 'nullable|numeric|min:0',
            'estimated_return_to_depot_minutes'  => 'nullable|integer|min:0',
            'estimated_return_to_pickup_km'      => 'nullable|numeric|min:0',
            'estimated_return_to_pickup_minutes' => 'nullable|integer|min:0',

            // Incident
            'has_incident'    => 'nullable|boolean',
            'incident_report' => 'nullable|required_if:has_incident,1|string',
            'incident_image'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            // Questionnaire answers
            'answers'   => 'sometimes|array',
            'answers.*' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();

            // validated() keeps UploadedFile instances, but be explicit so the
            // service's `instanceof UploadedFile` checks always work
            foreach (['depot_departure_image', 'pickup_arrival_image', 'dropoff_image', 'incident_image'] as $field) {
                if ($request->hasFile($field)) {
                    $data[$field] = $request->file($field);
                }
            }

            // Default crew/vehicle info from the booking if the app didn't send it
            $data['has_incident'] = $request->boolean('has_incident');

            $vehicleMoment = $this->vehicleMomentService->store($data);

            return response()->json([
                'success' => true,
                'message' => 'Vehicle moment created successfully',
                'data'    => $vehicleMoment->load(['driver', 'helper', 'vehicle', 'questionnaireAnswers']),
            ], 201);
        } catch (\Exception $e) {
            Log::error('storeMovements failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create vehicle moment',
                'error'   => config('app.debug') ? $e->getMessage() : 'Something went wrong',
            ], 500);
        }
    }
}
