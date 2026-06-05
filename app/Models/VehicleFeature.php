<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleFeature extends Model
{
    protected $fillable = [
        'vehicle_id',

        'dash_cam',
        'dash_cam_image',

        'ebs',
        'ebs_image',

        'air_conditioning',
        'air_conditioning_image',

        'reverse_camera',
        'reverse_camera_image',

        'camera_360',
        'camera_360_image',

        'emergency_braking_system',
        'emergency_braking_system_image',

        'hillside_braking_system',
        'hillside_braking_system_image',

        'hill_descent_control',
        'hill_descent_control_image',
    ];

    protected $casts = [
        'dash_cam' => 'boolean',
        'ebs' => 'boolean',
        'air_conditioning' => 'boolean',
        'reverse_camera' => 'boolean',
        'camera_360' => 'boolean',
        'emergency_braking_system' => 'boolean',
        'hillside_braking_system' => 'boolean',
        'hill_descent_control' => 'boolean',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
