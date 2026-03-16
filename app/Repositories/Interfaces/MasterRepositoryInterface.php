<?php
namespace App\Repositories\Interfaces;



interface MasterRepositoryInterface
{
    public function getAllModules();

    public function getAllRoles();
    public function getAllPermissions();
    public function getModuleById($id);
    public function getPermissionByName($name);
    public function getParentModules();
}