<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'vehicle_booking_id',
        'amount',
        'payment_method',
        'direction',
        'unique_id',
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
        'proof',
        'created_user_type',
        'gateway'
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'created_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    public function vehiclepayment()
    {
        return $this->belongsTo(VehicleBooking::class);
    }

    public function vehicleBooking()
    {
        return $this->belongsTo(VehicleBooking::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function crew()
    {
        return $this->belongsTo(CrewProfile::class, 'crew_id');
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

    // Scopes
    public function scopeIncome($query)
    {
        return $query->where('direction', 'in');
    }

    public function scopeExpense($query)
    {
        return $query->where('direction', 'out');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return 'रु ' . number_format($this->amount, 2);
    }

    public function getPaymentMethodBadgeAttribute()
    {
        $badges = [
            'cash' => '<span class="badge badge-success"><i class="fas fa-money-bill"></i> Cash</span>',
            'esewa' => '<span class="badge badge-primary"><i class="fas fa-mobile-alt"></i> eSewa</span>',
            'khalti' => '<span class="badge badge-purple"><i class="fas fa-mobile-alt"></i> Khalti</span>',
            'bank' => '<span class="badge badge-info"><i class="fas fa-university"></i> Bank</span>',
        ];

        return $badges[$this->payment_method] ?? '<span class="badge badge-secondary">' . ucfirst($this->payment_method) . '</span>';
    }

    public function getDirectionBadgeAttribute()
    {
        if ($this->direction == 'in') {
            return '<span class="badge badge-success"><i class="fas fa-arrow-down"></i> Income</span>';
        }
        return '<span class="badge badge-danger"><i class="fas fa-arrow-up"></i> Expense</span>';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'completed' => '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Completed</span>',
            'pending' => '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>',
            'failed' => '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Failed</span>',
            'cancelled' => '<span class="badge badge-secondary"><i class="fas fa-ban"></i> Cancelled</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge badge-secondary">' . ucfirst($this->status) . '</span>';
    }
}
