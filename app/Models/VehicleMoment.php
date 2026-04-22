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
    ];
    protected $appends = ['start_image_url', 'end_image_url', 'incident_image'];


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
        return $this->belongsTo(Vehicle::class);
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
}
