<?php

namespace App\Models;

use App\Models\EmailTemplate;
use Illuminate\Database\Eloquent\Model;
use App\Models\Partners;

class EmailtemplateActivities extends Model
{
    protected $fillable = [
        'activity_for',
        'partner_Uuid',
        'activity',
        'added_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
        'Uuid',
        'email_triggered',
        'sms_triggered',
        'notification_triggered'
    ];

    public function emailTemplate()
    {
        return $this->hasOne(EmailTemplate::class, 'activity_UUID', 'Uuid');
    }
}
