<?php

namespace App\Services;

use App\Repositories\Interfaces\MasterRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Models\Permission;
use App\Models\Module;
use App\Models\Customer;
use App\Models\Otp;
use App\Models\OtpSetup;
use App\Models\PasscodeSetup;
use App\Models\Passcode;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Events\SmsEvent;
use App\Events\EmailEvent;
use Illuminate\Support\Facades\Log;

class AuthService
{
    /**
     * Create a new class instance.
     */
    protected $masterRepository;
    protected $userRepository;
    protected $customerRepository;

    public function __construct(
        MasterRepositoryInterface $masterRepository,
        UserRepositoryInterface $userRepository,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->masterRepository = $masterRepository;
        $this->userRepository = $userRepository;
        $this->customerRepository = $customerRepository;
    }

    public function getOtpPasscodeAppLogin($request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            $username = $request->username;

            $emailLogin = 0;
            $mobileLogin = 0;

            $customer = $this->customerRepository->getCustomerByEmail($username);

            if ($customer) {
                $emailLogin = 1;
            } else {
                $customer = $this->customerRepository->getCustomerByMobileNumber($username);

                if ($customer) {
                    $mobileLogin = 1;
                }
            }

            if (!$customer) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid Credentials!',
                    'data' => '',
                    'statusCode' => 422
                ];
            }

            if (!$customer || !Hash::check($request->password, $customer->password)) {
                return array(
                    'status' => 'error',
                    'message' => 'Invalid Credentials!',
                    'data' => '',
                    'statusCode' => 422
                );
            }

            $email = $customer->email;
            $user = $this->userRepository->getUserByEmail($email);
            $userId = $user->id;
            if ($emailLogin == 1) {

                $setup = PasscodeSetup::firstOrFail();
                $windowStart = now()->subMinutes($setup->window_minutes);

                $recent = Passcode::where('email', $user->email)
                    ->where('requested_at', '>=', $windowStart)
                    ->latest()
                    ->first();

                $requestCount = $recent ? $recent->request_count + 1 : 1;

                if ($requestCount > $setup->max_requests) {
                    return back()->withErrors([
                        'otp' => 'OTP request limit reached. Try again later.'
                    ]);
                }
                $otp = random_int(100000, 999999);

                Passcode::create([
                    'user_id'       => $user->id,
                    'email'         => $user->email,
                    'passcode'      => $otp,
                    'requested_at'  => now(),
                    'request_count' => $requestCount,
                    'attempt_count' => 0,
                    'locked_until'  => null,
                ]);
                event(new EmailEvent($user->email,'passcode','success','customer'));
                $message = 'Login Passcode sent to email ' . $email;
            } else {
                $mobileNumberCountryCode = $customer->mobile_number_country_code;
                $mobileNumber = $customer->phone;
                $fullMobileNumber = $mobileNumberCountryCode . ' ' . $mobileNumber;
                $message = 'Login OTP sent to mobile number ' . $fullMobileNumber;

                $smsService = new SmsEvent($fullMobileNumber, 'login_otp', 'customer', $userId);
                $otpResponse = $smsService->handle();
            }

            return array(
                'status' => 'success',
                'message' => $message,
                'data' => '',
                'statusCode' => 200
            );
        } catch (\Exception $e) {
            Log::error("customer registration error", [
                "file" => $e->getFile(),
                "line" => $e->getLine(),
                "message" => $e->getMessage(),
            ]);
            return array(
                'status' => 'error',
                'message' => 'Internal Server Error',
                'data' => '',
                'statusCode' => 500
            );
        }
    }

    public function appLogin($request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
            'otp_passcode' => 'required|string',
        ]);
        $username = $request->username;

        $otp_passcode = $request->otp_passcode;

        $emailLogin = 0;
        $mobileLogin = 0;

        $customer = $this->customerRepository->getCustomerByEmail($username);

        if ($customer) {
            $emailLogin = 1;
        } else {
            $customer = $this->customerRepository->getCustomerByMobileNumber($username);

            if ($customer) {
                $mobileLogin = 1;
            }
        }

        if (!$customer) {
            return [
                'status' => 'error',
                'message' => 'Invalid Credentials!',
                'data' => '',
                'statusCode' => 422
            ];
        }

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            return array(
                'status' => 'error',
                'message' => 'Invalid Credentials!',
                'data' => '',
                'statusCode' => 422
            );
        }

        $email = $customer->email;
        $user = $this->userRepository->getUserByEmail($email);
        $userId = $user->id;
        if ($emailLogin == 1) {
            $currentPasscode = $this->masterRepository->getPasscodeByEmailByUserId($email, $userId);
            if ($currentPasscode->passcode != $otp_passcode) {
                return array(
                    'status' => 'error',
                    'message' => 'Invalid Passcode!',
                    'data' => '',
                    'statusCode' => 422
                );
            }
        } else {
            $mobileNumberCountryCode = $customer->mobile_number_country_code;
            $mobileNumber = $customer->phone;
            $fullMobileNumber = $mobileNumberCountryCode . ' ' . $mobileNumber;
            $currentOtp = $this->masterRepository->getOtpByMobileNumberByUserId($fullMobileNumber, $userId);
            if ($currentOtp->otp != $otp_passcode) {
                return array(
                    'status' => 'error',
                    'message' => 'Invalid OTP!',
                    'data' => '',
                    'statusCode' => 422
                );
            }
        }

        $details = [
            'id' => $customer->customer_uuid,
            'customer_type' => $customer->customer_type,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone
        ];

        return array(
            'status' => 'success',
            'message' => 'Customer login Successful!',
            'data' => ['profile' => $details],
            'statusCode' => 200
        );
    }
}
