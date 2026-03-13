<?php

namespace App\Repositories;

use App\Repositories\Interfaces\MasterRepositoryInterface;
use App\Models\Module;

class MasterRepository implements MasterRepositoryInterface
{
    public function getAllModules(){
        return Module::orderBy('order_by')->get();
    }
}