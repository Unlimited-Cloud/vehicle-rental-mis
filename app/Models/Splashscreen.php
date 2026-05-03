<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Splashscreen extends Model
{
    protected $fillable = [
        'image',
        'header',
        'description',
        'order_by'
    ];
}
