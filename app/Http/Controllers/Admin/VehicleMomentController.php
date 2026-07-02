<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\VehicleMomentService;
use App\Models\VehicleBooking;
use App\Models\CrewProfile;
use App\Models\User;
use App\Models\VehicleMoment;
use App\Repositories\Interfaces\VehicleMovementRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class VehicleMomentController extends Controller
{
    protected $service;
    private $currentUserIsDriver;
    private $currentUserIsCustomer;
    private $currentUserId;
    private $currentUserCustomerId;
    private $currentUserDriverId;
    private $currentUserRoleId;
    protected $vehicleMovementRepository;
    protected $userRepository;


    public function __construct(VehicleMomentService $service, VehicleMovementRepositoryInterface $vehicleMovementRepository, UserRepositoryInterface $userRepository)
    {
        $this->service = $service;
        $this->vehicleMovementRepository = $vehicleMovementRepository;
        $this->userRepository = $userRepository;
        $this->middleware(function ($request, $next) {
            $this->currentUserId = Auth::user()->id;
            $this->currentUserCustomerId = Auth::user()->customer_id;
            $this->currentUserRoleId = Auth::user()->role_id;
            $this->currentUserDriverId = $this->userRepository->getCrewProfileByUserId($this->currentUserId) ? $this->userRepository->getCrewProfileByUserId($this->currentUserId)->id : NULL;
            $this->currentUserIsCustomer = !empty(Auth::user()->customer_id) ? 'Y' : 'N';
            $this->currentUserIsDriver = $this->currentUserRoleId == 3 ? 'Y' : 'N';
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        Gate::authorize('index_vehicles_vehicle_movement');
        if ($this->currentUserIsDriver == 'Y') {
            $moments = $this->vehicleMovementRepository->getVehicleMovementsByDriverId($request, $this->currentUserDriverId);
        } else {
            $moments = $this->vehicleMovementRepository->getAllVehicleMovements($request);
        }
        $currentUserIsDriver = $this->currentUserIsDriver;

        return view('layouts.admin.vehicle_moments.index', compact('moments', 'currentUserIsDriver'));
    }

    public function create(Request $request)
    {
        Gate::authorize('create_vehicles_vehicle_movement');
        $booking = DB::table('vehicle_bookings as vb')
            ->select(
                'vb.*',
                'v.vehicle_name',
                'd.name as driver_name',
                'h.name as helper_name',
                'c.name as customer_name',
            )
            ->leftJoin('vehicles as v', 'v.id', '=', 'vb.vehicle_id')
            ->leftJoin('crew_profiles as cp_driver', 'cp_driver.id', '=', 'vb.driver_id')
            ->leftJoin('users as d', 'd.id', '=', 'cp_driver.user_id')
            ->leftJoin('crew_profiles as cp_helper', 'cp_helper.id', '=', 'vb.helper_id')
            ->leftJoin('users as h', 'h.id', '=', 'cp_helper.user_id')
            ->leftJoin('customers as c', 'c.id', '=', 'vb.customer_id')
            ->where('vb.id', $request->booking_id)
            ->first();

        if (!$booking) {
            abort(404, 'Booking not found');
        }

        // Get all vehicles for dropdown
        $vehicles = DB::table('vehicles')
            ->select('id', 'vehicle_name', 'vehicle_type')
            ->where('status', 1)
            ->get();

        // Get all drivers 
        $drivers = DB::table('users as u')
            ->select('u.id', 'u.name', 'cp.role')
            ->join('crew_profiles as cp', 'cp.user_id', '=', 'u.id')
            ->where('cp.role', 'driver')
            ->get();

        // Get all helpers
        $helpers = DB::table('users as u')
            ->select('u.id as user_id', 'u.name', 'cp.id as crew_id', 'cp.role')
            ->join('crew_profiles as cp', 'cp.user_id', '=', 'u.id')
            ->where('cp.role', 'helper')
            ->get();

        // Get Trip Categories
        $tripCategories = DB::table('trip_categories')
            ->select('id', 'name')
            ->where('status', 1)
            ->get();

        // Get Trip Routes
        $tripRoutes = DB::table('trip_routes')
            ->select('id', 'title')
            ->where('status', 1)
            ->get();

        $questionnaires = DB::table('questionnaires')
            ->select('id', 'question', 'type', 'is_required', 'sort_order')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        // Initialize allowance variables
        $driverAllowance = 0;
        $driverSalary = 0;
        $driverBonus = 0;
        $driverDeduction = 0;
        $driverRemarks = '';
        $helperAllowance = 0;
        $helperSalary = 0;
        $helperBonus = 0;
        $helperDeduction = 0;
        $helperRemarks = '';

        return view('layouts.admin.vehicle_moments.create', compact(
            'booking',
            'vehicles',
            'drivers',
            'helpers',
            'questionnaires',
            'tripCategories',
            'tripRoutes',
            'driverAllowance',
            'driverSalary',
            'driverBonus',
            'driverDeduction',
            'driverRemarks',
            'helperAllowance',
            'helperSalary',
            'helperBonus',
            'helperDeduction',
            'helperRemarks'
        ));
    }

    public function store(Request $request)
    {
        Gate::authorize('create_vehicles_vehicle_movement');
        $request->validate([
            'start_km' => 'required|numeric|min:0',
            'end_km' => 'nullable|numeric|gt:start_km',
        ], [
            'end_km.gt' => 'End KM must be greater than Start KM.',
        ]);

        try {
            $data = $request->all();
            $this->service->store($data);

            return redirect()->route('admin.vehicle_moments.index')
                ->with('success', 'Vehicle moment created successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
    public function edit($id)
    {
        Gate::authorize('update_vehicles_vehicle_movement');
        $moment = VehicleMoment::findOrFail($id);

        $booking = DB::table('vehicle_bookings as vb')
            ->select(
                'vb.*',
                'v.vehicle_name',
                'd.name as driver_name',
                'h.name as helper_name',
                'c.name as customer_name'
            )
            ->leftJoin('vehicles as v', 'v.id', '=', 'vb.vehicle_id')
            ->leftJoin('crew_profiles as cp_driver', 'cp_driver.id', '=', 'vb.driver_id')
            ->leftJoin('users as d', 'd.id', '=', 'cp_driver.user_id')
            ->leftJoin('crew_profiles as cp_helper', 'cp_helper.id', '=', 'vb.helper_id')
            ->leftJoin('users as h', 'h.id', '=', 'cp_helper.user_id')
            ->leftJoin('customers as c', 'c.id', '=', 'vb.customer_id')
            ->where('vb.id', $moment->booking_id)
            ->first();

        // Vehicles
        $vehicles = DB::table('vehicles')
            ->select('id', 'vehicle_name', 'vehicle_type')
            ->where('status', 1)
            ->get();

        // Drivers
        $drivers = DB::table('users as u')
            ->select('u.id', 'u.name', 'cp.role')
            ->join('crew_profiles as cp', 'cp.user_id', '=', 'u.id')
            ->where('cp.role', 'driver')
            ->get();

        // Helpers
        $helpers = DB::table('users as u')
            ->select('u.id as user_id', 'u.name', 'cp.id as crew_id', 'cp.role')
            ->join('crew_profiles as cp', 'cp.user_id', '=', 'u.id')
            ->where('cp.role', 'helper')
            ->get();

        // Get Trip Categories
        $tripCategories = DB::table('trip_categories')
            ->select('id', 'name')
            ->where('status', 1)
            ->get();

        // Get Trip Routes
        $tripRoutes = DB::table('trip_routes')
            ->select('id', 'title')
            ->where('status', 1)
            ->get();

        // Questionnaires
        $questionnaires = DB::table('questionnaires')
            ->select('id', 'question', 'type', 'is_required', 'sort_order')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        // Existing answers
        $answers = DB::table('vehicle_questionnaire_answers')
            ->where('vehicle_moment_id', $id)
            ->pluck('answer', 'questionnaire_id');

        // Fetch existing attendance/allowance data for driver
        $driverAttendance = DB::table('attendance')
            ->where('vehicle_moment_id', $id)
            ->where('crew_id', $booking->driver_id)
            ->first();

        $driverAllowance = $driverAttendance->allowances ?? 0;
        $driverSalary = $driverAttendance->salary_amount ?? 0;
        $driverBonus = $driverAttendance->bonus ?? 0;
        $driverDeduction = $driverAttendance->deduction ?? 0;
        $driverRemarks = $driverAttendance->remarks ?? '';

        // Fetch existing attendance/allowance data for helper
        $helperAttendance = DB::table('attendance')
            ->where('vehicle_moment_id', $id)
            ->where('crew_id', $booking->helper_id)
            ->first();

        $helperAllowance = $helperAttendance->allowances ?? 0;
        $helperSalary = $helperAttendance->salary_amount ?? 0;
        $helperBonus = $helperAttendance->bonus ?? 0;
        $helperDeduction = $helperAttendance->deduction ?? 0;
        $helperRemarks = $helperAttendance->remarks ?? '';

        return view('layouts.admin.vehicle_moments.create', compact(
            'moment',
            'booking',
            'vehicles',
            'drivers',
            'helpers',
            'questionnaires',
            'answers',
            'tripCategories',
            'tripRoutes',
            'driverAllowance',
            'driverSalary',
            'driverBonus',
            'driverDeduction',
            'driverRemarks',
            'helperAllowance',
            'helperSalary',
            'helperBonus',
            'helperDeduction',
            'helperRemarks'
        ));
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('update_vehicles_vehicle_movement');
        $request->validate([
            'start_km' => 'required|numeric|min:0',
            'end_km' => 'nullable|numeric|gt:start_km',
        ], [
            'end_km.gt' => 'End KM must be greater than Start KM.',
        ]);
        try {
            $data = $request->all();

            // Use service to update with all logic
            $this->service->update($id, $data);

            return redirect()->route('admin.vehicle_moments.index')
                ->with('success', 'Vehicle moment updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update vehicle moment: ' . $e->getMessage())
                ->withInput();
        }
    }
    public function show(VehicleMoment $vehicleMoment)
    {
        Gate::authorize('read_vehicles_vehicle_movement');
        try {
            // Fetch vehicle moment with joins
            $moment = DB::table('vehicle_moments as vm')
                ->select(
                    'vm.*',
                    'v.vehicle_name',
                    'd.name as driver_name',
                    'h.name as helper_name',
                    'c.name as customer_name',
                    'vb.start_date',
                    'vb.end_date',
                    'vb.file_no',
                    'vb.passenger',
                    'vb.from_destination',
                    'vb.to_destination',
                    'vb.no_of_people',
                    'vb.rate_per_day',
                    'vb.sub_total',
                    'vb.tax',
                    'vb.tax_amount_type',
                    'vb.discount',
                    'vb.discount_amount_type',
                    'vb.total_amount',
                    'vb.created_at as booking_date',
                    'tc.name as trip_category_name',
                    'tr.title as trip_route_name',

                )
                ->leftJoin('vehicle_bookings as vb', 'vb.id', '=', 'vm.booking_id')
                ->leftJoin('trip_categories as tc', 'tc.id', '=', 'vb.trip_category_id')
                ->leftJoin('trip_routes as tr', 'tr.id', '=', 'vb.trip_route_id')
                ->leftJoin('vehicles as v', 'v.id', '=', 'vb.vehicle_id')

                ->leftJoin('crew_profiles as cp_driver', 'cp_driver.id', '=', 'vb.driver_id')
                ->leftJoin('users as d', 'd.id', '=', 'cp_driver.user_id')

                ->leftJoin('crew_profiles as cp_helper', 'cp_helper.id', '=', 'vb.helper_id')
                ->leftJoin('users as h', 'h.id', '=', 'cp_helper.user_id')

                ->leftJoin('customers as c', 'c.id', '=', 'vb.customer_id')

                ->where('vm.id', $vehicleMoment->id)
                ->first();


            if (!$moment) {
                throw new \Exception();
            }

            // Fetch all questionnaire answers separately
            $questionnaireAnswers = DB::table('vehicle_questionnaire_answers as vqa')
                ->leftJoin('questionnaires as q', 'q.id', '=', 'vqa.questionnaire_id')
                ->select('vqa.answer', 'q.question')
                ->where('vqa.vehicle_moment_id', $vehicleMoment->id)
                ->get();


            return view('layouts.admin.vehicle_moments.show', compact('moment', 'questionnaireAnswers'));
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->route('admin.vehicle_moments.index')
                ->with('error', 'Vehicle moment not found');
        }
    }
}
