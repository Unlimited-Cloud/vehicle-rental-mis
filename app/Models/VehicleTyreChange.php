<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleTyreChange extends Model
{
    protected $fillable = [
        'vehicle_id',
        'change_date',
        'tyre_position',
        'tyre_manufacturer',
        'tyre_specifications',
        'amount',
        'invoice_upload',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
