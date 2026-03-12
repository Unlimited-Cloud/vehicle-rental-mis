<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'emailtemplate_id',
        'email_from',
        'email_to',
        'email_subject',
        'email_body',
        'email_cc',
        'status',
        'failure_reason'
    ];

    public function emailTemplate()
    {
        return $this->belongsTo(EmailTemplate::class, 'emailtemplate_id');
    }
}
