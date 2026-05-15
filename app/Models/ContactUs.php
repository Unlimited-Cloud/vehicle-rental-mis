<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    protected $table = 'contact_us';

    protected $fillable = [
        'full_name',
        'email',
        'mobile_number',
        'facebook_url',
        'instagram_url',
        'linkedin_url',
        'tiktok_url',
        'whatsapp_number',
        'twitter_url',
        'youtube_url',
        'subject',
        'message',
        'status'
    ];
}
