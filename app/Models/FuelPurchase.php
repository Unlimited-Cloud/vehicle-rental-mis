<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelPurchase extends Model
{
    protected $fillable = [
        'date_time',
        'vehicle_id',
        'driver_id',
        'petrol_pump_id',
        'liters',
        'rate',
        'amount',
        'pump_before',
        'pump_after',
        'tank_before',
        'tank_after',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(CrewProfile::class);
    }

    public function petrolPump()
    {
        return $this->belongsTo(PetrolPump::class);
    }
}
