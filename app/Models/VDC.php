<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VDC extends Model
{
    protected $table = 'vdc';

    protected $fillable = [
        'DISTRICT_ID',
        'NAME'
    ];
}
