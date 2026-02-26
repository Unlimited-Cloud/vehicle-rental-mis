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


    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
