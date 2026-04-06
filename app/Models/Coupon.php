<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'coupon_number',
        'petrol_pump_id',
        'amount',
        'booking_id',
        'used',
        'used_at'
    ];

    protected $casts = [
        'used' => 'boolean',
        'used_at' => 'datetime',
    ];

    // Auto-generate coupon number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($coupon) {

            // Get last coupon ID
            $lastCoupon = Coupon::orderBy('id', 'desc')->first();

            $nextNumber = $lastCoupon ? $lastCoupon->id + 1 : 1;

            // Format with leading zeros (5 digits)
            $coupon->coupon_number = 'ASH' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        });
    }

    // Relationships
    public function petrolPump()
    {
        return $this->belongsTo(PetrolPump::class);
    }

    public function booking()
    {
        return $this->belongsTo(VehicleBooking::class);
    }
}
