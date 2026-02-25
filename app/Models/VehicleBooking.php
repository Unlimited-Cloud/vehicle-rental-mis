<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleBooking extends Model
{
    protected $fillable = [
        'vehicle_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'from_destination',
        'to_destination',
        'no_of_people',
        'start_date',
        'end_date',
        'total_amount',
        'status',
        'notes'
    ];

    protected $dates = ['start_date', 'end_date'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
