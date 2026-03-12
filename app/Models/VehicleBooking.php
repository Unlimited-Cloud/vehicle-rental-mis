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
        'notes',
        'customer_id',
        'driver_id',
        'helper_id',
        'start_date',
        'end_date',
        'start_km',
        'end_km',
        'approx_fuel_litre',
        'start_time',
        'end_time',
        'no_of_hours',
        'rate_per_day',
        'sub_total',
        'tax_amount_type',
        'tax',
        'discount_amount_type',
        'discount',
        'payment_status',
        'signage_information'
    ];

    protected $dates = ['start_date', 'end_date'];

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

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
