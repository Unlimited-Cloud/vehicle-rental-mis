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
        'incident_report'
    ];

    public function booking()
    {
        return $this->belongsTo(VehicleBooking::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function helper()
    {
        return $this->belongsTo(User::class, 'helper_id');
    }

    public function questionnaireAnswers()
    {
        return $this->hasMany(VehicleQuestionnaireAnswer::class);
    }
}
