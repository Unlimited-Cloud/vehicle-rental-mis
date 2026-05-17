<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'vehicle_name',
        'vehicle_type',
        'brand',
        'model',
        'seater',
        'year',
        'rent_price_per_day',
        'fuel_type',
        'transmission',
        'image',
        'mileage',
        'horsepower',
        'car_color',
        'description',
        'car_images',

        'status',
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
    ];

    protected $appends = ['vehicle_image_url', 'vehicle_description_image_url', 'vehicle_insurance_url'];
    protected $casts = [
        'images' => 'array',
        'car_images' => 'array',
    ];

    protected $hidden = ['image', 'car_images', 'insurance_policy_document'];

    public function vehicleDetail()
    {
        return $this->hasOne(VehicleDetail::class);
    }

    public function fuelPurchases()
    {
        return $this->hasMany(FuelPurchase::class);
    }
    public function permits()
    {
        return $this->hasMany(VehiclePermit::class);
    }

    public function services()
    {
        return $this->hasMany(VehicleService::class);
    }

    public function repairs()
    {
        return $this->hasMany(VehicleRepair::class);
    }

    public function tyreChanges()
    {
        return $this->hasMany(VehicleTyreChange::class);
    }

    public function getVehicleImageUrlAttribute()
    {
        return $this->image ? asset($this->image) : null;
    }

    public function getVehicleInsuranceUrlAttribute()
    {
        return $this->insurance_policy_document ? asset($this->insurance_policy_document) : null;
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
}
