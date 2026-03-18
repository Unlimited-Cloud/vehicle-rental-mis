<?php

namespace App\Repositories;

use App\Repositories\Interfaces\VehicleOwnerRepositoryInterface;
use App\Models\Module;
use App\Models\Permission;
use App\Models\VehicleOwner;

class VehicleOwnerRepository implements VehicleOwnerRepositoryInterface
{
    public function getAllVehicleOwner()
    {
        return VehicleOwner::orderBy('name')->get();
    }

    public function getVehicleOwnerById($id)
    {
        return VehicleOwner::where('id', $id)->first();
    }
}
