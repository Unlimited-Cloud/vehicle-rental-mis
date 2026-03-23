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
        Gate::authorize('index_vehicles_trip_categories');
        $categories = TripCategory::latest()->get();
        return view('layouts.admin.trip_categories.index', compact('categories'));
    }

    public function create()
    {
        Gate::authorize('create_vehicles_trip_categories');
        return view('layouts.admin.trip_categories.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('create_vehicles_trip_categories');
        TripCategory::create($request->all());

        return redirect()->route('admin.trip-categories.index')
            ->with('success', 'Category created');
    }

    public function edit($id)
    {
        Gate::authorize('update_vehicles_trip_categories');
        $category = TripCategory::findOrFail($id);

        return view('layouts.admin.trip_categories.create', compact('category'));
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('update_vehicles_trip_categories');
        $category = TripCategory::findOrFail($id);

        $category->update($request->all());

        return redirect()->route('admin.trip-categories.index')
            ->with('success', 'Category updated');
    }

    public function destroy($id)
    {
        Gate::authorize('delete_vehicles_trip_categories');
        TripCategory::destroy($id);

        return back()->with('success', 'Deleted successfully');
    }
    public function storeAjax(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
        ]);

        $category = TripCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => 1
        ]);

        return response()->json([
            'success' => true,
            'id' => $category->id,
            'name' => $category->name
        ]);
    }
    public function listAjax()
    {
        return TripCategory::select('id', 'name')->get();
    }
}
