<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seater extends Model
{
    protected $fillable = [
        'name',
        'logo'
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'seater');
    }
}
