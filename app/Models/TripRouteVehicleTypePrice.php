<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripRouteVehicleTypePrice extends Model
{
    protected $fillable = [
        'vehicle_type',
        'seater',
        'brand',
        'per_km',
        'per_hour',
        'overnight_price',
    ];

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }
}
