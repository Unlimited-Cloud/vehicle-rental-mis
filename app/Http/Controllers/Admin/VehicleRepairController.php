<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrewProfile;
use App\Models\Vehicle;
use App\Models\VehicleRepair;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
        $drivers = CrewProfile::whereHas('user', function ($q) {
            $q->where('role', 'driver');
        })->with('user')->get();
        $vendors = Vendor::all();

        return view('layouts.admin.vehicle_repairs.create', compact('vehicles', 'drivers', 'vendors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'nullable',
            'vendor_id' => 'nullable',
            'bill' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'claim_insurance' => 'nullable',
            'repair_date' => 'required|date',
            'repair_details' => 'required|string',
            'repair_vendor' => 'nullable|string|max:255',
            'repair_amount' => 'nullable|numeric',
            'repair_valid_till' => 'nullable|date',
        ]);

        // Initialize the $data array to hold any additional data (e.g., bill file path)
        $data = [];

        // Handle file upload for 'bill' if it exists
        if ($request->hasFile('bill')) {
            $repairDirectory = public_path('uploads/repairs');

            // Check if the directory exists, if not, create it
            if (!file_exists($repairDirectory)) {
                mkdir($repairDirectory, 0775, true);  // Create the directory if it doesn't exist
            }

            $file = $request->file('bill');
            $fileName = time() . '_repair_' . $file->getClientOriginalName();  // Create a unique file name
            $file->move($repairDirectory, $fileName);  // Move the file to the correct directory

            // Store the relative file path in the $data array
            $data['bill'] = 'uploads/repairs/' . $fileName;  // This is the path that will be saved in the database
        }

        // Merge the $data array with the form data
        VehicleRepair::create(array_merge($request->all(), $data));

        return redirect()
            ->route('admin.vehicles.show', $request->vehicle_id)
            ->with('success', 'Record Created Successfully')
            ->with('active_tab', 'repairs');
    }
    public function edit(VehicleRepair $vehicleRepair)
    {
        $vehicles = Vehicle::all();
        $drivers = CrewProfile::whereHas('user', function ($q) {
            $q->where('role', 'driver');
        })->with('user')->get();
        $vendors = Vendor::all();
        return view('layouts.admin.vehicle_repairs.create', compact('vehicleRepair', 'vehicles', 'drivers', 'vendors'));
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
