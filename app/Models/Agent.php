<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $fillable = [
        'user_id',
        'role',
        'citizenship_doc',
        'contact_number',
        'address',
        'bank_name',
        'bank_code',
        'bank_account_name',
        'bank_account_number',
        'wallet_name',
        'wallet_number',
        'commission_rate',
        'is_verified',
        'status',
        'remarks',
        'agent_code'
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'status' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
