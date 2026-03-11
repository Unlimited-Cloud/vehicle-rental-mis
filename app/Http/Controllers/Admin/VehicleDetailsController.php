<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleDetail;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VehicleDetailsController extends Controller
{
    public function index()
    {
        $details = VehicleDetail::with('vehicle')->latest()->get();
        return view('layouts.admin.vehicle_details.index', compact('details'));
    }

    public function create(Request $request)
    {
        $vehicles = Vehicle::all();
        $vehicle_id = $request->query('vehicle_id') ?? null;
        return view('layouts.admin.vehicle_details.create', compact('vehicles', 'vehicle_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'blue_book_number' => 'nullable|string',
            'insurance_number' => 'nullable|string',
            'insurance_expiry' => 'nullable|date',
            'permit_number' => 'nullable|string',
            'permit_expiry' => 'nullable|date',
        ]);

        VehicleDetail::create($request->all());

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle details saved successfully');
    }

    public function edit(VehicleDetail $vehicle_detail)
    {
        $vehicles = Vehicle::all();
        return view('layouts.admin.vehicle_details.create', compact('vehicle_detail', 'vehicles'));
    }

    public function update(Request $request, VehicleDetail $vehicle_detail)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'blue_book_number' => 'nullable|string',
            'insurance_number' => 'nullable|string',
            'insurance_expiry' => 'nullable|date',
            'permit_number' => 'nullable|string',
            'permit_expiry' => 'nullable|date',
        ]);

        $vehicle_detail->update($request->all());

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle details updated successfully');
    }

    public function show(VehicleDetail $vehicle_detail)
    {
        $vehicle_detail->load('vehicle');

        return view('layouts.admin.vehicle_details.show', compact('vehicle_detail'));
    }

    public function destroy(VehicleDetail $vehicle_detail)
    {
        $vehicle_detail->delete();

        return redirect()->route('admin.vehicle_details.index')
            ->with('success', 'Vehicle details deleted successfully');
    }
}
