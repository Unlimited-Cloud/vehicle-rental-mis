<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasscodeSetup extends Model
{
    protected $fillable = [
        'otp_valid_minutes',
        'max_requests',
        'max_attempts',
        'window_minutes',
    ];
}
