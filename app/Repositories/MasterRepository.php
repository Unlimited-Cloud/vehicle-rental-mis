<?php

namespace App\Repositories;

use App\Repositories\Interfaces\MasterRepositoryInterface;
use App\Models\Module;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ProformaInvoice;
use App\Models\Passcode;
use App\Models\Otp;
use App\Models\Province;

class MasterRepository implements MasterRepositoryInterface
{
    public function getAllModules(){
        return Module::orderBy('order_by')->get();
    }

    public function getAllRoles(){
        return Role::orderBy('name')->get();
    }

    public function getAllPermissions(){
        return Permission::select('permissions.*','modules.name as modulename')
        ->join('modules','modules.id','=','permissions.module_id')->orderBy('modules.name')->orderBy('permissions.submodule_name')->get();
    }

    public function getModuleById($id){
        return Module::where('id',$id)->first();
    }

    public function getPermissionByName($name){
        return Permission::where('name',$name)->first();
    }

    public function getParentModules(){
        return Module::whereNull('parent_id')->orderBy('name')->get();
    }

    public function getAllProformaInvoices(){
        ProformaInvoice::with(['vehicle', 'booking'])
            ->latest()
            ->get();
    }

    public function getSubModules($parentId){
        return Module::where('parent_id',$parentId)->orderBy('order_by')->get()->toArray();
    }

    public function getPasscodeByEmailByUserId($email,$userId){
        return Passcode::where('email',$email)->where('user_id',$userId)->orderBy('id','desc')->first();
    }

    public function getOtpByMobileNumberByUserId($mobileNumber,$userId){
        return Otp::where('mobile_number',$mobileNumber)->where('user_id',$userId)->orderBy('id','desc')->first();
    }

    public function getAllOtps(){
        return Otp::get();
    }

    public function getStateById($id){
        return Province::where('id',$id)->first();
    }

    public function getStateByName($name){
        return Province::where('pname',$name)->first();
    }
}