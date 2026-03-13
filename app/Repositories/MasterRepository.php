<?php

namespace App\Repositories;

use App\Repositories\Interfaces\MasterRepositoryInterface;
use App\Models\Module;
use App\Models\Permission;

class MasterRepository implements MasterRepositoryInterface
{
    public function getAllModules(){
        return Module::orderBy('order_by')->get();
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
}