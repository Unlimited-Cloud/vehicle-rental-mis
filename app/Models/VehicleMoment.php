<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleMoment extends Model
{
    protected $fillable = [
        'booking_id',
        'driver_id',
        'helper_id',
        'vehicle_no',
        'signage_information',
        'start_datetime',
        'start_km',
        'start_image',
        'start_comments',
        'end_datetime',
        'end_km',
        'end_image',
        'end_comments',
        'has_incident',
        'incident_report',
        'incident_image',
        'trip_category_id',
        'trip_route_id',


        // Depot Departure
        'depot_departure_datetime',
        'depot_departure_km',
        'depot_departure_image',
        'depot_departure_comments',

        // Pickup Arrival
        'pickup_arrival_datetime',
        'pickup_arrival_km',
        'pickup_arrival_image',
        'pickup_arrival_comments',

        // Drop Off
        'dropoff_datetime',
        'dropoff_km',
        'dropoff_image',
        'dropoff_comments',

        // Estimated return values
        'estimated_return_to_depot_km',
        'estimated_return_to_depot_minutes',

        'estimated_return_to_pickup_km',
        'estimated_return_to_pickup_minutes',
    ];
    protected $appends = [
        'start_image_url',
        'end_image_url',
        'incident_image_url',
        'depot_departure_image_url',
        'pickup_arrival_image_url',
        'dropoff_image_url',
        'incident_image_url'
    ];


    public function booking()
    {
        return $this->belongsTo(VehicleBooking::class,);
    }

    public function driver()
    {
        return $this->belongsTo(CrewProfile::class, 'driver_id');
    }

    public function helper()
    {
        return $this->belongsTo(CrewProfile::class, 'helper_id');
    }

    public function questionnaireAnswers()
    {
        return $this->hasMany(VehicleQuestionnaireAnswer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_no', 'id');
    }

    public function tripRoute()
    {
        return $this->belongsTo(TripRoute::class, 'trip_route_id');
    }

    public function receipt()
    {
        return $this->hasOne(VehicleReceipt::class);
    }

    public function getStartImageUrlAttribute()
    {
        return $this->start_image ? asset($this->start_image) : null;
    }

    public function getEndImageUrlAttribute()
    {
        return $this->end_image ? asset($this->end_image) : null;
    }

    public function getIncidentImageUrlAttribute()
    {
        return $this->incident_image ? asset($this->incident_image) : null;
    }

    public function getDepotDepartureImageUrlAttribute()
    {
        return $this->depot_departure_image
            ? asset($this->depot_departure_image)
            : null;
    }

    public function getPickupArrivalImageUrlAttribute()
    {
        return $this->pickup_arrival_image
            ? asset($this->pickup_arrival_image)
            : null;
    }

    public function getDropoffImageUrlAttribute()
    {
        return $this->dropoff_image
            ? asset($this->dropoff_image)
            : null;
    }
}
