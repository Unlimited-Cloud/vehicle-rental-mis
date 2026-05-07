<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KhaltiPayment extends Model
{
    protected $table = 'khalti_payments';

    protected $fillable = [
        'booking_id',
        'merchant_transaction_id',
        'pidx',
        'txn_id',
        'amount',
        'fees',
        'total_amount',
        'user_name',
        'user_email',
        'user_mobile',
        'status',
        'khalti_init_response',
        'payment_type',
        'payment_id',
        'attendance_id',
        'crew_id'
    ];

    /**
     * Relationship: KhaltiPayment belongs to Booking
     */
    public function booking()
    {
        return $this->belongsTo(VehicleBooking::class, 'booking_id');
    }
}
