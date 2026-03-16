<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TripCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TripCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('index_vehicles_vehicle_category');
        $categories = TripCategory::latest()->get();
        return view('layouts.admin.trip_categories.index', compact('categories'));
    }

    public function create()
    {
        Gate::authorize('create_vehicles_vehicle_category');
        return view('layouts.admin.trip_categories.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('create_vehicles_vehicle_category');
        TripCategory::create($request->all());

        return redirect()->route('admin.trip-categories.index')
            ->with('success', 'Category created');
    }



    public function edit($id)
    {
        Gate::authorize('update_vehicles_vehicle_category');
        $category = TripCategory::findOrFail($id);

        return view('layouts.admin.trip_categories.create', compact('category'));
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('update_vehicles_vehicle_category');
        $category = TripCategory::findOrFail($id);

        $category->update($request->all());

        return redirect()->route('admin.trip-categories.index')
            ->with('success', 'Category updated');
    }

    public function destroy($id)
    {
        Gate::authorize('delete_vehicles_vehicle_category');
        TripCategory::destroy($id);

        return back()->with('success', 'Deleted successfully');
    }
}
