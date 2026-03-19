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
    public function getAllProformaInvoices();
    public function getSubModules($parentId);
    public function getPasscodeByEmailByUserId($email,$userId);
    public function getOtpByMobileNumberByUserId($mobileNumber,$userId);
    public function getAllOtps();
}