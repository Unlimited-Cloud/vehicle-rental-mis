<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EsewaPayment extends Model
{
    protected $fillable = [
        'transaction_uuid',
        'amount',
        'status',
        'booking_id',
        'esewa_response'
    ];

    public function booking()
    {
        return $this->belongsTo(VehicleBooking::class, 'booking_id');
    }
}
