<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vehicles = Vehicle::latest()->get();
        return view('layouts.admin.vehicles.index', compact('vehicles'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function create()
    {
        return view('layouts.admin.vehicles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_name' => 'required',
            'brand' => 'required',
            'model' => 'required',
            'year' => 'required|digits:4',
            'rent_price_per_day' => 'required|numeric',
            'fuel_type' => 'required',
            'transmission' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required'
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {

            $image = $request->file('image');

            $imageName = time() . '_' . $image->getClientOriginalName();

            $image->move(public_path('uploads/vehicle'), $imageName);

            $data['image'] = 'uploads/vehicle/' . $imageName;
        }

        Vehicle::create($data);

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle Created Successfully');
    }

    public function edit(Vehicle $vehicle)
    {
        return view('layouts.admin.vehicles.create', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'vehicle_name' => 'required',
            'brand' => 'required',
            'model' => 'required',
            'year' => 'required|digits:4',
            'rent_price_per_day' => 'required|numeric',
            'fuel_type' => 'required',
            'transmission' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required'
        ]);

        $data = $request->all();

        // Image Update
        if ($request->hasFile('image')) {

            // Delete old image
            if ($vehicle->image && file_exists(public_path($vehicle->image))) {
                unlink(public_path($vehicle->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();

            $image->move(public_path('uploads/vehicle'), $imageName);

            $data['image'] = 'uploads/vehicle/' . $imageName;
        }

        $vehicle->update($data);

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle Updated Successfully');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle Deleted Successfully');
    }
}
