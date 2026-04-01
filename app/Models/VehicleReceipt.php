<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleReceipt extends Model
{
    protected $fillable = [
        'vehicle_booking_id',
        'vehicle_moment_id',
        'vehicle_id',
        'customer_id',
        'receipt_number',
        'invoice_type',
        'start_datetime',
        'end_datetime',
        'hours',
        'days',
        'rate_per_day',
        'sub_total',
        'discount',
        'tax',
        'total_amount',
        'pdf_path',
        'file_no'
    ];

    // In VehicleReceipt.php model
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function booking()
    {
        return $this->belongsTo(VehicleBooking::class, 'vehicle_booking_id');
    }

    public function moment()
    {
        return $this->belongsTo(VehicleMoment::class, 'vehicle_moment_id');
    }
}
