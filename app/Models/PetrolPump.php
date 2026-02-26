<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetrolPump extends Model
{
    protected $fillable = [
        'name',
        'owner_name',
        'phone',
        'alternate_phone',
        'address',
        'pan_number',
        'opening_balance',
        'current_balance',
        'balance_type',
        'credit_limit',
        'status',
        'remarks'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2'
    ];

    /**
     * Relationships
     */
    public function transactions()
    {
        return $this->hasMany(PetrolPumpTransaction::class);
    }

    public function updateBalance($amount, $type)
    {
        switch ($type) {
            case 'credit':
            case 'payable':
                // We owe money to pump (payable increases)
                $this->current_balance -= $amount;
                $this->balance_type = 'payable';
                break;
            case 'debit':
                // Pump owes us money (receivable increases)
                $this->current_balance += $amount;
                $this->balance_type = 'receivable';
                break;
            case 'payment':
                // We pay the pump (payable decreases)
                $this->current_balance += $amount;
                // Auto-determine balance type
                if ($this->current_balance > 0) {
                    $this->balance_type = 'receivable';
                } elseif ($this->current_balance < 0) {
                    $this->balance_type = 'payable';
                }
                break;
        }

        $this->save();
    }

    /**
     * Revert balance update
     */
    public function revertBalanceUpdate($amount, $type)
    {
        switch ($type) {
            case 'credit':
            case 'payable':
                $this->current_balance += $amount;
                break;
            case 'debit':
                $this->current_balance -= $amount;
                break;
            case 'payment':
                $this->current_balance -= $amount;
                break;
        }

        // Re-determine balance type
        if ($this->current_balance > 0) {
            $this->balance_type = 'receivable';
        } elseif ($this->current_balance < 0) {
            $this->balance_type = 'payable';
        }

        $this->save();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
