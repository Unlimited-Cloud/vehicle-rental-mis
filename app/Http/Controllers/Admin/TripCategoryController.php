<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TripCategory;
use Illuminate\Http\Request;

class TripCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = TripCategory::latest()->get();
        return view('layouts.admin.trip_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('layouts.admin.trip_categories.create');
    }

    public function store(Request $request)
    {
        TripCategory::create($request->all());

        return redirect()->route('admin.trip-categories.index')
            ->with('success', 'Category created');
    }

   

    public function edit($id)
    {
        $category = TripCategory::findOrFail($id);

        return view('layouts.admin.trip_categories.create', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = TripCategory::findOrFail($id);

        $category->update($request->all());

        return redirect()->route('admin.trip-categories.index')
            ->with('success', 'Category updated');
    }

    public function destroy($id)
    {
        TripCategory::destroy($id);

        return back()->with('success', 'Deleted successfully');
    }
}
