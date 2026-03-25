<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleAssign extends Model
{
    protected $fillable = [
        'date',
        'vehicle_id',
        'driver_id',
        'helper_id',
        'remarks',
        'status',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(CrewProfile::class, 'driver_id');
    }

    public function helper()
    {
        return $this->belongsTo(CrewProfile::class, 'helper_id');
    }
}
