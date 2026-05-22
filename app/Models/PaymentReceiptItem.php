<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentReceiptItem extends Model
{
    protected $fillable = [
        'payment_receipt_id',
        'vehicle_receipt_id',
        'invoice_number',
        'invoice_amount',
        'paid_amount'
    ];

    public function paymentReceipt()
    {
        return $this->belongsTo(PaymentReceipt::class);
    }

    public function vehicleReceipt()
    {
        return $this->belongsTo(VehicleReceipt::class);
    }
}
