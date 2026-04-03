<?php

namespace App\Repositories\Interfaces;



interface VehicleRepositoryInterface
{
    public function getAllVehicleReceipts();
    public function getVehicleReceiptsByCustomerId($customerId);

    public function getAllVehicleProforma();
    public function getVehicleProformaByCustomerId($customerId);

    public function getAllVehicleEstimate();
    public function getVehicleEstimateByCustomerId($customerId);

    public function getAllVehicleBookingsCount();
    public function getVehicleBookingsCountByCustomerId($customerId);
    public function getAllActiveVehicleBookingsCount();
    public function getActiveVehicleBookingsCountByCustomerId($customerId);

    public function getAllPendingVehicleBookingsCount();
    public function getPendingVehicleBookingsCountByCustomerId($customerId);

    public function getAllRecentVehicleBookings($orderBy, $order, $limit);
    public function getRecentVehicleBookingsByCustomerId($orderBy, $order, $limit, $customerId);

    public function getAllVehicleBookings($request);
    public function getVehicleBookingsByCustomerId($request, $customerId);
}
