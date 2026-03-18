<?php

namespace App\Repositories\Interfaces;



interface VehicleOwnerRepositoryInterface
{
    public function getAllVehicleOwner();
    public function getVehicleOwnerById($id);
}
