<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = [
        'bank_name',
        'normalized_name',
        'swift_code',
        'bank_code',
        'configuration_id',
        'is_source_account',
        'is_payee_account',
    ];

    protected $casts = [
        'is_source_account' => 'boolean',
        'is_payee_account'  => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($bank) {
            if ($bank->bank_name) {
                $bank->normalized_name = strtoupper($bank->bank_name);
            }
        });
    }

    public function scopeSourceBanks($query)
    {
        return $query->where('is_source_account', true);
    }

    public function scopePayeeBanks($query)
    {
        return $query->where('is_payee_account', true);
    }
}
