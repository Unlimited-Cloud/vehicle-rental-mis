<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\VehicleAssignment;
use Illuminate\Support\Facades\Gate;

class VehicleAssignmentController extends Controller
{
    public function index()
    {
        Gate::authorize('index_vehicles_vehicle_assignment');
        $assignments = VehicleAssignment::with(['vehicle', 'driver', 'helper'])->latest()->get();
        return view('layouts.admin.vehicle_assignments.index', compact('assignments'));
    }

    public function create()
    {
        Gate::authorize('create_vehicles_vehicle_assignment');
        $vehicles = Vehicle::where('status', 1)->get(); // only available vehicles
        $drivers = User::whereHas('crewProfile', function ($q) {
            $q->where('role', 'driver');
        })->get();
        $helpers = User::whereHas('crewProfile', function ($q) {
            $q->where('role', 'helper');
        })->get();

        return view('layouts.admin.vehicle_assignments.create', compact('vehicles', 'drivers', 'helpers'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create_vehicles_vehicle_assignment');
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:users,id',
            'helper_id' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'shift' => 'nullable|string',
        ]);

        VehicleAssignment::create($request->all());

        return redirect()->route('admin.vehicle_assignments.index')
            ->with('success', 'Vehicle assigned successfully.');
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VehicleAssignment $vehicle_assignment)
    {
        Gate::authorize('update_vehicles_vehicle_assignment');
        $vehicles = Vehicle::where('status', 1)->get();
        $drivers = User::whereHas('crewProfile', fn($q) => $q->where('role', 'driver'))->get();
        $helpers = User::whereHas('crewProfile', fn($q) => $q->where('role', 'helper'))->get();

        return view('layouts.admin.vehicle_assignments.create', [
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'helpers' => $helpers,
            'vehicle_assignment' => $vehicle_assignment
        ]);
    }

    /**
     * Update the specified assignment.
     */
    public function update(Request $request, VehicleAssignment $vehicle_assignment)
    {
        Gate::authorize('update_vehicles_vehicle_assignment');
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:users,id',
            'helper_id' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'shift' => 'nullable|string',
        ]);

        $vehicle_assignment->update($request->all());

        return redirect()->route('admin.vehicle_assignments.index')
            ->with('success', 'Vehicle assignment updated successfully.');
    }

    /**
     * Remove the specified assignment.
     */
    public function destroy(VehicleAssignment $vehicle_assignment)
    {
        Gate::authorize('delete_vehicles_vehicle_assignment');
        $vehicle_assignment->delete();

        return redirect()->route('admin.vehicle_assignments.index')
            ->with('success', 'Vehicle assignment deleted successfully.');
    }
}
