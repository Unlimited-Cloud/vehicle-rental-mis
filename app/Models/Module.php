<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'route',
        'permission',
        'parent_id',
        'order_by'
    ];
}
