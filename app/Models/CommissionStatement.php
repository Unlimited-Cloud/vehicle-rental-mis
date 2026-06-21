<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionStatement extends Model
{
    protected $fillable = [
        'statement_number',
        'payee_type',
        'payee_code',
        'payee_id',
        'payment_id',
        'vehicle_booking_id',
        'period_start',
        'period_end',
        'booking_amount',
        'commission_rate',
        'commission_amount',
        'tds_rate',
        'tds_amount',
        'net_paid_amount',
        'payment_method',
        'bank_name',
        'bank_account_number',
        'transaction_reference',
        'payment_date',
        'pdf_path',
        'status',
        'remarks',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'payment_date' => 'date',
    ];

    public function booking()
    {
        return $this->belongsTo(VehicleBooking::class, 'vehicle_booking_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}
