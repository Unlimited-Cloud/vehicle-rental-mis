<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status'
    ];

    public function routes()
    {
        return $this->hasMany(TripRoute::class);
    }
}
