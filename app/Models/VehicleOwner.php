<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class VehicleOwner extends Model
{
    use  HasApiTokens;
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'pan_number',
        'license_number',
        'license_expiry',
        'status',
        'password',
        'bank_name',
        'bank_code',
        'bank_account_name',
        'bank_account_number',
        'wallet_name',
        'wallet_number',
        'commission_rate',
        'user_id'
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function getFullNameAttribute()
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name
        ]);

        return !empty($parts) ? implode(' ', $parts) : $this->name;
    }

    public function getStatusBadgeAttribute()
    {
        return $this->status == 'active'
            ? '<span class="badge badge-success">Active</span>'
            : '<span class="badge badge-danger">Inactive</span>';
    }


    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'vehicle_owner_id');
    }
}
