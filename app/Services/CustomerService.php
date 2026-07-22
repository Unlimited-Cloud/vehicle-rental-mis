<?php

namespace App\Services;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\MasterRepositoryInterface;
use App\Utilities\VehicleRentalUtilities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CustomerService
{
    protected $customerRepository;
    protected $userRepository;
    protected $masterRepository;
    
    /**
     * Create a new class instance.
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        UserRepositoryInterface $userRepository,
        MasterRepositoryInterface $masterRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->userRepository = $userRepository;
        $this->masterRepository = $masterRepository;
    }

    public function saveCustomer($request){
        Log::info("Customer Registration Request",["payload" => $request->all()]);
        try{
            $validator = Validator::make($request->all(), [
                'customerType' => 'required|string',
                'first_name' => [
                    'required_if:customerType,individual',
                    'nullable',
                    'string',
                ],

                'last_name' => [
                    'required_if:customerType,individual',
                    'nullable',
                    'string',
                ],

                'institutionName' => [
                    'required_if:customerType,institution',
                    'nullable',
                    'string',
                ],
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
                'country_id' => 'nullable|exists:countries,id',
                'district_id' => 'nullable|exists:district,id',
                'vdc_id' => 'nullable|exists:vdc,id',
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
                'country_id' => $request->country_id,
                'district_id' => $request->district_id,
                'vdc_id' => $request->vdc_id,
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
                $addData['first_name'] = $request->first_name;
                $addData['middle_name'] = $request->middle_name;
                $addData['last_name'] = $request->last_name;
                $addData['name'] = trim($request->first_name . ' ' . $request->middle_name . ' ' . $request->last_name);
            }

            $customer = Customer::create($addData);

            $newCustomerId = $customer->id;

            if($customerType == 'individual'){
                $userName = trim($request->first_name . ' ' . $request->middle_name . ' ' . $request->last_name);
            }else{
                $userName = $request->institutionName;
            }

            $userData = [
                'name' => $userName,
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
                'data' => [
                    'customerId' => $customerUuid,
                    "created_at" => $customer->created_at
                        ->timezone('Asia/Kathmandu')
                        ->format('Y-m-d H:i:s'),
                ], 
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

    public function updateProfile($request){
        Log::info("update profile body",["payload" => $request->all()]);
        try{
            $validator = Validator::make($request->all(), [
                'customer_uuid' => 'nullable|exists:customers,customer_uuid',
                'first_name' => 'required_if:customerType,individual|string',
                'last_name'  => 'required_if:customerType,individual|string',
                'institutionName'  => 'required_if:customerType,institution',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('customers', 'email')
                        ->ignore($request->customer_uuid, 'customer_uuid'),
                ],
                'mobileNumber' => [
                    'required',
                    Rule::unique('customers', 'phone')->ignore($request->customer_uuid, 'customer_uuid'),
                ],
                'country_id' => 'nullable|exists:countries,id',
                'district_id' => 'nullable|exists:district,id',
                'vdc_id' => 'nullable|exists:vdc,id',
            ]);

            if ($validator->fails()) {
                return array(
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'data' => '', 
                    'statusCode' => 422
                );
            }

            $customer_uuid = $request->customer_uuid;

            $updated_at = now();

            $updateData = [
                'customer_uuid' => $customer_uuid,
                'email' => $request->email,
                'mobile_number_country_code' => $request->mobileNumberCountryCode,
                'phone' => $request->mobileNumber,
                'address' => $request->address,
                'country_id' => $request->country_id,
                'district_id' => $request->district_id,
                'vdc_id' => $request->vdc_id,
                'city' => $request->city,
                'state' => $request->state,
                'pan_number' => $request->pan_number,
                'status' => 1,
                'updated_at' => $updated_at,
            ];

            $customer = $this->customerRepository->getCustomerByUuid($customer_uuid);

            if($customer->customer_type == 'institution'){
                $updateData['name'] = $request->institutionName;
                $updateData['license_number'] = $request->licenseNumber;
                $updateData['license_expiry'] = VehicleRentalUtilities::covertDateToYmd($request->licenseExpiry);
            }else{
                $updateData['first_name'] = $request->first_name;
                $updateData['middle_name'] = $request->middle_name;
                $updateData['last_name'] = $request->last_name;
                $updateData['name'] = trim($request->first_name . ' ' . $request->middle_name . ' ' . $request->last_name);
            }
            
            Customer::where('customer_uuid',$customer_uuid)->update($updateData);

            $customerId = $customer->id;

            $customerUser = $this->userRepository->getUserByCustomerIdAndUserType($customerId,'customer_app');

            $userData = [
                'name' => trim($request->first_name . ' ' . $request->middle_name . ' ' . $request->last_name),
                'password' => $customer->password,
                'email' => $request->email,
                'user_type' => 'customer_app',
                'customer_id' => $customerId,
                'created_at' => now()
            ];

            if($customerUser){
                $user = User::where('id',$customerUser->id)->create($userData);
                $userId = $customerUser->id;
            }else{
                $user = User::create($userData);
                $userId = $user->id;
            }

            $customerUpdate = array(
                'author_type' => 'customer_app',
                'author_id' => $userId
            );
            $customer->update($customerUpdate);

            return array(
                'status' => 'success',
                'message' => 'Customer Updated Successfully!',
                'data' => [
                    'customerId' => $customer_uuid,
                    "updated_at" => $customer->updated_at
                        ->timezone('Asia/Kathmandu')
                        ->format('Y-m-d H:i:s'),
                ], 
                'statusCode' => 200
            );
        }catch ( \Exception $e){
            Log::error("CustomerService updateProfile error",[
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

            $stateId = $stateName = null;
            
            $state = $customerDetail->state;
            if(is_numeric($state)){
                $stateDetail = $this->masterRepository->getStateById($state);
                if($stateDetail){
                    $stateId = $stateDetail->id;
                    $stateName = $stateDetail->pname;
                }
            }else{
                $stateDetail = $this->masterRepository->getStateByName($state);
                if($stateDetail){
                    $stateId = $stateDetail->id;
                    $stateName = $stateDetail->pname;
                } 
            }
            
            $details = [
                'id' => $customerDetail->customer_uuid,
                'customer_type' => $customerDetail->customer_type,
                'name' => $customerDetail->name,
                'first_name' => $customerDetail->first_name,
                'middle_name' => $customerDetail->middle_name,
                'last_name' => $customerDetail->last_name,
                'email' => $customerDetail->email,
                'mobile_number_country_code' => $customerDetail->mobile_number_country_code,
                'phone' => $customerDetail->phone,
                'address' => $customerDetail->address,
                'country' => $customerDetail->countryname,
                'stateId' => $stateId,
                'state' => $stateName,
                'district' => $customerDetail->districtname,
                'vdc' => $customerDetail->vdcname,
                'profile_image' => asset($customerDetail->profile_image),
                'district_id' => $customerDetail->customerDistrictId,
                'vdc_id' => $customerDetail->customerVdcId,
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