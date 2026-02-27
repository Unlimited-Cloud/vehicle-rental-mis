<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleService;
use Illuminate\Http\Request;

class VehicleServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = VehicleService::with('vehicle')->latest()->get();
        return view('layouts.admin.vehicle_services.index', compact('services'));
    }

    public function create()
    {
        $vehicles = Vehicle::all();
        return view('layouts.admin.vehicle_services.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_date' => 'required|date',
            'service_done_at' => 'required|string|max:255',
            'service_details' => 'nullable|string',
            'service_amount' => 'nullable|numeric',
            'service_bill_copy' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'next_service_km' => 'nullable|integer',
            'next_service_date' => 'nullable|date',
        ]);

        $data = $request->all();

        // Upload Service Bill Copy
        if ($request->hasFile('service_bill_copy')) {
            $file = $request->file('service_bill_copy');
            $fileName = time() . '_service_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/services'), $fileName);
            $data['service_bill_copy'] = 'uploads/services/' . $fileName;
        }

        VehicleService::create($data);


        return redirect()
            ->route('admin.vehicles.show', $request->vehicle_id)
            ->with('success', 'Record Created Successfully')
            ->with('active_tab', 'services');
    }

    public function edit(VehicleService $vehicleService)
    {
        $vehicles = Vehicle::all();
        return view('layouts.admin.vehicle_services.create', compact('vehicleService', 'vehicles'));
    }

    public function update(Request $request, VehicleService $vehicleService)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_date' => 'required|date',
            'service_done_at' => 'required|string|max:255',
            'service_details' => 'nullable|string',
            'service_amount' => 'nullable|numeric',
            'service_bill_copy' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'next_service_km' => 'nullable|integer',
            'next_service_date' => 'nullable|date',
        ]);

        $data = $request->all();

        if ($request->hasFile('service_bill_copy')) {

            if ($vehicleService->service_bill_copy && file_exists(public_path($vehicleService->service_bill_copy))) {
                unlink(public_path($vehicleService->service_bill_copy));
            }

            $file = $request->file('service_bill_copy');
            $fileName = time() . '_service_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/services'), $fileName);
            $data['service_bill_copy'] = 'uploads/services/' . $fileName;
        }

        $vehicleService->update($data);

        return redirect()
            ->route('admin.vehicles.show', $request->vehicle_id)
            ->with('success', 'Record Updated Successfully')
            ->with('active_tab', 'services');
    }

    public function destroy(VehicleService $vehicleService)
    {
        if ($vehicleService->service_bill_copy && file_exists(public_path($vehicleService->service_bill_copy))) {
            unlink(public_path($vehicleService->service_bill_copy));
        }

        $vehicleService->delete();

        return redirect()
            ->route('admin.vehicles.show', $vehicleService->vehicle_id)
            ->with('success', 'Record Deleted Successfully')
            ->with('active_tab', 'services');
    }
}
