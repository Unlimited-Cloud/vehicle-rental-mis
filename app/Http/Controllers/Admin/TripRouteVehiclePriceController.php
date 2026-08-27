<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TripRoute;
use App\Models\Vehicle;
use App\Models\TripCategory;
use App\Models\TripRouteVehiclePrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TripRouteVehiclePriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Gate::authorize('index_trip_route_vehicle_prices');

        $prices = TripRouteVehiclePrice::with([
            'vehicle'
        ])->latest()->get();

        return view(
            'layouts.admin.trip_route_vehicle_prices.index',
            compact('prices')
        );
    }
    public function vehicleView()
    {
        // Gate::authorize('index_trip_route_vehicle_prices');

        // Get ALL vehicles with their prices - DON'T FILTER
        $vehicles = Vehicle::with([
            'routePrices' => function ($query) {
                $query->with(['tripRoute.category']);
            }
        ])->get();

        // Get all categories for filter
        $categories = TripCategory::where('deleted_at', null)->get();

        // Get ALL prices - DON'T FILTER BY VEHICLE
        $prices = TripRouteVehiclePrice::with([
            'tripRoute.category',
            'vehicle'
        ])->get();


        return view(
            'layouts.admin.trip_route_vehicle_prices.vehicle-view',
            compact('vehicles', 'categories', 'prices')
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Gate::authorize('create_trip_route_vehicle_prices');

        $vehicles   = Vehicle::pluck('vehicle_name', 'id');

        return view(
            'layouts.admin.trip_route_vehicle_prices.create',
            compact('vehicles')
        );
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        // Gate::authorize('create_trip_route_vehicle_prices');

        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'per_km'     => 'nullable|numeric|min:0',
            'per_hour'   => 'nullable|numeric|min:0',
            'price'   => 'nullable|numeric|min:0',
            'overnight'  => 'nullable|boolean',
        ]);

        TripRouteVehiclePrice::updateOrCreate(
            [
                'vehicle_id' => $request->vehicle_id,
                'trip_route_id' => null,
            ],
            [
                'per_km'    => $request->per_km,
                'per_hour'  => $request->per_hour,
                'price'   => $request->price,
                'overnight' => $request->overnight,
            ]
        );

        return redirect()
            ->route('admin.trip-routes-vehicle-prices.index')
            ->with('success', 'Price added successfully');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Gate::authorize('update_trip_route_vehicle_prices');

        $price = TripRouteVehiclePrice::findOrFail($id);
        $vehicles   = Vehicle::pluck('vehicle_name', 'id');

        return view(
            'layouts.admin.trip_route_vehicle_prices.create',
            compact(
                'price',
                'vehicles'
            )
        );
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, $id)
    {
        // Gate::authorize('update_trip_route_vehicle_prices');

        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'per_km'     => 'nullable|numeric|min:0',
            'per_hour'   => 'nullable|numeric|min:0',
            'price'   => 'nullable|numeric|min:0',
            'overnight'  => 'nullable|boolean',
        ]);

        $price = TripRouteVehiclePrice::findOrFail($id);

        $price->update([
            'vehicle_id' => $request->vehicle_id,
            'per_km'     => $request->per_km,
            'per_hour'   => $request->per_hour,
            'price'   => $request->price,
            'overnight'  => $request->overnight,
        ]);

        return redirect()
            ->route('admin.trip-routes-vehicle-prices.index')
            ->with('success', 'Price updated successfully');
    }

    public function show()
    {
        $categories = TripCategory::with(['routes'])->get();
        $vehicles = Vehicle::get();

        return view('layouts.admin.trip_routes.show', compact('categories', 'vehicles'));
    }

    /**
     * Remove the specified resource.
     */
    public function destroy($id)
    {
        // Gate::authorize('delete_trip_route_vehicle_prices');

        $price = TripRouteVehiclePrice::findOrFail($id);
        $price->delete();

        return back()->with(
            'success',
            'Price deleted successfully'
        );
    }
}
