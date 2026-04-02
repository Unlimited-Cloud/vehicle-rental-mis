<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstimateBill extends Model
{
    protected $fillable = [
        'vehicle_id',
        'estimate_number',
        'rate_per_day',
        'sub_total',
        'tax',
        'discount',
        'total_amount',
        'version',
        'pdf_path',
        'customer_id',
        'file_no'
    ];

    public function booking()
    {
        return $this->belongsTo(VehicleBooking::class, 'vehicle_booking_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
