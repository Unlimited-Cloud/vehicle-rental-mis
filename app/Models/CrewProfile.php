<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrewProfile extends Model
{
    protected $fillable = [
        'user_id',
        'role',
        'license_number',
        'license_expiry',
        'citizenship_doc',
        'contact_number',
        'experience',
        'basic_salary',
        'age'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function fuelPurchases()
    {
        return $this->hasMany(FuelPurchase::class);
    }
    public function repairs()
    {
        return $this->hasMany(VehicleRepair::class);
    }

    public function bankDetails()
    {
        return $this->hasMany(CrewBankDetail::class, 'crew_id');
    }
}
