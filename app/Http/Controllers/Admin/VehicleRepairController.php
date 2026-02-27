<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleRepair;
use Illuminate\Http\Request;

class VehicleRepairController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $repairs = VehicleRepair::with('vehicle')->latest()->get();
        return view('layouts.admin.vehicle_repairs.index', compact('repairs'));
    }

    public function create()
    {
        $vehicles = Vehicle::all();
        return view('layouts.admin.vehicle_repairs.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'repair_date' => 'required|date',
            'repair_details' => 'required|string',
            'repair_vendor' => 'nullable|string|max:255',
            'repair_amount' => 'nullable|numeric',
            'repair_valid_till' => 'nullable|date',
        ]);

        VehicleRepair::create($request->all());


        return redirect()
            ->route('admin.vehicles.show', $request->vehicle_id)
            ->with('success', 'Record Created Successfully')
            ->with('active_tab', 'repairs');
    }

    public function edit(VehicleRepair $vehicleRepair)
    {
        $vehicles = Vehicle::all();
        return view('layouts.admin.vehicle_repairs.create', compact('vehicleRepair', 'vehicles'));
    }

    public function update(Request $request, VehicleRepair $vehicleRepair)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'repair_date' => 'required|date',
            'repair_details' => 'required|string',
            'repair_vendor' => 'nullable|string|max:255',
            'repair_amount' => 'nullable|numeric',
            'repair_valid_till' => 'nullable|date',
        ]);

        $vehicleRepair->update($request->all());

        return redirect()
            ->route('admin.vehicles.show', $request->vehicle_id)
            ->with('success', 'Record Updated Successfully')
            ->with('active_tab', 'repairs');
    }

    public function destroy(VehicleRepair $vehicleRepair)
    {
        $vehicleRepair->delete();

        return redirect()
            ->route('admin.vehicles.show', $vehicleRepair->vehicle_id)
            ->with('success', 'Record Deleted Successfully')
            ->with('active_tab', 'repairs');
    }
}
