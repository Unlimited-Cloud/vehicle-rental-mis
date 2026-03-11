<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehiclePermit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VehiclePermitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permits = VehiclePermit::with('vehicle')->latest()->get();
        return view('layouts.admin.vehicle_permits.index', compact('permits'));
    }

    public function create()
    {
        $vehicles = Vehicle::all();
        return view('layouts.admin.vehicle_permits.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'permit_from_organization' => 'required|string|max:255',
            'permit_expiry_date' => 'required|date',
            'permit_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $data = $request->all();

        // Upload Permit Document
        if ($request->hasFile('permit_document')) {
            $file = $request->file('permit_document');
            $fileName = time() . '_permit_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/permits'), $fileName);
            $data['permit_document'] = 'uploads/permits/' . $fileName;
        }

        VehiclePermit::create($data);

        return redirect()
            ->route('admin.vehicles.show', $request->vehicle_id)
            ->with('success', 'Record Created Successfully')
            ->with('active_tab', 'permits');
    }

    public function edit(VehiclePermit $vehiclePermit)
    {
        $vehicles = Vehicle::all();
        return view('layouts.admin.vehicle_permits.create', compact('vehiclePermit', 'vehicles'));
    }

    public function update(Request $request, VehiclePermit $vehiclePermit)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'permit_from_organization' => 'required|string|max:255',
            'permit_expiry_date' => 'required|date',
            'permit_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $data = $request->all();

        if ($request->hasFile('permit_document')) {

            // Delete old file
            if ($vehiclePermit->permit_document && file_exists(public_path($vehiclePermit->permit_document))) {
                unlink(public_path($vehiclePermit->permit_document));
            }

            $file = $request->file('permit_document');
            $fileName = time() . '_permit_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/permits'), $fileName);
            $data['permit_document'] = 'uploads/permits/' . $fileName;
        }

        $vehiclePermit->update($data);


        return redirect()
            ->route('admin.vehicles.show', $request->vehicle_id)
            ->with('success', 'Record Updated Successfully')
            ->with('active_tab', 'permits');
    }

    public function destroy(VehiclePermit $vehiclePermit)
    {
        if ($vehiclePermit->permit_document && file_exists(public_path($vehiclePermit->permit_document))) {
            unlink(public_path($vehiclePermit->permit_document));
        }

        $vehiclePermit->delete();

        return redirect()
            ->route('admin.vehicles.show', $vehiclePermit->vehicle_id)
            ->with('success', 'Record Deleted Successfully')
            ->with('active_tab', 'permits');
    }
}
