<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('index_vehicles_vendor');
        $vendors = Vendor::all();
        return view('layouts.admin.vendors_list.index', compact('vendors'));
    }

    public function create()
    {
        Gate::authorize('create_vehicles_vendor');
        return view('layouts.admin.vendors_list.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('create_vehicles_vendor');
        $request->validate([
            'company_name' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        Vendor::create($request->all());

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor added successfully.');
    }

    public function edit(Vendor $vendor)
    {
        Gate::authorize('update_vehicles_vendor');
        return view('layouts.admin.vendors_list.create', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        Gate::authorize('update_vehicles_vendor');
        $request->validate([
            'company_name' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $vendor->update($request->all());

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor)
    {
        Gate::authorize('delete_vehicles_vendor');
        $vendor->delete();
        return redirect()->route('admin.vendors.index')->with('success', 'Vendor deleted successfully.');
    }

    public function show(Vendor $vendor)
    {
        Gate::authorize('read_vehicles_vendor');
        return view('layouts.admin.vendors_list.show', compact('vendor'));
    }
}
