<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TripCategory;
use App\Models\TripRoute;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TripRoutesExport;
use App\Imports\TripRoutesImport;

class TripRouteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $routes = TripRoute::with('category')->latest()->get();

        return view('layouts.admin.trip_routes.index', compact('routes'));
    }


    public function create()
    {
        $categories = TripCategory::pluck('name', 'id');

        return view('layouts.admin.trip_routes.create', compact('categories'));
    }


    public function store(Request $request)
    {
        TripRoute::create($request->all());

        return redirect()->route('admin.trip-routes.index')
            ->with('success', 'Route created successfully');
    }

    public function show()
    {
        $categories = TripCategory::with('routes')->get();

        return view('layouts.admin.trip_routes.show', compact('categories'));
    }

    public function categoryView()
    {
        $categories = TripCategory::with('routes')->get();
        return view('layouts.admin.trip_routes.show', compact('categories'));
    }
    public function edit($id)
    {
        $route = TripRoute::findOrFail($id);

        $categories = TripCategory::pluck('name', 'id');

        return view(
            'layouts.admin.trip_routes.create',
            compact('route', 'categories')
        );
    }


    public function update(Request $request, $id)
    {
        $route = TripRoute::findOrFail($id);

        $route->update($request->all());

        return redirect()->route('admin.trip-routes.index')
            ->with('success', 'Route updated successfully');
    }


    public function destroy($id)
    {
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
