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

    protected $appends = ['vehicle_image_url'];

    protected $hidden = ['image'];

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
}
