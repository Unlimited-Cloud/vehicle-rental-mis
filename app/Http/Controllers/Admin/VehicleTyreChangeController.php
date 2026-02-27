<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleTyreChange;
use Illuminate\Http\Request;

class VehicleTyreChangeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tyres = VehicleTyreChange::with('vehicle')->latest()->get();
        return view('layouts.admin.vehicle_tyre_changes.index', compact('tyres'));
    }

    public function create()
    {
        $vehicles = Vehicle::all();
        return view('layouts.admin.vehicle_tyre_changes.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'change_date' => 'required|date',
            'tyre_position' => 'required|in:FL,FR,BL,BR',
            'tyre_manufacturer' => 'nullable|string|max:255',
            'tyre_specifications' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric',
            'invoice_upload' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $data = $request->all();

        // Upload Invoice
        if ($request->hasFile('invoice_upload')) {

            $file = $request->file('invoice_upload');
            $fileName = time() . '_tyre_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/tyres'), $fileName);

            $data['invoice_upload'] = 'uploads/tyres/' . $fileName;
        }

        VehicleTyreChange::create($data);


        return redirect()
            ->route('admin.vehicles.show', $request->vehicle_id)
            ->with('success', 'Tyre Change Record Created Successfully')
            ->with('active_tab', 'tyres');
    }

    public function edit(VehicleTyreChange $vehicleTyreChange)
    {
        $vehicles = Vehicle::all();
        return view('layouts.admin.vehicle_tyre_changes.create', compact('vehicleTyreChange', 'vehicles'));
    }

    public function update(Request $request, VehicleTyreChange $vehicleTyreChange)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'change_date' => 'required|date',
            'tyre_position' => 'required|in:FL,FR,BL,BR',
            'tyre_manufacturer' => 'nullable|string|max:255',
            'tyre_specifications' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric',
            'invoice_upload' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $data = $request->all();

        if ($request->hasFile('invoice_upload')) {

            if (
                $vehicleTyreChange->invoice_upload &&
                file_exists(public_path($vehicleTyreChange->invoice_upload))
            ) {
                unlink(public_path($vehicleTyreChange->invoice_upload));
            }

            $file = $request->file('invoice_upload');
            $fileName = time() . '_tyre_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/tyres'), $fileName);

            $data['invoice_upload'] = 'uploads/tyres/' . $fileName;
        }

        $vehicleTyreChange->update($data);

        return redirect()
            ->route('admin.vehicles.show', $request->vehicle_id)
            ->with('success', 'Tyre Change Record Updated Successfully')
            ->with('active_tab', 'tyres');
    }

    public function destroy(VehicleTyreChange $vehicleTyreChange)
    {
        if (
            $vehicleTyreChange->invoice_upload &&
            file_exists(public_path($vehicleTyreChange->invoice_upload))
        ) {
            unlink(public_path($vehicleTyreChange->invoice_upload));
        }

        $vehicleTyreChange->delete();

        return redirect()
            ->route('admin.vehicles.show', $vehicleTyreChange->vehicle_id)
            ->with('success', 'Tyre Change Record Deleted Successfully')
            ->with('active_tab', 'tyres');
    }
}
