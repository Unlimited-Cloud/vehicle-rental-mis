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
            'tripRoute',
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

        $tripRoutes = TripRoute::pluck('title', 'id');
        $categories = TripCategory::pluck('name', 'id');
        $vehicles   = Vehicle::pluck('vehicle_name', 'id');

        return view(
            'layouts.admin.trip_route_vehicle_prices.create',
            compact('tripRoutes', 'categories', 'vehicles')
        );
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        // Gate::authorize('create_trip_route_vehicle_prices');

        $request->validate([
            'trip_route_id' => 'required|exists:trip_routes,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'price' => 'required|numeric|min:0',
        ]);

        TripRouteVehiclePrice::create([
            'trip_route_id' => $request->trip_route_id,
            'vehicle_id'    => $request->vehicle_id,
            'price'         => $request->price,
        ]);

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

        $tripRoutes = TripRoute::pluck('title', 'id');
        $categories = TripCategory::pluck('name', 'id');
        $vehicles   = Vehicle::pluck('vehicle_name', 'id');

        return view(
            'layouts.admin.trip_route_vehicle_prices.create',
            compact(
                'price',
                'tripRoutes',
                'categories',
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
            'trip_route_id' => 'required|exists:trip_routes,id',
            'vehicle_id'    => 'required|exists:vehicles,id',
            'price'         => 'required|numeric|min:0',
        ]);

        $price = TripRouteVehiclePrice::findOrFail($id);

        $price->update([
            'trip_route_id' => $request->trip_route_id,
            'vehicle_id'    => $request->vehicle_id,
            'price'         => $request->price,
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
