<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerLocation extends Model
{

    protected $table = 'customer_location';
    protected $fillable = [
        'customer_uuid',
        'lat',
        'lng',
        'address',
        'place_name'
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];
}
