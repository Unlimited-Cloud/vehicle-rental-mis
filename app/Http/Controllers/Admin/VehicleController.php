<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\FuelType;
use App\Models\Seater;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('index_vehicles');
        $vehicles = Vehicle::latest()->get();
        return view('layouts.admin.vehicles.index', compact('vehicles'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function create()
    {
        Gate::authorize('create_vehicles');
        $brands = Brand::latest()->get();
        $seaters = Seater::latest()->get();
        $fuel_type = FuelType::latest()->get();
        return view('layouts.admin.vehicles.create', compact('brands', 'seaters', 'fuel_type'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create_vehicles');
        $request->validate([
            'vehicle_name' => 'required',
            'vehicle_type' => 'nullable',
            'brand' => 'required',
            'model' => 'required',
            'seater' => 'nullable|integer|min:1',
            'year' => 'required|digits:4',
            'rent_price_per_day' => 'nullable|numeric',
            'fuel_type' => 'required',
            'transmission' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required',
            'is_helper_needed' => 'nullable',

            // Registration
            'registration_number' => 'nullable|string|max:255',
            'registered_at' => 'nullable|string|max:255',
            'number_plate_color' => 'nullable|in:RED,BLACK,GREEN',
            'registration_expiry' => 'nullable|date',
            'bill_book_number' => 'nullable|string|max:255',
            'bill_book_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            // Insurance
            'insurance_policy_no' => 'nullable|string|max:255',
            'insurance_company' => 'nullable|string|max:255',
            'insurance_type' => 'nullable|string|max:255',
            'insurance_till' => 'nullable|date',
            'insurance_cost_per_annum' => 'nullable|numeric',
            'insurance_policy_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',

            'mileage' => 'nullable|integer|min:0',
            'horsepower' => 'nullable|integer|min:0',
            'car_color' => 'nullable|string|max:100',
            'description' => 'nullable|string',

            // multiple images
            'car_images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {

            $image = $request->file('image');

            $imageName = time() . '_' . $image->getClientOriginalName();

            $image->move(public_path('uploads/vehicle'), $imageName);

            $data['image'] = 'uploads/vehicle/' . $imageName;
        }

        if ($request->hasFile('car_images')) {

            $images = [];

            foreach ($request->file('car_images') as $file) {
                if (!$file) continue;

                $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/vehicle'), $fileName);

                $images[] = 'uploads/vehicle/' . $fileName;
            }

            $data['car_images'] = !empty($images) ? $images : null;
        } else {

            $data['car_images'] = null;
        }

        // Bill Book Image
        if ($request->hasFile('bill_book_image')) {
            $file = $request->file('bill_book_image');
            $fileName = time() . '_bill_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/vehicle'), $fileName);
            $data['bill_book_image'] = 'uploads/vehicle/' . $fileName;
        }

        // Insurance Policy Document
        if ($request->hasFile('insurance_policy_document')) {
            $file = $request->file('insurance_policy_document');
            $fileName = time() . '_insurance_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/vehicle'), $fileName);
            $data['insurance_policy_document'] = 'uploads/vehicle/' . $fileName;
        }

        Vehicle::create($data);

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle Created Successfully');
    }

    public function show(Vehicle $vehicle)
    {
        Gate::authorize('read_vehicles');
        $vehicle->load(['permits', 'services', 'repairs', 'repairs.vendor', 'repairs.driver', 'tyreChanges']);

        return view('layouts.admin.vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle)
    {
        Gate::authorize('update_vehicles');
        $brands = Brand::latest()->get();
        $seaters = Seater::latest()->get();
        $fuel_type = FuelType::latest()->get();
        return view('layouts.admin.vehicles.create', compact('vehicle', 'brands', 'seaters', 'fuel_type'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        Gate::authorize('update_vehicles');
        $request->validate([
            'vehicle_name' => 'required',
            'vehicle_type' => 'nullable',
            'brand' => 'required',
            'model' => 'required',
            'seater' => 'nullable|integer|min:1',
            'year' => 'required|digits:4',
            'rent_price_per_day' => 'nullable|numeric',
            'fuel_type' => 'required',
            'transmission' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required',
            'is_helper_needed' => 'nullable',

            // Registration
            'registration_number' => 'nullable|string|max:255',
            'registered_at' => 'nullable|string|max:255',
            'number_plate_color' => 'nullable|in:RED,BLACK,GREEN',
            'registration_expiry' => 'nullable|date',
            'bill_book_number' => 'nullable|string|max:255',
            'bill_book_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            // Insurance
            'insurance_policy_no' => 'nullable|string|max:255',
            'insurance_company' => 'nullable|string|max:255',
            'insurance_type' => 'nullable|string|max:255',
            'insurance_till' => 'nullable|date',
            'insurance_cost_per_annum' => 'nullable|numeric',
            'insurance_policy_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',

            'mileage' => 'nullable|integer|min:0',
            'horsepower' => 'nullable|integer|min:0',
            'car_color' => 'nullable|string|max:100',
            'description' => 'nullable|string',

            // multiple images
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->all();

        // Image Update
        if ($request->hasFile('image')) {

            // Delete old image
            if ($vehicle->image && file_exists(public_path($vehicle->image))) {
                unlink(public_path($vehicle->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();

            $image->move(public_path('uploads/vehicle'), $imageName);

            $data['image'] = 'uploads/vehicle/' . $imageName;
        }

        // MULTIPLE IMAGES UPDATE
        if ($request->hasFile('car_images')) {

            // delete old images
            if ($vehicle->car_images) {
                foreach ($vehicle->car_images as $oldImage) {
                    if (file_exists(public_path($oldImage))) {
                        unlink(public_path($oldImage));
                    }
                }
            }

            $images = [];

            foreach ($request->file('car_images') as $file) {
                $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/vehicle'), $fileName);
                $images[] = 'uploads/vehicle/' . $fileName;
            }

            $data['car_images'] = $images;
        }

        // Bill Book Image
        if ($request->hasFile('bill_book_image')) {
            $file = $request->file('bill_book_image');
            $fileName = time() . '_bill_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/vehicle'), $fileName);
            $data['bill_book_image'] = 'uploads/vehicle/' . $fileName;
        }

        // Insurance Policy Document
        if ($request->hasFile('insurance_policy_document')) {
            $file = $request->file('insurance_policy_document');
            $fileName = time() . '_insurance_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/vehicle'), $fileName);
            $data['insurance_policy_document'] = 'uploads/vehicle/' . $fileName;
        }

        $vehicle->update($data);

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle Updated Successfully');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        Gate::authorize('delete_vehicles');
        $vehicle->delete();

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle Deleted Successfully');
    }

    public function setActiveTab(Request $request)
    {
        session(['active_tab' => $request->tab]);
        return response()->json(['success' => true]);
    }
}
