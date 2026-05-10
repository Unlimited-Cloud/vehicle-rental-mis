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
        'esewa_response',
        'attendance_id',
        'crew_id',
        'payment_type',
        'payment_id'
    ];

    public function booking()
    {
        return $this->belongsTo(VehicleBooking::class, 'booking_id');
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

    public function crew()
    {
        return $this->belongsTo(CrewProfile::class, 'crew_id');
    }
}
