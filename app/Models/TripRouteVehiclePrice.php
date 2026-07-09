<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripRouteVehiclePrice extends Model
{
    protected $table = 'trip_routes_vehicle_price';

    protected $fillable = [
        'trip_route_id',
        'vehicle_id',
        'price',
        'per_km',
        'per_hour',
        'overnight',
    ];

    public function tripRoute()
    {
        return $this->belongsTo(TripRoute::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
