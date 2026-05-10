<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'vehicle_booking_id',
        'amount',
        'payment_method',
        'transaction_reference',
        'payment_date',
        'notes',
        'created_by',
        'deleted_by',
        'deleted_at',
        'status',
        'crew_id',
        'attendance_id',
        'payment_type',
        'proof'
    ];

    public function vehiclepayment()
    {
        return $this->belongsTo(VehicleBooking::class);
    }
}
