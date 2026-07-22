<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleCatalog extends Model
{
    protected $fillable = [
        // Required
        'brand',

        // Vehicle specifications
        'vehicle_type',
        'model',
        'seater',
        'year',
        'fuel_type',
        'transmission',
        'image',
        'mileage',
        'horsepower',
        'car_color',
        'description',
        'car_images',

        // Status
        'is_helper_needed',

        // Registration Details
        'registration_number',
        'registered_at',
        'number_plate_color',
        'registration_expiry',
        'bill_book_number',
        'bill_book_image',

        // Insurance Details
        'insurance_policy_no',
        'insurance_company',
        'insurance_type',
        'insurance_till',
        'insurance_cost_per_annum',
        'insurance_policy_document',
        'passenger_insured',
        'passenger_insured_amount',
        'passenger_insurance_company',

        // Additional catalog fields
        'engine_capacity',
        'fuel_tank_capacity',
        'top_speed',
        'acceleration',
        'drivetrain',
        'emission_standard',
        'features',
        'safety_features',

        // Safety features
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
        'car_images' => 'array',
        'images' => 'array',
        'features' => 'array',
        'safety_features' => 'array',
        'registered_at' => 'datetime',
        'registration_expiry' => 'datetime',
        'insurance_till' => 'datetime',
        'insurance_cost_per_annum' => 'decimal:2',
        'is_helper_needed' => 'boolean',
        'dash_cam' => 'boolean',
        'ebs' => 'boolean',
        'air_conditioning' => 'boolean',
        'reverse_camera' => 'boolean',
        'camera_360' => 'boolean',
        'emergency_braking_system' => 'boolean',
        'hillside_braking_system' => 'boolean',
        'hill_descent_control' => 'boolean',
    ];

    protected $appends = ['vehicle_image_url', 'vehicle_description_image_url'];

    protected $hidden = ['image', 'car_images', 'insurance_policy_document'];
    // Accessor for image URL
    public function getVehicleImageUrlAttribute()
    {
        return $this->image ? asset($this->image) : null;
    }


    public function getVehicleDescriptionImageUrlAttribute()
    {
        if (!empty($this->car_images) && is_array($this->car_images)) {

            return array_map(function ($image) {

                if (!is_string($image) || empty($image)) {
                    return null;
                }

                return asset($image);
            }, $this->car_images);
        }

        return null;
    }


    // Scopes for common queries
    public function scopeByBrand($query, $brand)
    {
        return $query->where('brand', 'like', '%' . $brand . '%');
    }

    public function scopeByVehicleType($query, $type)
    {
        return $query->where('vehicle_type', $type);
    }

    public function scopeByFuelType($query, $fuelType)
    {
        return $query->where('fuel_type', $fuelType);
    }

    public function scopeByTransmission($query, $transmission)
    {
        return $query->where('transmission', $transmission);
    }

    // Relationship with vehicles (if you want to link catalog to actual vehicles)

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'brand', 'brand')
            ->whereColumn('vehicles.seater', 'vehicle_catalogs.seater')
            ->whereColumn('vehicles.fuel_type', 'vehicle_catalogs.fuel_type');
    }
}
