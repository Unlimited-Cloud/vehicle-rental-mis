<?php

namespace App\Repositories;

use App\Repositories\Interfaces\VehicleRepositoryInterface;
use App\Models\Module;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ProformaInvoice;
use App\Models\VehicleReceipt;
use App\Models\VehicleBooking;

class VehicleRepository implements VehicleRepositoryInterface
{
    public function getAllVehicleReceipts()
    {
        return VehicleReceipt::with(['vehicle', 'customer', 'booking'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getVehicleReceiptsByCustomerId($customerId)
    {
        return VehicleReceipt::with(['vehicle', 'customer', 'booking'])
            ->where('vehicle_receipts.customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getAllVehicleBookingsCount()
    {
        return VehicleBooking::count();
    }

    public function getVehicleBookingsCountByCustomerId($customerId)
    {
        return VehicleBooking::where('vehicle_bookings.customer_id', $customerId)->count();
    }

    public function getAllActiveVehicleBookingsCount()
    {
        return VehicleBooking::where('status', 'confirmed')
            ->whereDate('end_date', '>=', now())->count();
    }

    public function getActiveVehicleBookingsCountByCustomerId($customerId)
    {
        return VehicleBooking::where('status', 'confirmed')
            ->whereDate('end_date', '>=', now())->where('vehicle_bookings.customer_id', $customerId)->count();
    }

    public function getAllPendingVehicleBookingsCount()
    {
        return VehicleBooking::where('status', 'confirmed')
            ->whereDate('end_date', '>=', now())->count();
    }

    public function getPendingVehicleBookingsCountByCustomerId($customerId)
    {
        return VehicleBooking::where('status', 'confirmed')
            ->whereDate('end_date', '>=', now())->where('vehicle_bookings.customer_id', $customerId)->count();
    }

    public function getAllRecentVehicleBookings($orderBy, $order, $limit)
    {
        return VehicleBooking::with(['vehicle', 'customer'])
            ->orderBy($orderBy, $order)
            ->limit(6)
            ->get();
    }

    public function getRecentVehicleBookingsByCustomerId($orderBy, $order, $limit, $customerId)
    {
        VehicleBooking::with(['vehicle', 'customer'])
            ->where('vehicle_bookings.customer_id', $customerId)
            ->orderBy($orderBy, $order)
            ->limit($limit)
            ->get();
    }

    public function getAllVehicleBookings($request)
    {
        $query = VehicleBooking::with([
            'vehicle',
            'customer',
            'payment',
            'driver.user'
        ]);

        // Filter by vehicle
        if ($request->vehicle_id) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        // Filter by customer
        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by driver
        if ($request->driver_id) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->file_no) {
            $query->where('file_no', 'LIKE', '%' . $request->file_no . '%');
        }

        // NEW: Filter by passenger name
        if ($request->passenger) {
            $query->where('passenger_name', 'LIKE', '%' . $request->passenger . '%');
        }

        $bookings = $query->orderBy('start_date', 'desc')->get();
        return $bookings;
    }

    public function getVehicleBookingsByCustomerId($request, $customerId)
    {
        $query = VehicleBooking::with([
            'vehicle',
            'customer',
            'payment',
            'driver.user'
        ]);

        $query->where('customer_id', $customerId);

        // Filter by vehicle
        if ($request->vehicle_id) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        // Filter by customer
        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by driver
        if ($request->driver_id) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->file_no) {
            $query->where('file_no', 'LIKE', '%' . $request->file_no . '%');
        }

        // NEW: Filter by passenger name
        if ($request->passenger) {
            $query->where('passenger_name', 'LIKE', '%' . $request->passenger . '%');
        }

        $bookings = $query->orderBy('start_date', 'desc')->get();
        return $bookings;
    }
}
