<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;
    protected $fillable = ['mobile_number', 'otp', 'expires_at','verified_at', 'user_type', 'user_id', 'message'];
    protected $dates = ['expires_at'];
}
