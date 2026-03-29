<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrewProfile;
use App\Models\Vehicle;
use App\Models\VehicleAssign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class VehicleAssignController extends Controller
{
    public function index()
    {
        // Gate::authorize('index_vehicles_vehicle_assign');

        $assigns = VehicleAssign::with(['vehicle', 'driver', 'helper'])
            ->latest()
            ->get();

        return view('layouts.admin.vehicle_assign.index', compact('assigns'));
    }

    public function create()
    {
        // Gate::authorize('create_vehicles_vehicle_assign');

        $vehicles = Vehicle::all();
        $drivers = CrewProfile::where('role', 'driver')->get();
        $helpers = CrewProfile::where('role', 'helper')->get();

        return view('layouts.admin.vehicle_assign.create', compact('vehicles', 'drivers', 'helpers'));
    }

    public function store(Request $request)
    {
        // Gate::authorize('create_vehicles_vehicle_assign');

        $request->validate([
            'date' => 'required|date',
            'vehicle_id' => [
                'required',
                Rule::unique('vehicle_assigns')->where(function ($q) use ($request) {
                    return $q->where('date', $request->date);
                })
            ],
            'driver_id' => 'required|exists:crew_profiles,id',
            'helper_id' => 'nullable|exists:crew_profiles,id',
            'remarks' => 'nullable|string',
        ]);

        VehicleAssign::create($request->all());

        return redirect()->route('admin.vehicle_assign.index')
            ->with('success', 'Vehicle assigned successfully');
    }

    public function edit(VehicleAssign $vehicle_assign)
    {
        // Gate::authorize('update_vehicles_vehicle_assign');

        $vehicles = Vehicle::all();
        $drivers = CrewProfile::where('role', 'driver')->get();
        $helpers = CrewProfile::where('role', 'helper')->get();

        return view('layouts.admin.vehicle_assign.create', compact(
            'vehicle_assign',
            'vehicles',
            'drivers',
            'helpers'
        ));
    }

    public function update(Request $request, VehicleAssign $vehicle_assign)
    {
        // Gate::authorize('update_vehicles_vehicle_assign');

        $request->validate([
            'date' => 'required|date',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:crew_profiles,id',
            'helper_id' => 'nullable|exists:crew_profiles,id',
            'remarks' => 'nullable|string',
        ]);

        $vehicle_assign->update($request->all());

        return redirect()->route('admin.vehicle_assign.index')
            ->with('success', 'Vehicle assignment updated successfully');
    }

    public function show(VehicleAssign $vehicle_assign)
    {
        // Gate::authorize('read_vehicles_vehicle_assign');

        $vehicle_assign->load(['vehicle', 'driver', 'helper']);

        return view('layouts.admin.vehicle_assign.show', compact('vehicle_assign'));
    }

    public function destroy(VehicleAssign $vehicle_assign)
    {
        // Gate::authorize('delete_vehicles_vehicle_assign');

        $vehicle_assign->delete();

        return redirect()->route('admin.vehicle_assign.index')
            ->with('success', 'Vehicle assignment deleted successfully');
    }
}
