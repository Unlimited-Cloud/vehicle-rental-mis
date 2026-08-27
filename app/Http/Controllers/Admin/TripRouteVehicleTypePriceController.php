<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TripRouteVehicleTypePrice;
use App\Models\VehicleType;
use App\Models\Brand;
use App\Models\FuelType;
use App\Models\Seater;
use Illuminate\Http\Request;

class TripRouteVehicleTypePriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $prices = TripRouteVehicleTypePrice::with('vehicleType')
            ->latest()
            ->get();

        return view(
            'layouts.admin.trip_route_vehicle_type_prices.index',
            compact('prices')
        );
    }

    public function create()
    {
        $vehicleTypes = FuelType::orderBy('name', 'asc')
            ->pluck('name', 'id');

        $brand = Brand::orderBy('name', 'asc')
            ->pluck('name', 'id');

        $seaters = Seater::orderByRaw('CAST(name AS UNSIGNED) ASC')
            ->pluck('name', 'id');

        return view(
            'layouts.admin.trip_route_vehicle_type_prices.create',
            compact('vehicleTypes', 'brand', 'seaters')
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_type' => 'nullable|exists:fuel_type,name',
            'seater' => 'required|exists:seaters,name',
            'brand' => 'required|string|max:255',
            'per_km' => 'nullable|numeric|min:0',
            'per_hour' => 'nullable|numeric|min:0',
            'overnight_price' => 'nullable|numeric|min:0',
        ]);

        TripRouteVehicleTypePrice::create(
            [
                'vehicle_type' => $request->vehicle_type,
                'seater' => $request->seater,
                'brand' => $request->brand,
                'per_km' => $request->per_km,
                'per_hour' => $request->per_hour,
                'overnight_price' => $request->overnight_price,
            ]
        );

        return redirect()
            ->route('admin.trip-routes-vehicle-type-prices.index')
            ->with('success', 'Price saved successfully.');
    }

    public function edit($id)
    {
        $price = TripRouteVehicleTypePrice::findOrFail($id);

        $vehicleTypes = FuelType::orderBy('name', 'asc')
            ->pluck('name', 'id');

        $brand = Brand::orderBy('name', 'asc')
            ->pluck('name', 'id');

        $seaters = Seater::orderByRaw('CAST(name AS UNSIGNED) ASC')
            ->pluck('name', 'id');

        return view(
            'layouts.admin.trip_route_vehicle_type_prices.create',
            compact('price', 'vehicleTypes', 'brand', 'seaters')
        );
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'vehicle_type' => 'nullable|exists:fuel_type,name',
            'seater' => 'required|exists:seaters,name',
            'brand' => 'required|string|max:255',
            'per_km' => 'nullable|numeric|min:0',
            'per_hour' => 'nullable|numeric|min:0',
            'overnight_price' => 'nullable|numeric|min:0',
        ]);

        $price = TripRouteVehicleTypePrice::findOrFail($id);

        $price->update([
            'vehicle_type' => $request->vehicle_type,
            'seater' => $request->seater,
            'brand' => $request->brand,
            'per_km' => $request->per_km,
            'per_hour' => $request->per_hour,
            'overnight_price' => $request->overnight_price,
        ]);

        return redirect()
            ->route('admin.trip-routes-vehicle-type-prices.index')
            ->with('success', 'Price updated successfully.');
    }

    public function destroy($id)
    {
        TripRouteVehicleTypePrice::findOrFail($id)->delete();

        return back()->with('success', 'Price deleted successfully.');
    }
}
