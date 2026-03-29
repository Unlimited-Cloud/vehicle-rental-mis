<?php

namespace App\Helpers;

use App\Models\Passcode;
use Illuminate\Support\Facades\Http;
use App\Models\PasscodeSetup;
use Illuminate\Support\Facades\Mail;
use PhpParser\Node\Stmt\TryCatch;
use App\Models\EmailtemplateActivities;
use App\Models\Customer;
use App\Events\PartnerEvent;
use App\Events\OwnEvent;


class PasscodeHelper
{
    /**
     * @author prabinunlimited
     * Create and send Passcode
     *
     * @param string $userEmail
     * @param string $passcode
     * @param string $expiresAt
     * @param string $userType
     * @param int $userId
     * @return array
     */
    public static function createAndSendPasscode($userEmail, $passcode, $currentDate, $userType, $userId, $partnerId = NULL)
    {
        
        $passcodeRecord = Passcode::where('email', $userEmail)
                        ->where('date', $currentDate)
                        ->where('user_type',$userType)
                        ->first();
            
        if ($passcodeRecord) {
            
            // If the request count is 3 or more, deny the request
            $passcodeLimit = 3; // Default limit set to 3
            $passcodeSetup = PasscodeSetup::first(); // Retrieve the first PasscodeSetup record

            if ($passcodeSetup) {
                $passcodeLimit = $passcodeSetup->limit_numbers; // If a record exists, set the limit to 'limit_numbers' from the record
            }

            if ($passcodeRecord->request_count >= $passcodeLimit) {
                return array('status'=> 'error','message' => 'Passcode request limit reached for today.'); // Return an error response if the limit is reached
            }


            // Increment the request count
            $request_count = $passcodeRecord->request_count + 1;
            $passcodeRecord = new Passcode();
            $passcodeRecord->email = $userEmail;
            $passcodeRecord->date = $currentDate;
            $passcodeRecord->user_type = $userType;
            $passcodeRecord->user_id = $userId != '0' ? $userId : NULL;
            $passcodeRecord->partner_id = $partnerId != '0' ? $partnerId : NULL;
            $passcodeRecord->request_count = $request_count;
        } else {
            // Create a new record if this is the first request of the day
            $passcodeRecord = new Passcode();
            $passcodeRecord->email = $userEmail;
            $passcodeRecord->date = $currentDate;
            $passcodeRecord->user_type = $userType;
            $passcodeRecord->user_id = $userId != '0' ? $userId : NULL;
            $passcodeRecord->partner_id = $partnerId != '0' ? $partnerId : NULL;
            $passcodeRecord->request_count = 1;
        }
        try {
            // Send the passcode via email
            $email = $userEmail;
            $emailTemplates = NULL;
           
            
            // 
            // if(!empty($customerData->partnerUuid)){

            //     dd('partner uuid');
            //     // $emailTemplates = EmailtemplateActivities::where('activity', 'passcode')
            //     // ->where('partner_Uuid', $customerData->partnerUuid)
            //     // ->with(['emailTemplate', 'Partner'])
            //     // ->first();
            //     event(new PartnerEvent());
            // }else{
            //     // $emailTemplates = EmailtemplateActivities::where('activity', 'Bulk Onboarding')
            //     // ->where('partner_Uuid', NULL)
            //     // ->with(['emailTemplate', 'Partner'])
            //     // ->first();
            //     Mail::to($email)->send(new \App\Mail\PasscodeMail($passcode));
            // }
           
            // Mail::to($email)->send(new \App\Mail\PasscodeMail($passcode));
            
            $passcodeRecord->passcode = $passcode;
            $passcodeRecord->save();
            return array('status' => 'success', 'message' => 'Passcode successfully sent');
        } catch (\Exception $e) {
            
            dd($e->getMessage());
            // Return error response if email sending fails
            return array('status' => 'error', 'message' => 'Error while sending passcode');
        }
    }
}