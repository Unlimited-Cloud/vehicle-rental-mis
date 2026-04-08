<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripCategory extends Model
{
    // use SoftDeletes;

    protected $table = 'trip_categories';
    protected $fillable = [
        'name',
        'description',
        'status',
        'deleted_by'
    ];

    public function routes()
    {
        return $this->hasMany(TripRoute::class);
    }
}
