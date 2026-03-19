<?php
namespace App\Repositories\Interfaces;



interface CustomerRepositoryInterface
{
    public function getAllCustomers();
    public function getCustomerById($id);
    public function getCustomerByUuid($uuid);
    public function getCustomerByEmail($email);
    public function getCustomerByMobileNumber($mobileNumber);
}