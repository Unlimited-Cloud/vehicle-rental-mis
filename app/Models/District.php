<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'district';

    protected $fillable = [
        'name',
        'name_np',
        'province_id',
        'district_index'
    ];
}
