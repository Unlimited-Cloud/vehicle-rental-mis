<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'name',
        'contact',
        'email',
        'address',
        'company_name'
    ];
    public function repairs()
    {
        return $this->hasMany(VehicleRepair::class);
    }
}
