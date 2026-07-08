<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripRoute extends Model
{
    // use SoftDeletes;
    protected $fillable = [
        'trip_category_id',
        'title',
        'km',
        'car_price',
        'hiace_price',
        'coaster_price',
        'bus_price',
        'van_price',
        'status',
        'deleted_by'
    ];

    public function category()
    {
        return $this->belongsTo(TripCategory::class, 'trip_category_id');
    }

    public function vehiclePrices()
    {
        return $this->hasMany(TripRouteVehiclePrice::class);
    }
}
