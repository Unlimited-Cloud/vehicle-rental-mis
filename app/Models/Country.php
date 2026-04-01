<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{

    protected $fillable = [
        'name',
        'is_restricted',
        'nationality',
        'country_code',
        'country_code2',
        'country_code3',
        'currency',
        'phone_code',
        'flag_url',
        'created_at'
    ];

    // Define the relationship to customers
    public function customers()
    {
        return $this->hasMany(Customer::class, 'country', 'id');
    }
}
