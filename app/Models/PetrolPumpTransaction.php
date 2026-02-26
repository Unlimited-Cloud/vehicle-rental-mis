<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetrolPumpTransaction extends Model
{
    protected $fillable = [
        'petrol_pump_id',
        'invoice_number',
        'transaction_date',
        'transaction_type',
        'amount',
        'paid_amount',
        'balance',
        'fuel_quantity',
        'fuel_type',
        'rate_per_liter',
        'payment_method',
        'reference_number',
        'remarks',
        'status',
        'vehicle_id',
        'odometer_reading'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'fuel_quantity' => 'decimal:2',
        'rate_per_liter' => 'decimal:2'
    ];

    /**
     * Relationships
     */
    public function petrolPump()
    {
        return $this->belongsTo(PetrolPump::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Generate invoice number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            $date = now()->format('Ymd');
            $lastTransaction = self::whereDate('created_at', today())->count();
            $transaction->invoice_number = 'PPT-' . $date . '-' . str_pad($lastTransaction + 1, 4, '0', STR_PAD_LEFT);
        });
    }

    public function getTransactionTypeBadgeAttribute()
    {
        $badges = [
            'credit' => 'badge-success',
            'debit' => 'badge-danger',
            'payment' => 'badge-success',
            'payable' => 'badge-warning'
        ];

        $labels = [
            'credit' => 'Credit (Inbound)',
            'debit' => 'Debit (Outbound)',
            'payment' => 'Payment',
            'payable' => 'Payable'
        ];

        $badgeClass = $badges[$this->transaction_type] ?? 'badge-secondary';
        $label = $labels[$this->transaction_type] ?? ucfirst($this->transaction_type);

        return '<span class="badge ' . $badgeClass . '">' . $label . '</span>';
    }

    public function getFuelTypeBadgeAttribute()
    {
        if (!$this->fuel_type) {
            return 'N/A';
        }

        $badges = [
            'petrol' => 'badge-danger',
            'diesel' => 'badge-warning',
            'cng' => 'badge-info',
            'other' => 'badge-secondary'
        ];

        $badgeClass = $badges[$this->fuel_type] ?? 'badge-secondary';

        return '<span class="badge ' . $badgeClass . '">' . ucfirst($this->fuel_type) . '</span>';
    }

    /**
     * Get status badge
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'badge-warning',
            'completed' => 'badge-success',
            'cancelled' => 'badge-danger'
        ];

        $badgeClass = $badges[$this->status] ?? 'badge-secondary';

        return '<span class="badge ' . $badgeClass . '">' . ucfirst($this->status) . '</span>';
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute()
    {
        return '₹ ' . number_format($this->amount, 2);
    }

    /**
     * Get formatted paid amount
     */
    public function getFormattedPaidAmountAttribute()
    {
        return '₹ ' . number_format($this->paid_amount, 2);
    }

    public function getFormattedBalanceAmountAttribute()
    {
        return '₹ ' . number_format($this->balance, 2);
    }

    /**
     * Get formatted balance
     */
    public function getFormattedBalanceAttribute()
    {
        return '₹ ' . number_format($this->balance, 2);
    }

    public function getPaymentMethodBadgeAttribute()
    {
        if (!$this->payment_method) {
            return 'N/A';
        }

        $badges = [
            'cash' => 'badge-success',
            'bank_transfer' => 'badge-info',
            'cheque' => 'badge-warning',
            'card' => 'badge-primary',
            'upi' => 'badge-secondary'
        ];

        $labels = [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
            'card' => 'Card',
            'upi' => 'UPI'
        ];

        $badgeClass = $badges[$this->payment_method] ?? 'badge-secondary';
        $label = $labels[$this->payment_method] ?? ucfirst($this->payment_method);

        return '<span class="badge ' . $badgeClass . '">' . $label . '</span>';
    }
}
