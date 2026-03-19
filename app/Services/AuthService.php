<?php

namespace App\Services;

use App\Repositories\Interfaces\MasterRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Models\Permission;
use App\Models\Module;
use App\Models\Customer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Events\SmsEvent;

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

    public function getOtpPasscodeAppLogin($request){
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
        if($emailLogin == 1){
            $otp_passcode = $this->masterRepository->getPasscodeByEmailByUserId($email,$userId);
        }else{
            $mobileNumber = $customer->phone;
            $smsService = new SmsEvent($mobileNumber, 'At_Login', 'customer', $userId);
            $otp_passcode = $this->masterRepository->getOtpByMobileNumberByUserId($mobileNumber,$userId);
        }

        return array(
            'status' => 'success',
            'message' => 'Please Verify OTP/Passcode!',
            'data' => ['otp_passcode' => $otp_passcode], 
            'statusCode' => 200
        );
    }

    public function appLogin($request){
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
        if($emailLogin == 1){
            $currentPasscode = $this->masterRepository->getPasscodeByEmailByUserId($email,$userId);
            if($currentPasscode->passcode != $otp_passcode){
                return array(
                    'status' => 'error',
                    'message' => 'Invalid Passcode!',
                    'data' => '', 
                    'statusCode' => 422
                );
            }
        }else{
            $mobileNumber = $customer->phone;
            $currentOtp = $this->masterRepository->getOtpByMobileNumberByUserId($mobileNumber,$userId);
            if($currentOtp->otp != $otp_passcode){
                return array(
                    'status' => 'error',
                    'message' => 'Invalid Passcode/OTP!',
                    'data' => '', 
                    'statusCode' => 422
                );
            }
        }

        return array(
            'status' => 'success',
            'message' => 'Customer login Successful!',
            'data' => ['profile' => $customer], 
            'statusCode' => 200
        );
    }
}