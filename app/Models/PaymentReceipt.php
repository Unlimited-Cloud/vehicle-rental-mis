<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentReceipt extends Model
{
    protected $fillable = [
        'receipt_number',
        'customer_id',
        'total_invoice_amount',
        'tds_deduction',
        'tds_rate',
        'net_paid_amount',
        'received_amount',
        'difference_amount',
        'payment_method',
        'bank_name',
        'bank_account_number',
        'cheque_number',
        'cheque_date',
        'transaction_id',
        'payment_date',
        'notes',
        'tds_applied',
        'pdf_path'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'cheque_date' => 'date',
        'tds_applied' => 'boolean'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(PaymentReceiptItem::class);
    }

    public function vehicleReceipts()
    {
        return $this->belongsToMany(VehicleReceipt::class, 'payment_receipt_items', 'payment_receipt_id', 'vehicle_receipt_id')
            ->withPivot('invoice_amount', 'paid_amount');
    }
}
