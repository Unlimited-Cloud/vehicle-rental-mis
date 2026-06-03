<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\VehicleFeature;

class VehicleSecurityFeatureController extends Controller
{
    public function index()
    {
        $features = VehicleFeature::with('vehicle')->latest()->get();

        return view('layouts.admin.vehicle_security_features.index', compact('features'));
    }

    public function create()
    {
        $vehicles = Vehicle::all();

        return view('layouts.admin.vehicle_security_features.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',

            'dash_cam_image' => 'nullable|image',
            'ebs_image' => 'nullable|image',
            'air_conditioning_image' => 'nullable|image',
            'reverse_camera_image' => 'nullable|image',
            'camera_360_image' => 'nullable|image',
            'emergency_braking_system_image' => 'nullable|image',
            'hillside_braking_system_image' => 'nullable|image',
            'hill_descent_control_image' => 'nullable|image',
        ]);

        $data = $request->except([
            'dash_cam_image',
            'ebs_image',
            'air_conditioning_image',
            'reverse_camera_image',
            'camera_360_image',
            'emergency_braking_system_image',
            'hillside_braking_system_image',
            'hill_descent_control_image',
        ]);

        $uploadPath = public_path('uploads/vehicle-security-features');

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $imageFields = [
            'dash_cam_image',
            'ebs_image',
            'air_conditioning_image',
            'reverse_camera_image',
            'camera_360_image',
            'emergency_braking_system_image',
            'hillside_braking_system_image',
            'hill_descent_control_image',
        ];

        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadPath, $filename);

                $data[$field] = $filename;
            }
        }

        VehicleFeature::create($data);

        return redirect()
            ->route('admin.vehicle-security-features.index')
            ->with('success', 'Vehicle security feature created successfully');
    }

    public function show($id)
    {
        $feature = VehicleFeature::with('vehicle')->findOrFail($id);

        return view('layouts.admin.vehicle_security_features.show', compact('feature'));
    }

    public function edit($id)
    {
        $feature = VehicleFeature::findOrFail($id);
        $vehicles = Vehicle::all();

        return view('layouts.admin.vehicle_security_features.create', compact('feature', 'vehicles'));
    }

    public function update(Request $request, $id)
    {
        $feature = VehicleFeature::findOrFail($id);

        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',

            'dash_cam_image' => 'nullable|image',
            'ebs_image' => 'nullable|image',
            'air_conditioning_image' => 'nullable|image',
            'reverse_camera_image' => 'nullable|image',
            'camera_360_image' => 'nullable|image',
            'emergency_braking_system_image' => 'nullable|image',
            'hillside_braking_system_image' => 'nullable|image',
            'hill_descent_control_image' => 'nullable|image',
        ]);

        $data = $request->except([
            'dash_cam_image',
            'ebs_image',
            'air_conditioning_image',
            'reverse_camera_image',
            'camera_360_image',
            'emergency_braking_system_image',
            'hillside_braking_system_image',
            'hill_descent_control_image',
        ]);

        $uploadPath = public_path('uploads/vehicle-security-features');

        $imageFields = [
            'dash_cam_image',
            'ebs_image',
            'air_conditioning_image',
            'reverse_camera_image',
            'camera_360_image',
            'emergency_braking_system_image',
            'hillside_braking_system_image',
            'hill_descent_control_image',
        ];

        foreach ($imageFields as $field) {

            if ($request->hasFile($field)) {

                if (
                    $feature->$field &&
                    file_exists($uploadPath . '/' . $feature->$field)
                ) {
                    unlink($uploadPath . '/' . $feature->$field);
                }

                $file = $request->file($field);
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadPath, $filename);

                $data[$field] = $filename;
            }
        }

        $feature->update($data);

        return redirect()
            ->route('admin.vehicle-security-features.index')
            ->with('success', 'Vehicle security feature updated successfully');
    }

    public function destroy($id)
    {
        $feature = VehicleFeature::findOrFail($id);

        $uploadPath = public_path('uploads/vehicle-security-features');

        $imageFields = [
            'dash_cam_image',
            'ebs_image',
            'air_conditioning_image',
            'reverse_camera_image',
            'camera_360_image',
            'emergency_braking_system_image',
            'hillside_braking_system_image',
            'hill_descent_control_image',
        ];

        foreach ($imageFields as $field) {
            if (
                $feature->$field &&
                file_exists($uploadPath . '/' . $feature->$field)
            ) {
                unlink($uploadPath . '/' . $feature->$field);
            }
        }

        $feature->delete();

        return redirect()
            ->route('admin.vehicle-security-features.index')
            ->with('success', 'Vehicle security feature deleted successfully');
    }
}
