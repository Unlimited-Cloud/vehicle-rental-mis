<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrewProfile;
use Illuminate\Http\Request;
use App\Models\FuelPurchase;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\PetrolPump;

class FuelPurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fuels = FuelPurchase::with(['vehicle', 'driver', 'petrolPump'])
            ->latest()
            ->paginate(10);

        return view('layouts.admin.fuel_purchased.index', compact('fuels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vehicles = Vehicle::where('status', '1')->get();

        $drivers = CrewProfile::where('role', 'driver')
            ->with('user')
            ->get()
            ->pluck('user.name', 'id');
        $pumps = PetrolPump::all();

        return view('layouts.admin.fuel_purchased.create', compact('vehicles', 'drivers', 'pumps'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'date_time' => 'required',
            'vehicle_id' => 'required',
            'driver_id' => 'required',
            'petrol_pump_id' => 'required',
            'liters' => 'required|numeric',
            'rate' => 'required|numeric',
            'amount' => 'required|numeric',
            'pump_before' => 'nullable|image',
            'pump_after' => 'nullable|image',
            'tank_before' => 'nullable|image',
            'tank_after' => 'nullable|image',
        ]);

        $uploadPath = public_path('uploads/fuel_purchased');

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        foreach (['pump_before', 'pump_after', 'tank_before', 'tank_after'] as $image) {

            if ($request->hasFile($image)) {

                $file = $request->file($image);

                $filename = time() . '_' . $image . '.' . $file->getClientOriginalExtension();

                $file->move($uploadPath, $filename);

                $data[$image] = 'uploads/fuel_purchased/' . $filename;
            }
        }

        FuelPurchase::create($data);

        return redirect()->route('admin.fuel_purchased.index')->with('success', 'Fuel Purchased created successfully.');
    }

    public function edit(FuelPurchase $fuel_purchased)
    {
        $vehicles = Vehicle::where('status', '1')->get();
        $drivers = CrewProfile::where('role', 'driver')
            ->with('user')
            ->get()
            ->pluck('user.name', 'id');
        $pumps = PetrolPump::all();

        return view('layouts.admin.fuel_purchased.create', [
            'fuel_purchased' => $fuel_purchased,
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'pumps' => $pumps,
        ]);
    }

    public function update(Request $request, FuelPurchase $fuel_purchased)
    {
        $data = $request->validate([
            'date_time' => 'required',
            'vehicle_id' => 'required',
            'driver_id' => 'required',
            'petrol_pump_id' => 'required',
            'liters' => 'required|numeric',
            'rate' => 'required|numeric',
            'amount' => 'required|numeric',
            'pump_before' => 'nullable|image',
            'pump_after' => 'nullable|image',
            'tank_before' => 'nullable|image',
            'tank_after' => 'nullable|image',
        ]);

        $uploadPath = public_path('uploads/fuel_purchased');

        foreach (['pump_before', 'pump_after', 'tank_before', 'tank_after'] as $image) {
            if ($request->hasFile($image)) {
                $file = $request->file($image);
                $filename = time() . '_' . $image . '.' . $file->getClientOriginalExtension();
                $file->move($uploadPath, $filename);
                $data[$image] = 'uploads/fuel_purchased/' . $filename;
            } else {
                // Preserve old value
                $data[$image] = $fuel_purchased->$image;
            }
        }

        $fuel_purchased->update($data);

        return redirect()->route('admin.fuel_purchased.index')->with('success', 'Fuel Purchased updated successfully.');
    }

    public function show($id)
    {
        $fuel = FuelPurchase::findOrFail($id);

        return view('layouts.admin.fuel_purchased.show', compact('fuel'));
    }

    public function destroy(FuelPurchase $fuel_purchased)
    {
        $fuel_purchased->delete();

        return redirect()->route('admin.fuel_purchased.index')->with('success', 'Fuel Purchased deleted successfully.');
    }
}
