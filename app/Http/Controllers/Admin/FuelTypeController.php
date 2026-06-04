<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FuelType;

class FuelTypeController extends Controller
{
    public function index()
    {
        $fuelTypes = FuelType::latest()->get();
        return view('layouts.admin.fuel_type.index', compact('fuelTypes'));
    }

    public function create()
    {
        return view('layouts.admin.fuel_type.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'logo'   => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'status' => 'required|boolean',
        ]);

        $logoName = null;

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = uniqid() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('uploads/fuel-types'), $logoName);
        }

        FuelType::create([
            'name'   => $request->name,
            'logo'   => $logoName,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('admin.fuel-type.index')
            ->with('success', 'Fuel type created successfully.');
    }

    public function edit($id)
    {
        $fuelType = FuelType::findOrFail($id);

        return view('layouts.admin.fuel_type.create', compact('fuelType'));
    }

    public function update(Request $request, $id)
    {
        $fuelType = FuelType::findOrFail($id);

        $request->validate([
            'name'   => 'required|string|max:255',
            'logo'   => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'status' => 'required|boolean',
        ]);

        $logoName = $fuelType->logo;

        if ($request->hasFile('logo')) {

            if (
                $fuelType->logo &&
                file_exists(public_path('uploads/fuel-types/' . $fuelType->logo))
            ) {
                unlink(public_path('uploads/fuel-types/' . $fuelType->logo));
            }

            $logo = $request->file('logo');
            $logoName = uniqid() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('uploads/fuel-types'), $logoName);
        }

        $fuelType->update([
            'name'   => $request->name,
            'logo'   => $logoName,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('admin.fuel-type.index')
            ->with('success', 'Fuel type updated successfully.');
    }

    public function destroy($id)
    {
        $fuelType = FuelType::findOrFail($id);

        if (
            $fuelType->logo &&
            file_exists(public_path('uploads/fuel-types/' . $fuelType->logo))
        ) {
            unlink(public_path('uploads/fuel-types/' . $fuelType->logo));
        }

        $fuelType->delete();

        return redirect()
            ->route('admin.fuel-type.index')
            ->with('success', 'Fuel type deleted successfully.');
    }
}
