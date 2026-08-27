<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TripCategory;
use App\Models\TripRoute;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TripRoutesExport;
use App\Imports\TripRoutesImport;
use App\Imports\TripRoutesPriceImport;


use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class TripRouteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('index_vehicles_trip_routes');

        $routes = TripRoute::with('category')->whereNull('deleted_at')->latest()->get();
        $categories = TripCategory::whereNull('deleted_at')->orderBy('name')->pluck('name', 'id');

        return view('layouts.admin.trip_routes.index', compact('routes', 'categories'));
    }


    public function create()
    {
        Gate::authorize('create_vehicles_trip_routes');
        $categories = TripCategory::pluck('name', 'id');

        return view('layouts.admin.trip_routes.create', compact('categories'));
    }


    public function store(Request $request)
    {
        Gate::authorize('create_vehicles_trip_routes');
        TripRoute::create($request->all());

        return redirect()->route('admin.trip-routes.index')
            ->with('success', 'Route created successfully');
    }

    public function show()
    {
        Gate::authorize('read_vehicles_trip_routes');
        $categories = TripCategory::with('routes')->get();

        return view('layouts.admin.trip_routes.show', compact('categories'));
    }

    public function categoryView()
    {
        Gate::authorize('read_vehicles_trip_routes');
        $categories = TripCategory::with('routes')->whereNull('deleted_at')->get();
        return view('layouts.admin.trip_routes.show', compact('categories'));
    }
    public function edit($id)
    {
        Gate::authorize('update_vehicles_trip_routes');
        $route = TripRoute::findOrFail($id);

        $categories = TripCategory::pluck('name', 'id');

        return view(
            'layouts.admin.trip_routes.create',
            compact('route', 'categories')
        );
    }


    public function update(Request $request, $id)
    {
        Gate::authorize('update_vehicles_trip_routes');
        $route = TripRoute::findOrFail($id);

        $route->update($request->all());

        return redirect()->route('admin.trip-routes.index')
            ->with('success', 'Route updated successfully');
    }


    public function destroy($id)
    {
        Gate::authorize('delete_vehicles_trip_routes');

        $tripRoute = TripRoute::findOrFail($id);
        $tripRoute->deleted_by = Auth::id();
        $tripRoute->save();
        $tripRoute->delete();

        return back()->with('success', 'Deleted successfully');
    }


    public function export()
    {
        return Excel::download(new TripRoutesExport, 'trip_routes.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls'
        ]);

        Excel::import(new TripRoutesImport, $request->file('excel_file'));

        return redirect()->back()->with('success', 'Trip routes uploaded successfully!');
    }

    public function upload()
    {
        return view('layouts.admin.trip_routes.upload'); // Blade file path
    }


    public function importRoutePrice(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        Excel::import(new TripRoutesPriceImport, $request->file('file'));

        return redirect()->back()->with('success', 'Trip routes price uploaded successfully!');
    }

    public function uploadTripPrice()
    {
        return view('layouts.admin.trip_route_vehicle_prices.upload'); // Blade file path
    }



    public function storeAjax(Request $request)
    {
        $request->validate([
            'trip_category_id' => 'required|exists:trip_categories,id',
            'title' => 'required|max:255',
        ]);

        $route = TripRoute::create([
            'trip_category_id' => $request->trip_category_id,
            'title' => $request->title,
            'km' => $request->km ?? 0,
            'car_price' => $request->car_price ?? 0,
            'hiace_price' => $request->hiace_price ?? 0,
            'coaster_price' => $request->coaster_price ?? 0,
            'bus_price' => $request->bus_price ?? 0,
            'van_price' => $request->van_price ?? 0,
            'status' => 1
        ]);

        return response()->json([
            'success' => true,
            'id' => $route->id,
            'title' => $route->title
        ]);
    }
    public function listAjax()
    {
        return TripRoute::select('id', 'title')->get();
    }
}
