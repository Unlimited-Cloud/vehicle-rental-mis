<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function prices()
    {
        return $this->hasMany(TripRouteVehicleTypePrice::class);
    }
}
