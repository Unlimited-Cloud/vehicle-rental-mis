<?php

namespace App\Events;

use App\Models\Customer;
use App\Models\EmailTemplate;
use App\Models\EmailtemplateActivities;
use App\Models\Otp;
use App\Models\OtpSetup;
use App\Models\User;
use App\Utilities\VehicleRentalUtilities;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsEvent
{
    public function __construct(
        private string $fullMobileNumber,
        private string $activity,
        private string $userType,
        private ?int $userId,
        private ?string $partnerUuid = null,
        private ?int $beneficiaryId = null
    ) {}

    public function handle(): array
    {
        // dd($this->fullMobileNumber, $this->activity, $this->userType, $this->userId, $this->partnerUuid);
        try {
            
            $mobileNumber = $this->fullMobileNumber;
            $mobileNumberExplode = explode(' ',$mobileNumber);
            if(!empty($mobileNumberExplode)){
                $customerMobileNumber = $mobileNumberExplode[1];
            }else{
                $customerMobileNumber = $mobileNumber;
            }

            // Find customer
            $customer = Customer::where('phone', $customerMobileNumber)->first();
            if (!$customer) {
                Log::warning('Customer not found', ['mobile' => $customerMobileNumber]);
                return ['status' => 'error', 'message' => 'Customer not found.'];
            }

            $partnerUuid = $this->partnerUuid ?? null;

            // Check activity
            $activityRow = EmailtemplateActivities::where('activity', $this->activity)
                ->where('sms_triggered', '1')
                ->first();

            if (!$activityRow) {
                return ['status' => 'error', 'message' => 'Activity not found or SMS not enable for this activity.'];
            }

            // Fetch SMS template
            $templateQuery = EmailTemplate::where('activity_UUID', $activityRow->Uuid)
                ->where('activity', $activityRow->activity)
                ->where('sms_template_triggered', '1');

            $template = $templateQuery->first();

            if (!$template || empty($template->success_sms_content)) {
                return ['status' => 'error', 'message' => 'SMS template missing or SMS not enable for this template.'];
            }

            // OTP setup
            $otpSetup = OtpSetup::first();
            $otpLimit = $otpSetup->otp_limit ?? 3;
            $expireTime = $otpSetup->expiry_minutes ?? 5;

            // Check OTP limit today
            $otpCountToday = Otp::where('mobile_number', $mobileNumber)
                ->whereDate('created_at', Carbon::today())
                ->count();

            if ($otpCountToday >= $otpLimit) {
                return ['status' => 'error', 'message' => 'OTP request limit reached for today.'];
            }

            // Generate OTP
            $otp = rand(100000, 999999);

            $extraVars = [];

            // Build personal details
            $personalDetails = VehicleRentalUtilities::buildPersonalDetailsSms($customer, $extraVars);

            // Replace SMS template variables
            $message = VehicleRentalUtilities::searchForSmsVar($template->success_sms_content, $personalDetails, $otp);
            $cleanMessage = strip_tags($message);

            $cleanMobileNumber = str_replace(' ', '', $mobileNumber);
            
            // Send SMS
            $response = Http::get('http://unlimitedsms.net/playsms/index.php', [
                'app' => 'ws',
                'u'   => 'lalit',
                'h'   => '9e9e5b1984cae182f35f296f82b7d5b8',
                'op'  => 'pv',
                'to'  => $cleanMobileNumber,
                'msg' => $cleanMessage,
            ]);

            // Save OTP
            Otp::create([
                'mobile_number' => $mobileNumber,
                'otp'           => $otp,
                'expires_at'    => now()->addMinutes($expireTime),
                'user_type'     => $this->userType,
                'user_id'       => $this->userId,
                'message'       => $message,
            ]);

            Log::info('SMS SENT', [
                'mobile'   => $cleanMobileNumber,
                'response' => $response->body(),
            ]);

            return ['status' => 'success', 'otp' => $otp];
        } catch (\Throwable $e) {
            Log::error('SMS ERROR', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => 'SMS sending failed.'];
        }
    }
}
