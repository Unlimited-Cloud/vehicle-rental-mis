<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiclePermit extends Model
{
    protected $fillable = [
        'vehicle_id',
        'permit_from_organization',
        'permit_expiry_date',
        'permit_document',
    ];
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
