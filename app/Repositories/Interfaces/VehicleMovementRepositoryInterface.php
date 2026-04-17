<?php

namespace App\Repositories\Interfaces;



interface VehicleMovementRepositoryInterface
{
    public function getVehicleMovementsByDriverId($request, $driverId);
    public function getAllVehicleMovements($request);
    public function getTransactionsByDriverId($request, $driverId);
    public function getAllTransactions($request);
}
