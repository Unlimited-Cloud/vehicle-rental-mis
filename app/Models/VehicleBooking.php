<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleBooking extends Model
{
    protected $fillable = [
        'vehicle_id',
        'contact_person',
        'contact_email',
        'contact_number',
        'from_destination',
        'to_destination',
        'no_of_people',
        'start_date',
        'end_date',
        'total_amount',
        'status',
        'notes',
        'customer_id',
        'driver_id',
        'helper_id',
        'start_km',
        'end_km',
        'approx_fuel_litre',
        'start_time',
        'end_time',
        'no_of_hours',
        'rate_per_day',
        'sub_total',
        'tax_amount_type',
        'tax',
        'discount_amount_type',
        'discount',
        'payment_status',
        'signage_information',
        'trip_category_id',
        'trip_route_id',
        'vat',
        'passenger',
        'file_no',
        'agent_code',
        'remaining_balance',
        'deleted_at',
        'deleted_by'
    ];

    protected $dates = ['start_date', 'end_date'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(CrewProfile::class, 'driver_id');
    }

    public function helper()
    {
        return $this->belongsTo(CrewProfile::class, 'helper_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function proformaInvoices()
    {
        return $this->hasMany(ProformaInvoice::class, 'vehicle_booking_id');
    }
    public function tripRoute()
    {
        return $this->belongsTo(TripRoute::class, 'trip_route_id');
    }
    public function vehicleMoment()
    {
        return $this->hasOne(VehicleMoment::class, 'booking_id');
    }
    public function receipts()
    {
        return $this->hasMany(VehicleReceipt::class, 'file_no', 'file_no');
    }

    public function khaltiPayments()
    {
        return $this->hasMany(KhaltiPayment::class, 'booking_id');
    }

    public function latestKhaltiPayment()
    {
        return $this->hasOne(KhaltiPayment::class, 'booking_id')->latest();
    }

    public function esewaPayments()
    {
        return $this->hasMany(EsewaPayment::class, 'booking_id');
    }
}
