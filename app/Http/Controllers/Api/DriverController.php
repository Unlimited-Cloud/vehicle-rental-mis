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

class DriverController extends Controller
{
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
}
