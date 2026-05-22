<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use App\Models\EmailtemplateActivities;
use App\Models\Customer;
use App\Models\User;
use App\Models\EmailTemplate;
use App\Models\EmailLog;
use App\Models\Passcode;
use App\Mail\OwnMail;
use App\Models\VehicleBooking;
use App\Utilities\VehicleRentalUtilities;

class EmailEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $email;
    public $activity;
    public $response;
    public $userType;
    public $mailType;
    public $emailTemplates;
    public $PersonalDetails;
    public  $isPasscode;
    public $currentUserId;
    public $transactionId;

    public $recurringRemittanceId;
    public function __construct($email, $activity, $response, $userType,  $transactionId = null,  $recurringRemittanceId = null)
    {
        $this->email  = $email;
        $this->activity = $activity;
        $this->response = $response;
        $this->userType = $userType;
        $this->transactionId = $transactionId;
        $this->isPasscode = true;
        $this->currentUserId = Auth::user() ? Auth::user()->id : 0;
        $this->recurringRemittanceId = $recurringRemittanceId;

        if ($this->userType == 'customer') {
            $emailconfig = 'asiyana';
            $this->mailType = 'Customer';
            $this->PersonalDetails = Customer::where('email', $email)->first();

            if (empty($this->PersonalDetails)) {
                $this->PersonalDetails = User::where('email', $email)->first();
            }


            if ($this->activity == 'forgot_password') {
                $this->PersonalDetails = Customer::where('email', $email)->first();
            }
            //
            if ($this->activity == 'create_booking' || $this->activity == 'confirmed_booking') {
                $this->PersonalDetails = VehicleBooking::join('customers', 'vehicle_bookings.customer_id', '=', 'customers.id')
                    ->join('vehicles', 'vehicle_bookings.vehicle_id', '=', 'vehicles.id')
                    ->join('trip_routes', 'vehicle_bookings.trip_route_id', '=', 'trip_routes.id')
                    ->where('vehicle_bookings.customer_id', $this->PersonalDetails->id)
                    ->select(
                        'vehicle_bookings.*',
                        'customers.name',
                        'customers.email',
                        'customers.customer_uuid',
                        'vehicles.vehicle_name',
                        'vehicles.vehicle_type',
                        'trip_routes.title as trip_route_name'
                    )
                    ->latest('vehicle_bookings.created_at')
                    ->first();
            }


            $this->emailTemplates = EmailTemplate::where('activity',  $this->activity)->where('email_template_triggered', 1)
                ->first();
        } else {
            $emailconfig = 'asiyana';
            $this->mailType = 'User';

            $this->PersonalDetails = $userDetail = User::where('email', $email)->first();
            $userDetailId = $userDetail->id;
            if ($this->activity == 'password_reset_otp') {
                $this->PersonalDetails = User::where('email', $email)->first();
            }


            $this->emailTemplates = EmailTemplate::where('activity',  $this->activity)->where('email_template_triggered', 1)
                ->first();
        }

        try {
            Mail::mailer($emailconfig)->to($this->email)->send(new OwnMail($this->mailType, $this->emailTemplates, $this->PersonalDetails, $this->response, $this->activity));


            if (!empty($this->emailTemplates)) {
                $emailTemplateSend = $this->emailTemplates;
                $emailTemplateId = $this->emailTemplates->id;
                $emailSubject = $this->emailTemplates->email_subject;
                $personalDetailsSend = $this->PersonalDetails;
                $emailTemplateActivity = $this->activity;
                $sendPasscode = null;
                if ($emailTemplateActivity == 'passcode' || $emailTemplateActivity == 'forgot_password' || $emailTemplateActivity == 'password_reset_otp') {

                    $sendPasscode  = Passcode::where('email', $personalDetailsSend['email'])->latest()->first();
                }
                $emailBody = VehicleRentalUtilities::searchForEmailVar($emailTemplateSend->success_email_content, $personalDetailsSend, $sendPasscode);

                $emailCc = $this->emailTemplates->email_cc;
            } else {
                $emailTemplateId = $emailSubject = $emailBody = $emailCc = NULL;
            }
            $emailLogData['emailtemplate_id'] = $emailTemplateId;
            $emailLogData['email_from'] = config('mail.mailers.' . $emailconfig . '.username');
            $emailLogData['email_to'] = $this->email;
            $emailLogData['email_subject'] = $emailSubject;
            $emailLogData['email_body'] = $emailBody;
            $emailLogData['email_cc'] = $emailCc;
            $emailLogData['status'] = 'success';

            EmailLog::create($emailLogData);
        } catch (\Exception $e) {
            //throw $th;
            Log::error('Mail sending failed:' . $this->email . $e->getMessage() . 'file ' . $e->getFile() . ' line ' . $e->getLine());

            if (!empty($this->emailTemplates)) {
                $emailTemplateSend = $this->emailTemplates;
                $emailTemplateId = $this->emailTemplates->id;
                $emailSubject = $this->emailTemplates->email_subject;
                $personalDetailsSend = $this->PersonalDetails;
                $emailTemplateActivity = $this->activity;
                $sendPasscode = null;
                if ($emailTemplateActivity == 'passcode' || $emailTemplateActivity == 'password_reset_otp') {

                    $sendPasscode  = Passcode::where('email', $personalDetailsSend['email'])->latest()->first();
                }
                $emailBody = VehicleRentalUtilities::searchForEmailVar($emailTemplateSend->success_email_content, $personalDetailsSend, $sendPasscode);
                $emailCc = $this->emailTemplates->email_cc;
            } else {
                $emailTemplateId = $emailSubject = $emailBody = $emailCc = NULL;
            }
            $emailLogData['emailtemplate_id'] = $emailTemplateId;
            $emailLogData['email_from'] = config('mail.mailers.' . $emailconfig . '.username');
            $emailLogData['email_to'] = $this->email;
            $emailLogData['email_subject'] = $emailSubject;
            $emailLogData['email_body'] = $emailBody;
            $emailLogData['email_cc'] = $emailCc;
            $emailLogData['status'] = 'failed';
            $failureReason = 'Mail sending failed:' . $this->email . $e->getMessage() . 'file ' . $e->getFile() . ' line ' . $e->getLine();
            $emailLogData['failure_reason'] = $failureReason;
            EmailLog::create($emailLogData);
        }
    }

    /**.
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
