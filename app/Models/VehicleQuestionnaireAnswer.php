<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleQuestionnaireAnswer extends Model
{
    protected $fillable = [
        'vehicle_moment_id',
        'questionnaire_id',
        'answer'
    ];

    public function vehicleMoment()
    {
        return $this->belongsTo(VehicleMoment::class);
    }

    public function questionnaire()
    {
        return $this->belongsTo(Questionnaire::class);
    }
}
