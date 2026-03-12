<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProformaInvoice extends Model
{
    protected $fillable = [
        'vehicle_booking_id',
        'vehicle_id',
        'invoice_number',
        'from_date',
        'to_date',
        'days',
        'rate_per_day',
        'sub_total',
        'tax',
        'discount',
        'total_amount',
        'version',
        'pdf_path'
    ];

    public function booking()
    {
        return $this->belongsTo(VehicleBooking::class, 'vehicle_booking_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
