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
        $data = Customer::select(
            'customers.*',
            'customers.country_id as customerCountryId',
            'customers.district_id as customerDistrictId',
            'customers.vdc_id as customerVdcId',
            'countries.name as countryname',
            'district.name as districtname',
            'vdc.name as vdcname',
        )
        ->where('customer_uuid',$uuid)
        ->leftJoin('countries','countries.id','=','customers.country_id')
        ->leftJoin('district','district.id','=','customers.district_id')
        ->leftJoin('vdc','vdc.id','=','customers.vdc_id')
        ->first();
        return $data;
    }
}