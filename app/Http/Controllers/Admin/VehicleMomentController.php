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
        try {
            $data = $request->all();

            // Use service to store with all logic
            $this->service->store($data);

            return redirect()->route('admin.vehicle_moments.index')
                ->with('success', 'Vehicle moment created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create vehicle moment: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, $id)
    {
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
                    'vb.end_date'
                )
                ->leftJoin('vehicle_bookings as vb', 'vb.id', '=', 'vm.booking_id')
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
