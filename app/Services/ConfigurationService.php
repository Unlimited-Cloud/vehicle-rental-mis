<?php

namespace App\Services;

use App\Repositories\Interfaces\MasterRepositoryInterface;
use App\Models\Permission;
use App\Models\Module;
use Illuminate\Support\Str;


class ConfigurationService
{
    /**
     * Create a new class instance.
     */
    protected $masterRepository;

    public function __construct(
        MasterRepositoryInterface $masterRepository
    ) {
        $this->masterRepository = $masterRepository;
    }

    public function storePermissions($request){
        $moduleId = $request->module_id;
        $moduleDetail = $this->masterRepository->getModuleById($moduleId);
        
        if(!empty($moduleDetail->parent_id)){
            $parentModuleId = $moduleDetail->parent_id;
            $parentModuleDetail = $this->masterRepository->getModuleById($parentModuleId);
        }

        $permissions = ['index'];

        if ($request->permission_create == 1) {
            $permissions[] = 'create';
        }

        if ($request->permission_read == 1) {
            $permissions[] = 'read';
        }

        if ($request->permission_update == 1) {
            $permissions[] = 'update';
        }

        if ($request->permission_delete == 1) {
            $permissions[] = 'delete';
        }

        if (!empty($permissions)) {

            $moduleName = Str::slug($moduleDetail->name, '_');

            foreach ($permissions as $permission) {

                if (isset($parentModuleDetail)) {
                    $parentModuleName = Str::slug($parentModuleDetail->name, '_');
                    $permissionName = $permission . '_' . $parentModuleName . '_' . $moduleName;
                } else {
                    $permissionName = $permission . '_' . $moduleName;
                }

                $permissionExist = $this->masterRepository->getPermissionByName($permissionName);
                $saveData = [
                    'module_id' => isset($parentModuleDetail) ? $parentModuleDetail->id : $moduleId,
                    'name' => $permissionName,
                    'guard_name' => 'web',
                    'submodule_name' => $moduleName
                ];
                
                if($permissionExist){
                    $saveData['updated_at'] = now();
                    Permission::where('id',$permissionExist->id)->update($saveData);
                }else{
                    $saveData['created_at'] = now();
                    Permission::create($saveData);
                }
            }
        }
    }

    public function storeModules($request){

        $moduleName = $request->name;
        $order_by = !empty($request->parent_id) ? $request->parent_id.$request->order_by : $request->order_by;

        if(!empty($request->parent_id)){
            $parentModuleId = $request->parent_id;
            $parentModuleDetail = $this->masterRepository->getModuleById($parentModuleId);
            
            $parentModuleName = Str::slug($parentModuleDetail->name, '_');
            $moduleSlug = Str::slug($moduleName, '_');

            $permissionName = 'index_' . $parentModuleName . '_' . $moduleSlug;
        }else{
            $permissionName = 'index_' . Str::slug($moduleName, '_');
        }
        
        Module::create([
            'name'     => $moduleName,
            'parent_id'    => $request->parent_id,
            'icon' => $request->icon,
            'route'  => $request->route,
            'permission'  => $permissionName,
            'order_by'  => $order_by,
        ]);
    }
}
