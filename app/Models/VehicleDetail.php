<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleDetail extends Model
{
    protected $fillable = [
        'vehicle_id',
        'blue_book_number',
        'insurance_number',
        'insurance_expiry',
        'permit_number',
        'permit_expiry',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
