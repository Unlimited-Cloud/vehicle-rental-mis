<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BasicTable extends Model
{
    protected $table = 'basic_tables';

    protected $fillable = [
        'logo',
        'login_logo',
        'company_name',
        'footer_text',
        'terms_and_conditions',
        'privacy_policy',
        'about_us',
        'contact_us',
    ];
}
