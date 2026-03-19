<?php

namespace App\Repositories;

use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Models\Module;
use App\Models\Permission;
use App\Models\Customer;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function getAllCustomers(){
        return Customer::orderBy('name')->get();
    }

    public function getCustomerById($id){
        return Customer::where('id',$id)->first();
    }

    public function getCustomerByEmail($email){
        return Customer::where('email',$email)->first();
    }

    public function getCustomerByMobileNumber($mobileNumber){
        return Customer::where('phone',$mobileNumber)->first();
    }

    public function getCustomerByUuid($uuid){
        return Customer::where('customer_uuid',$uuid)->first();
    }
}