<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Passenger extends Model
{
    protected $fillable = [
        'contact_person',
        'contact_address',
        'contact_email',
        'contact_number',
        'customer_id',
        'booking_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function booking()
    {
        return $this->belongsTo(VehicleBooking::class);
    }
}
