<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seater;
use Illuminate\Http\Request;

class SeaterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $seaters = Seater::latest()->get();
        return view('layouts.admin.seater.index', compact('seaters'));
    }

    public function create()
    {
        return view('layouts.admin.seater.create');
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

        Seater::create([
            'name' => $request->name,
            'logo' => $logoName
        ]);

        return redirect()->route('admin.seater.index')->with('success', 'Seater created successfully');
    }

    public function show($id)
    {
        $seater = Seater::findOrFail($id);
        return view('layouts.admin.seater.show', compact('seater'));
    }

    public function edit($id)
    {
        $seater = Seater::findOrFail($id);
        return view('layouts.admin.seater.create', compact('seater'));
    }

    public function update(Request $request, $id)
    {
        $seater = Seater::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $logoName = $seater->logo;

        if ($request->hasFile('logo')) {

            // delete old logo
            if ($seater->logo && file_exists(public_path('uploads/seaters/' . $seater->logo))) {
                unlink(public_path('uploads/seaters/' . $seater->logo));
            }

            $logo = $request->file('logo');
            $logoName = time() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('uploads/seaters'), $logoName);
        }

        $seater->update([
            'name' => $request->name,
            'logo' => $logoName
        ]);

        return redirect()->route('admin.seater.index')->with('success', 'Seater updated successfully');
    }

    public function destroy($id)
    {
        $seater = Seater::findOrFail($id);

        if ($seater->logo && file_exists(public_path('uploads/seaters/' . $seater->logo))) {
            unlink(public_path('uploads/seaters/' . $seater->logo));
        }

        $seater->delete();

        return redirect()->route('admin.seater.index')->with('success', 'Seater deleted successfully');
    }
}
