<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Model
{
    use  HasApiTokens;
    protected $fillable = [
        'customer_type',
        'customer_uuid',
        'first_name',
        'middle_name',
        'last_name',
        'name',
        'email',
        'mobile_number_country_code',
        'phone',
        'address',
        'city',
        'state',
        'pan_number',
        'license_number',
        'license_expiry',
        'status',
        'password',
        'author_type',
        'author_id'
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

    public function bookings()
    {
        return $this->hasMany(VehicleBooking::class, 'customer_id');
    }
}
