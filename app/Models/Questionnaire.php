<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Questionnaire extends Model
{
    protected $fillable = [
        'question',
        'type',
        'is_required',
        'sort_order',
        'is_active'
    ];

    public function answers()
    {
        return $this->hasMany(VehicleQuestionnaireAnswer::class);
    }
}
