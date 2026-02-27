<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleService extends Model
{
    protected $fillable = [
        'vehicle_id',
        'service_date',
        'service_done_at',
        'service_details',
        'service_amount',
        'service_bill_copy',
        'next_service_km',
        'next_service_date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
