<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\EmailtemplateActivities;

class EmailTemplate extends Model
{
    protected $fillable = [
        'delay_min',
        'title',
        'delay_hour',
        'delay_days',
        'email_subject',
        'success_email_content',
        'success_sms_content',
        'error_email_content',
        'error_sms_content',
        'activity',
        'template_for',
        'partner_Uuid',
        'template_UUID',
        'activity_UUID',
        'success_customer_notification_content',
        'success_admin_notification_content',
        'email_cc',
        'email_template_triggered',
        'sms_template_triggered',
        'notification_template_triggered'
    ];

    public function emailActivities()
    {
        return $this->belongsTo(EmailtemplateActivities::class, 'activity_UUID', 'template_UUID');
    }
}
