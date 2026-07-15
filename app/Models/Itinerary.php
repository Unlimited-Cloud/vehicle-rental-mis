<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Itinerary extends Model
{
    protected $fillable = [
        'booking_id',
        'file_no',
        'day_number',
        'itinerary_date',
        'from_destination',
        'to_destination',
        'est_km',
        'est_hours',
        'is_overnight',
        'per_km_rate',
        'per_hour_rate',
        'overnight_charge',
        'est_price',
        'notes',
    ];

    protected $casts = [
        'is_overnight' => 'boolean',
        'itinerary_date' => 'date',
        'est_km' => 'decimal:2',
        'est_hours' => 'decimal:2',
        'per_km_rate' => 'decimal:2',
        'per_hour_rate' => 'decimal:2',
        'overnight_charge' => 'decimal:2',
        'est_price' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(VehicleBooking::class, 'booking_id');
    }
}
