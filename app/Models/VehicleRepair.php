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
        'driver_id',
        'vendor_id',
        'bill',
        'claim_insurance'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function driver()
    {
        return $this->belongsTo(CrewProfile::class, 'driver_id');
    }
}
