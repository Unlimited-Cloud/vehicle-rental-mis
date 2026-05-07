<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::latest()->get();
        return view('layouts.admin.banner.index', compact('banners'));
    }

    public function create()
    {
        return view('layouts.admin.banner.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'link' => 'nullable|url',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer'
        ]);

        $imageName = null;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/banners'), $imageName);
        }

        Banner::create([
            'title' => $request->title,
            'image' => $imageName,
            'link' => $request->link,
            'description' => $request->description,
            'is_active' => $request->is_active ?? 1,
            'order' => $request->order ?? 0,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->route('admin.banner.index')->with('success', 'Banner created successfully');
    }

    public function show($id)
    {
        $banner = Banner::findOrFail($id);
        return view('layouts.admin.banner.show', compact('banner'));
    }

    public function edit($id)
    {
        $banner = Banner::findOrFail($id);
        return view('layouts.admin.banner.create', compact('banner'));
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $request->validate([
            'title' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'link' => 'nullable|url',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer'
        ]);

        $imageName = $banner->image;

        if ($request->hasFile('image')) {

            // delete old image
            if ($banner->image && file_exists(public_path('uploads/banners/' . $banner->image))) {
                unlink(public_path('uploads/banners/' . $banner->image));
            }

            $image = $request->file('image');
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/banners'), $imageName);
        }

        $banner->update([
            'title' => $request->title,
            'image' => $imageName,
            'link' => $request->link,
            'description' => $request->description,
            'is_active' => $request->is_active ?? 1,
            'order' => $request->order ?? 0,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->route('admin.banner.index')->with('success', 'Banner updated successfully');
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);

        if ($banner->image && file_exists(public_path('uploads/banners/' . $banner->image))) {
            unlink(public_path('uploads/banners/' . $banner->image));
        }

        $banner->delete();

        return redirect()->route('admin.banner.index')->with('success', 'Banner deleted successfully');
    }
}
