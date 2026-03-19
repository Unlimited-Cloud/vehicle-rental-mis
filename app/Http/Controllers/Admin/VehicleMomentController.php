<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\VehicleMomentService;
use App\Models\VehicleBooking;
use App\Models\CrewProfile;
use App\Models\User;
use App\Models\VehicleMoment;
use Illuminate\Support\Facades\DB;

class VehicleMomentController extends Controller
{
    protected $service;

    public function __construct(VehicleMomentService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $moments = $this->service->getAll();

        return view('layouts.admin.vehicle_moments.index', compact('moments'));
    }

    public function create(Request $request)
    {
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
            ->select('id', 'vehicle_name')
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

        $questionnaires = DB::table('questionnaires')
            ->select('id', 'question', 'type', 'is_required', 'sort_order')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        return view('layouts.admin.vehicle_moments.create', compact('booking', 'vehicles', 'drivers', 'helpers', 'questionnaires'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_km' => 'required|numeric|min:0',
            'end_km' => 'required|numeric|gt:start_km',
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
            ->select('id', 'vehicle_name')
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

        return view('layouts.admin.vehicle_moments.create', compact(
            'moment',
            'booking',
            'vehicles',
            'drivers',
            'helpers',
            'questionnaires',
            'answers'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'start_km' => 'required|numeric|min:0',
            'end_km' => 'required|numeric|gt:start_km',
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
