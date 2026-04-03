<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = Brand::latest()->get();
        return view('layouts.admin.brand.index', compact('brands'));
    }

    public function create()
    {
        return view('layouts.admin.brand.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $logoName = null;

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('uploads/brands'), $logoName);
        }

        Brand::create([
            'name' => $request->name,
            'logo' => $logoName
        ]);

        return redirect()->route('admin.brand.index')->with('success', 'Brand created successfully');
    }

    public function show($id)
    {
        $brand = Brand::findOrFail($id);
        return view('layouts.admin.brand.show', compact('brand'));
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        return view('layouts.admin.brand.create', compact('brand'));
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $logoName = $brand->logo;

        if ($request->hasFile('logo')) {

            // delete old logo
            if ($brand->logo && file_exists(public_path('uploads/brands/' . $brand->logo))) {
                unlink(public_path('uploads/brands/' . $brand->logo));
            }

            $logo = $request->file('logo');
            $logoName = time() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('uploads/brands'), $logoName);
        }

        $brand->update([
            'name' => $request->name,
            'logo' => $logoName
        ]);

        return redirect()->route('admin.brand.index')->with('success', 'Brand updated successfully');
    }

    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);

        if ($brand->logo && file_exists(public_path('uploads/brands/' . $brand->logo))) {
            unlink(public_path('uploads/brands/' . $brand->logo));
        }

        $brand->delete();

        return redirect()->route('admin.brand.index')->with('success', 'Brand deleted successfully');
    }
}
