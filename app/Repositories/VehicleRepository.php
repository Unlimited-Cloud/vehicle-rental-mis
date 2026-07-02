<?php

namespace App\Repositories;

use App\Models\EstimateBill;
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


    public function getAllVehicleProforma()
    {
        return ProformaInvoice::with(['vehicle', 'customer', 'booking'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getVehicleProformaByCustomerId($customerId)
    {
        return ProformaInvoice::with(['vehicle', 'customer', 'booking'])
            ->where('proforma_invoices.customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }


    public function getAllVehicleEstimate()
    {
        return EstimateBill::with(['vehicle', 'customer', 'booking'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getVehicleEstimateByCustomerId($customerId)
    {
        return EstimateBill::with(['vehicle', 'customer', 'booking'])
            ->where('estimate_bills.customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getAllVehicleBookingsCount()
    {
        return VehicleBooking::whereNull('deleted_at')->count();
    }
    public function getVehicleBookingsCountByCustomerId($customerId)
    {
        return VehicleBooking::where('vehicle_bookings.customer_id', $customerId)->whereNull('vehicle_bookings.deleted_at')->count();
    }

    public function getVehicleBookingsCountByDriverId($driverId)
    {
        return VehicleBooking::where('vehicle_bookings.driver_id', $driverId)->whereNull('vehicle_bookings.deleted_at')->count();
    }

    public function getAllActiveVehicleBookingsCount()
    {
        return VehicleBooking::where('status', 'confirmed')
            ->whereNull('deleted_at')
            ->whereDate('end_date', '>=', now())->count();
    }

    public function getActiveVehicleBookingsCountByCustomerId($customerId)
    {
        return VehicleBooking::where('status', 'confirmed')
            ->whereNull('deleted_at')
            ->whereDate('end_date', '>=', now())->where('vehicle_bookings.customer_id', $customerId)->count();
    }

    public function getAllPendingVehicleBookingsCount()
    {
        return VehicleBooking::where('status', 'pending')
            ->whereNull('deleted_at')
            ->whereDate('end_date', '>=', now())->count();
    }

    public function getPendingVehicleBookingsCountByCustomerId($customerId)
    {
        return VehicleBooking::where('status', 'pending')
            ->whereNull('deleted_at')
            ->whereDate('end_date', '>=', now())->where('vehicle_bookings.customer_id', $customerId)->count();
    }

    // public function getAllRecentVehicleBookings($orderBy, $order, $limit)
    // {
    //     return VehicleBooking::with(['vehicle', 'customer', 'tripRoute'])
    //         ->whereNull('deleted_at')
    //         ->orderBy($orderBy, $order)
    //         ->limit(6)
    //         ->get();
    // }

    public function getAllRecentVehicleBookings($orderBy, $order)
    {
        return VehicleBooking::with(['vehicle', 'customer', 'tripRoute', 'driver'])
            ->whereNull('vehicle_bookings.deleted_at')
            ->whereDate('start_date', now())
            ->orderBy($orderBy, $order)
            ->get();
    }

    public function getRecentVehicleBookingsByCustomerId($orderBy, $order, $customerId)
    {
        return VehicleBooking::with(['vehicle', 'customer', 'tripRoute', 'driver'])
            ->whereNull('vehicle_bookings.deleted_at')
            ->where('vehicle_bookings.customer_id', $customerId)
            ->whereDate('start_date', now()) // filter today's bookings
            ->orderBy($orderBy, $order)
            ->get();
    }

    public function getRecentVehicleBookingsByDriverId($orderBy, $order, $limit, $driverId)
    {
        return VehicleBooking::with(['vehicle', 'customer', 'tripRoute', 'driver'])
            ->whereNull('vehicle_bookings.deleted_at')
            ->where('vehicle_bookings.driver_id', $driverId)
            ->whereDate('start_date', now()) // filter today's bookings
            ->orderBy($orderBy, $order)
            ->limit($limit)
            ->get();
    }

    // public function getRecentVehicleBookingsByCustomerId($orderBy, $order, $limit, $customerId)
    // {
    //     VehicleBooking::with(['vehicle', 'customer', 'tripRoute'])
    //         ->whereNull('deleted_at')
    //         ->where('vehicle_bookings.customer_id', $customerId)
    //         ->orderBy($orderBy, $order)
    //         ->limit($limit)
    //         ->get();
    // }

    // public function getRecentVehicleBookingsByDriverId($orderBy, $order, $limit, $driverId)
    // {
    //     return VehicleBooking::with(['vehicle', 'customer', 'tripRoute'])
    //         ->whereNull('deleted_at')
    //         ->where('vehicle_bookings.driver_id', $driverId)
    //         ->orderBy($orderBy, $order)
    //         ->limit($limit)
    //         ->get();
    // }

    public function getAllVehicleBookings($request)
    {
        $query = VehicleBooking::with([
            'vehicle',
            'customer',
            'payment',
            'driver.user',
            'tripRoute'
        ])->withExists([
            'vehicleMoment as is_moment_started'
        ])->whereNull('vehicle_bookings.deleted_at');

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

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('start_date', [
                $request->start_date,
                $request->end_date
            ]);
        } elseif ($request->start_date) {
            $query->whereDate('start_date', '>=', $request->start_date);
        } elseif ($request->end_date) {
            $query->whereDate('start_date', '<=', $request->end_date);
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

        $query->where('customer_id', $customerId)->whereNull('vehicle_bookings.deleted_at');

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

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('start_date', [
                $request->start_date,
                $request->end_date
            ]);
        } elseif ($request->start_date) {
            $query->whereDate('start_date', '>=', $request->start_date);
        } elseif ($request->end_date) {
            $query->whereDate('start_date', '<=', $request->end_date);
        }

        $bookings = $query->orderBy('start_date', 'desc')->get();
        return $bookings;
    }

    public function getVehicleBookingsByDriverId($request, $driverId)
    {
        $query = VehicleBooking::with([
            'vehicle',
            'customer',
            'payment',
            'driver.user'
        ]);

        $query->where('driver_id', $driverId)->whereNull('vehicle_bookings.deleted_at');

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

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('start_date', [
                $request->start_date,
                $request->end_date
            ]);
        } elseif ($request->start_date) {
            $query->whereDate('start_date', '>=', $request->start_date);
        } elseif ($request->end_date) {
            $query->whereDate('start_date', '<=', $request->end_date);
        }

        $bookings = $query->orderBy('start_date', 'desc')->get();
        return $bookings;
    }
}
