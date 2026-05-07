<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Passcode extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'passcode',
        'requested_at',
        'request_count',
        'attempt_count',
        'locked_until',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
