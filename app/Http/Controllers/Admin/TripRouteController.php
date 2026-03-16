<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TripCategory;
use App\Models\TripRoute;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TripRoutesExport;
use App\Imports\TripRoutesImport;
use Illuminate\Support\Facades\Gate;

class TripRouteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('index_vehicles_vehicle_route');
        $routes = TripRoute::with('category')->latest()->get();

        return view('layouts.admin.trip_routes.index', compact('routes'));
    }


    public function create()
    {
        Gate::authorize('create_vehicles_vehicle_route');
        $categories = TripCategory::pluck('name', 'id');

        return view('layouts.admin.trip_routes.create', compact('categories'));
    }


    public function store(Request $request)
    {
        Gate::authorize('create_vehicles_vehicle_route');
        TripRoute::create($request->all());

        return redirect()->route('admin.trip-routes.index')
            ->with('success', 'Route created successfully');
    }

    public function show()
    {
        Gate::authorize('read_vehicles_vehicle_route');
        $categories = TripCategory::with('routes')->get();

        return view('layouts.admin.trip_routes.show', compact('categories'));
    }

    public function categoryView()
    {
        Gate::authorize('read_vehicles_vehicle_route');
        $categories = TripCategory::with('routes')->get();
        return view('layouts.admin.trip_routes.show', compact('categories'));
    }
    public function edit($id)
    {
        Gate::authorize('update_vehicles_vehicle_route');
        $route = TripRoute::findOrFail($id);

        $categories = TripCategory::pluck('name', 'id');

        return view(
            'layouts.admin.trip_routes.create',
            compact('route', 'categories')
        );
    }


    public function update(Request $request, $id)
    {
        Gate::authorize('update_vehicles_vehicle_route');
        $route = TripRoute::findOrFail($id);

        $route->update($request->all());

        return redirect()->route('admin.trip-routes.index')
            ->with('success', 'Route updated successfully');
    }


    public function destroy($id)
    {
        Gate::authorize('delete_vehicles_vehicle_route');
        TripRoute::destroy($id);

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
}
