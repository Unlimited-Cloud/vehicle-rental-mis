<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Splashscreen;

class SplashScreenController extends Controller
{
    public function index()
    {
        $splashscreens = Splashscreen::latest()->get();
        return view('layouts.admin.splashscreen.index', compact('splashscreens'));
    }

    public function create()
    {
        return view('layouts.admin.splashscreen.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'header' => 'required',
            'description' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'order_by' => 'nullable|integer'
        ]);

        $logoName = null;

        if ($request->hasFile('image')) {
            $logo = $request->file('image');
            $logoName = time() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('uploads/splashscreens'), $logoName);
        }

        Splashscreen::create([
            'header' => $request->header,
            'description' => $request->description,
            'image' => $logoName,
            'order_by' => $request->order_by ?? 0
        ]);

        return redirect()->route('admin.splashscreen.index')->with('success', 'Splashscreen created successfully');
    }

    public function show($id)
    {
        $splashscreen = Splashscreen::findOrFail($id);
        return view('layouts.admin.splashscreen.show', compact('splashscreen'));
    }

    public function edit($id)
    {
        $splashscreen = Splashscreen::findOrFail($id);
        return view('layouts.admin.splashscreen.create', compact('splashscreen'));
    }

    public function update(Request $request, $id)
    {
        $splashscreen = Splashscreen::findOrFail($id);

        $request->validate([
            'header' => 'required',
            'description' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'order_by' => 'nullable|integer'
        ]);

        $imageName = $splashscreen->image;

        if ($request->hasFile('image')) {

            // delete old image
            if ($splashscreen->image && file_exists(public_path('uploads/splashscreens/' . $splashscreen->image))) {
                unlink(public_path('uploads/splashscreens/' . $splashscreen->image));
            }

            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/splashscreens'), $imageName);
        }

        $splashscreen->update([
            'header' => $request->header,
            'description' => $request->description,
            'image' => $imageName,
            'order_by' => $request->order_by ?? 0
        ]);

        return redirect()->route('admin.splashscreen.index')->with('success', 'Splashscreen updated successfully');
    }

    public function destroy($id)
    {
        $splashscreen = Splashscreen::findOrFail($id);

        if ($splashscreen->image && file_exists(public_path('uploads/splashscreens/' . $splashscreen->image))) {
            unlink(public_path('uploads/splashscreens/' . $splashscreen->image));
        }

        $splashscreen->delete();

        return redirect()->route('admin.splashscreen.index')->with('success', 'Splashscreen deleted successfully');
    }
}
