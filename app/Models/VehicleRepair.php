<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleRepair extends Model
{
    protected $fillable = [
        'vehicle_id',
        'repair_date',
        'repair_details',
        'repair_vendor',
        'repair_amount',
        'repair_valid_till',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
