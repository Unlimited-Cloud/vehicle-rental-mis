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
use App\Repositories\Interfaces\UserRepositoryInterface;

class DashboardController extends Controller
{
    private $currentUserId;

    private $currentUserCustomerId;
    private $currentUserRoleId;
    private $currentUserIsCustomer;
    private $currentUserIsDriver;
    private $currentUserDriverId;

    protected $vehicleRepository;
    protected $userRepository;

    public function __construct(
        VehicleRepositoryInterface $vehicleRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->vehicleRepository = $vehicleRepository;
        $this->userRepository = $userRepository;

        $this->middleware(function ($request, $next) {
            $this->currentUserId = Auth::user()->id;
            $this->currentUserCustomerId = Auth::user()->customer_id;
            $this->currentUserDriverId = $this->userRepository->getCrewProfileByUserId($this->currentUserId) ? $this->userRepository->getCrewProfileByUserId($this->currentUserId)->id : NULL;
            $this->currentUserRoleId = Auth::user()->role_id;
            $this->currentUserIsCustomer = !empty(Auth::user()->customer_id) ? 'Y' : 'N';
            $this->currentUserIsDriver = $this->currentUserRoleId == 3 ? 'Y' : 'N';
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

        $vehicles = Vehicle::pluck('vehicle_name', 'id');
        $customers = Customer::pluck('name', 'id');

        $totalCrew = CrewProfile::count();

        // Petrol pump count
        $totalPetrolPumps = PetrolPump::count();

        $currentUserIsCustomer = $this->currentUserIsCustomer;
        $currentUserIsDriver = $this->currentUserIsDriver;
        // Booking counts
        if ($this->currentUserIsCustomer == 'Y') {
            $totalBookings = $this->vehicleRepository->getVehicleBookingsCountByCustomerId($this->currentUserCustomerId);
        } else {
            if ($this->currentUserIsDriver == 'Y') {
                $totalBookings = $this->vehicleRepository->getVehicleBookingsCountByDriverId($this->currentUserDriverId);
            } else {
                $totalBookings = $this->vehicleRepository->getAllVehicleBookingsCount();
            }
        }

        if ($this->currentUserIsCustomer == 'Y') {
            $activeBookings = $this->vehicleRepository->getActiveVehicleBookingsCountByCustomerId($this->currentUserCustomerId);
        } else {
            $activeBookings = $this->vehicleRepository->getAllActiveVehicleBookingsCount();
        }

        if ($this->currentUserIsCustomer == 'Y') {
            $pendingBookings = $this->vehicleRepository->getPendingVehicleBookingsCountByCustomerId($this->currentUserCustomerId);
        } else {
            $pendingBookings = $this->vehicleRepository->getAllPendingVehicleBookingsCount();
        }

        if ($this->currentUserIsCustomer == 'Y') {
            $recentBookings = $this->vehicleRepository->getRecentVehicleBookingsByCustomerId('start_date', 'desc', $this->currentUserCustomerId);
        } else {
            if ($this->currentUserIsDriver == 'Y') {
                $recentBookings = $this->vehicleRepository->getRecentVehicleBookingsByDriverId('start_date', 'desc', '6', $this->currentUserDriverId);
            } else {
                $recentBookings = $this->vehicleRepository->getAllRecentVehicleBookings('start_date', 'desc',);
            }
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
            'currentUserIsCustomer',
            'currentUserIsDriver',
            'vehicles',
            'customers'
        ));
    }
    public function getDashboardData(Request $request)
    {
        $range     = $request->range ?? 7;
        $vehicleId = $request->vehicle_id;
        $customerId = $request->customer_id;

        $fromDate = now()->subDays($range)->startOfDay();
        $toDate   = now()->endOfDay();

        $query = VehicleBooking::query()
            ->whereBetween('start_date', [$fromDate, $toDate]);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        // 📈 Trends
        // $trends = (clone $query)
        //     ->selectRaw('DATE(start_date) as date, COUNT(*) as total')
        //     ->groupBy('date')
        //     ->orderBy('date')
        //     ->get();

        $trends = (clone $query)
            ->selectRaw('DATE(start_date) as date, COUNT(*) as total, SUM(total_amount) as total_amount')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 🥧 Status
        $status = (clone $query)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get();

        // 📊 Top Customers
        $customers = (clone $query)
            ->selectRaw('customer_id, COUNT(*) as total')
            ->with('customer')
            ->groupBy('customer_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // 📊 Top Customers
        $vehicles = (clone $query)
            ->selectRaw('vehicle_id, COUNT(*) as total')
            ->with('vehicle')
            ->groupBy('vehicle_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // 🚗 Vehicle Utilization
        $totalVehicles = Vehicle::count();
        $usedVehicles = (clone $query)->distinct('vehicle_id')->count('vehicle_id');

        // 📅 Heatmap
        $heatmap = (clone $query)
            ->selectRaw('DATE(start_date) as date, COUNT(*) as total')
            ->groupBy('date')
            ->get();

        return response()->json([
            'trends' => $trends,
            'status' => $status,
            'customers' => $customers,
            'vehicles' => $vehicles,

            // 'revenue' => $revenue,
            'utilization' => [
                'used' => $usedVehicles,
                'total' => $totalVehicles
            ],
            'heatmap' => $heatmap
        ]);
    }
}
