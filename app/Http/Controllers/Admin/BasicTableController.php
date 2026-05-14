<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BasicTable;
use Illuminate\Http\Request;

class BasicTableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = BasicTable::all();
        return view('layouts.admin.basic_tables.index', compact('items'));
    }

    public function create()
    {
        return view('layouts.admin.basic_tables.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'login_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'company_name' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'privacy_policy' => 'nullable|string',
            'about_us' => 'nullable|string',
            'contact_us' => 'nullable|string',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/basic_setups/logo'), $filename);

            $data['logo'] = 'uploads/basic_setups/logo/' . $filename;
        }


        if ($request->hasFile('login_logo')) {
            $file = $request->file('login_logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/basic_setups/login_logo'), $filename);

            $data['login_logo'] = 'uploads/basic_setups/login_logo/' . $filename;
        }

        BasicTable::create($data);

        return redirect()->route('admin.basic_tables.index')
            ->with('success', 'Created successfully');
    }

    public function show(string $id)
    {
        $item = BasicTable::findOrFail($id);
        return view('layouts.admin.basic_tables.show', compact('item'));
    }

    public function edit(string $id)
    {
        $item = BasicTable::findOrFail($id);
        return view('layouts.admin.basic_tables.create', compact('item'));
    }

    public function update(Request $request, string $id)
    {
        $item = BasicTable::findOrFail($id);

        $data = $request->validate([
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'login_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'company_name' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'privacy_policy' => 'nullable|string',
            'about_us' => 'nullable|string',
            'contact_us' => 'nullable|string',
        ]);

        if ($request->hasFile('logo')) {

            // delete old image
            if ($item->logo && file_exists(public_path($item->logo))) {
                unlink(public_path($item->logo));
            }

            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/basic_setups/logo'), $filename);

            $data['logo'] = 'uploads/basic_setups/logo/' . $filename;
        }

        if ($request->hasFile('login_logo')) {

            // delete old image
            if ($item->login_logo && file_exists(public_path($item->login_logo))) {
                unlink(public_path($item->login_logo));
            }

            $file = $request->file('login_logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/basic_setups/login_logo'), $filename);

            $data['login_logo'] = 'uploads/basic_setups/login_logo/' . $filename;
        }

        $item->update($data);

        return redirect()->route('admin.basic_tables.index')
            ->with('success', 'Updated successfully');
    }

    public function destroy(string $id)
    {
        $item = BasicTable::findOrFail($id);
        $item->delete();
        return redirect()->route('admin.basic_tables.index')
            ->with('success', 'Deleted successfully');
    }
}
