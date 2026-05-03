<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleAssignment extends Model
{
    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'helper_id',
        'start_date',
        'end_date',
        'shift',
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
