<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\CrewProfile;
use App\Models\User;
use App\Models\PetrolPump;
use App\Models\VehicleBooking;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function index()
    {
        Gate::authorize('index_dashboard');
        // Vehicle counts
        $totalVehicles = Vehicle::count();
        $availableVehicles = Vehicle::where('status', 1)->count();
        $unavailableVehicles = Vehicle::where('status', 0)->count();

        // Customer count
        $totalCustomers = Customer::count();

        // Crew counts
        $totalDrivers = CrewProfile::whereHas('user', function ($q) {
            $q->where('role', 'driver');
        })->count();

        $totalHelpers = CrewProfile::whereHas('user', function ($q) {
            $q->where('role', 'helper');
        })->count();

        $totalCrew = CrewProfile::count();

        // Petrol pump count
        $totalPetrolPumps = PetrolPump::count();

        // Booking counts
        $totalBookings = VehicleBooking::count();
        $activeBookings = VehicleBooking::where('status', 'confirmed')
            ->whereDate('end_date', '>=', now())
            ->count();
        $pendingBookings = VehicleBooking::where('status', 'pending')->count();

        // Recent bookings for dashboard display
        $recentBookings = VehicleBooking::with(['vehicle', 'customer'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('layouts.admin.dashboard', compact(
            'totalVehicles',
            'availableVehicles',
            'unavailableVehicles',
            'totalCustomers',
            'totalDrivers',
            'totalHelpers',
            'totalCrew',
            'totalPetrolPumps',
            'totalBookings',
            'activeBookings',
            'pendingBookings',
            'recentBookings'
        ));
    }
}
