<?php

namespace App\Services;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Utilities\VehicleRentalUtilities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CustomerService
{
    protected $customerRepository;
    /**
     * Create a new class instance.
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    public function saveCustomer($request){
        try{
            $validator = Validator::make($request->all(), [
                'customerType' => 'required|string',
                'first_name' => 'required_if:customerType,individual|string',
                'last_name'  => 'required_if:customerType,individual|string',
                'institutionName'  => 'required_if:customerType,institution|string',
                'email' => 'required|email|unique:customers',
                'mobileNumber' => 'required|unique:customers,phone',
                'password' => [
                    'required',
                    'string',
                    'min:12', // at least 12 characters
                    'regex:/[A-Z]/', // at least 1 capital letter
                    'regex:/[!@#$%^&*(),.?":{}|<>]/', // at least 1 special character
                    'max:255',
                ],
            ]);

            if ($validator->fails()) {
                return array(
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'data' => '', 
                    'statusCode' => 422
                );
            }

            $customerUuid = Str::uuid()->toString();
            
            $customerType = $request->customerType;

            $addData = [
                'customer_uuid' => $customerUuid,
                'customer_type' => $customerType,
                'email' => $request->email,
                'mobile_number_country_code' => $request->mobileNumberCountryCode,
                'phone' => $request->mobileNumber,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'pan_number' => $request->pan_number,
                'status' => 1,
                'password' => Hash::make($request->password)
            ];

            if($customerType == 'institution'){
                $addData['name'] = $request->institutionName;
                $addData['license_number'] = $request->licenseNumber;
                $addData['license_expiry'] = VehicleRentalUtilities::covertDateToYmd($request->licenseExpiry);
            }else{
                $addData['first_name'] = $request->firstName;
                $addData['middle_name'] = $request->middleName;
                $addData['last_name'] = $request->lastName;
                $addData['name'] = trim($request->first_name . ' ' . $request->middle_name . ' ' . $request->last_name);
            }

            $customer = Customer::create($addData);

            $newCustomerId = $customer->id;

            $userData = [
                'name' => trim($request->first_name . ' ' . $request->middle_name . ' ' . $request->last_name),
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => 'customer_app',
                'customer_id' => $newCustomerId,
                'created_at' => now()
            ];
            $user = User::create($userData);
            $newUserId = $user->id;

            $customerUpdate = array(
                'author_type' => 'customer_app',
                'author_id' => $newUserId
            );
            $customer->update($customerUpdate);

            return array(
                'status' => 'success',
                'message' => 'Customer Registered Successfully!',
                'data' => ['customerId' => $customerUuid], 
                'statusCode' => 200
            );
        }catch ( \Exception $e){
            Log::error("customer registration error",[
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

    public function getProfileByUuid($uuid){
        try{
            $customerDetail = $this->customerRepository->getCustomerByUuid($uuid);
            if(!$customerDetail){
                return array(
                    'status' => 'error',
                    'message' => 'Invalid Customer ID!',
                    'data' => ['customerId' => $uuid], 
                    'statusCode' => 422
                );
            }
            $details = [
                'id' => $customerDetail->customer_uuid,
                'customer_type' => $customerDetail->customer_type,
                'name' => $customerDetail->name,
                'email' => $customerDetail->email,
                'phone' => $customerDetail->phone,
                'profile_image' => asset($customerDetail->profile_image),
            ];
            return array(
                'status' => 'success',
                'message' => 'Customer Fetched Successfully!',
                'data' => ['customerDetail' => $details], 
                'statusCode' => 200
            );
        }catch ( \Exception $e){
            Log::error("customerController getProfileByUuid",[
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
}