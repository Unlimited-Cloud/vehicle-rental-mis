<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripRoute extends Model
{
    protected $fillable = [
        'trip_category_id',
        'title',
        'km',
        'car_price',
        'hiace_price',
        'coaster_price',
        'bus_price',
        'van_price',
        'status'
    ];

    public function category()
    {
        return $this->belongsTo(TripCategory::class, 'trip_category_id');
    }
}
