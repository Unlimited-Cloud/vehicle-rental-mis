<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class ClientSecret extends Model
{
    use HasApiTokens, HasFactory;
    protected $table = 'client_secrets';
    protected $fillable = [
        'client_id',
        'client_secret',
        'client_name',
    ];
}
