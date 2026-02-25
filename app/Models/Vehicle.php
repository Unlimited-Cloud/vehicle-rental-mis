<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'vehicle_name',
        'brand',
        'model',
        'year',
        'rent_price_per_day',
        'fuel_type',
        'transmission',
        'image',
        'status'
    ];

    public function vehicleDetail()
    {
        return $this->hasOne(VehicleDetail::class);
    }
}
