<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = 'province';

    protected $fillable = [
        'pnumber',
        'pname',
        'pname_np',
        'headquarter',
        'map_index',
        'status',
        'inserted_on'
    ];
}
