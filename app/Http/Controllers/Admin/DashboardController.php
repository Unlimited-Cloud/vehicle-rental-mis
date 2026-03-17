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
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\VehicleRepositoryInterface;

class DashboardController extends Controller
{
    private $currentUserId;

    private $currentUserCustomerId;

    private $currentUserIsCustomer;
    protected $vehicleRepository;

    public function __construct(VehicleRepositoryInterface $vehicleRepository) {
        $this->vehicleRepository = $vehicleRepository;
        $this->middleware(function ($request, $next) {
            $this->currentUserId = Auth::user()->id;
            $this->currentUserCustomerId = Auth::user()->customer_id;
            $this->currentUserIsCustomer = !empty(Auth::user()->customer_id) ? 'Y' : 'N';
            return $next($request);
        });
    }
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

        $currentUserIsCustomer = $this->currentUserIsCustomer;
        // Booking counts
        if($this->currentUserIsCustomer == 'Y'){
            $totalBookings = $this->vehicleRepository->getVehicleBookingsCountByCustomerId($this->currentUserCustomerId);
        }else{
            $totalBookings = $this->vehicleRepository->getAllVehicleBookingsCount();
        }

        if($this->currentUserIsCustomer == 'Y'){
            $activeBookings = $this->vehicleRepository->getActiveVehicleBookingsCountByCustomerId($this->currentUserCustomerId);
        }else{
            $activeBookings = $this->vehicleRepository->getAllActiveVehicleBookingsCount();
        }

        if($this->currentUserIsCustomer == 'Y'){
            $pendingBookings = $this->vehicleRepository->getPendingVehicleBookingsCountByCustomerId($this->currentUserCustomerId);
        }else{
            $pendingBookings = $this->vehicleRepository->getAllPendingVehicleBookingsCount();
        }

        if($this->currentUserIsCustomer == 'Y'){
            $recentBookings = $this->vehicleRepository->getRecentVehicleBookingsByCustomerId('start_date', 'desc',6,$this->currentUserCustomerId);
        }else{
            $recentBookings = $this->vehicleRepository->getAllRecentVehicleBookings('start_date', 'desc',6);
        }

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
            'recentBookings',
            'currentUserIsCustomer'
        ));
    }
}
