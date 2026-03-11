<?php

namespace App\Repositories\Interfaces;



interface UserRepositoryInterface
{




    public function getAllUsers($search);
    public function storeUser($data);
    public function updateUser($data, $id);

    public function deleteUser($id);


    public function getUser($id);


    public function getAllPermissions();

    public function getAllPermissionWithPagination();

    public function getAllRoles();
    public function getAllPartners();

    public function savePermission($permission);

    public function getPermissionById($id);

    public function updatePermission($id, $permission);

    public function deletePermission($id);


    public function getRolesWithPermissions($id);

    public function getAllUsersList($search);


    public function ProfileDetails();

    public function updateStatus($toggelval, $userId);
    public function getAllPartnerUsersList($search);
    public function storePartnerUser($data);
    public function getAllNonPartnerUsersList($search);
    public function getAllPartnerRoles();
    public function getAllLoggedInPartnerUsersList($search,$partnerId);
    public function getLoggedInPartnerData($partnerId);
    public function getPartnerUser($id);
    public function updatePartnerUser($data, $id);
    public function checkIsWhiteLabelledPartnerUser($userId);
    public function getUserByEmail($email);
    public function getRoleById($id);
    public function getUserByUuid($uuid);
    public function getCustomerUserByEmail($email);
}
