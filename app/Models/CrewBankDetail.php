<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrewBankDetail extends Model
{
    protected $fillable = [
        'crew_id',
        'bank_name',
        'bank_code',
        'account_holder_name',
        'account_number',
        'is_active',
        'is_verified'
    ];

    public function crew()
    {
        return $this->belongsTo(CrewProfile::class);
    }
}
