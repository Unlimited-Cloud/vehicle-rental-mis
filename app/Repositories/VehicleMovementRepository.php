<?php

namespace App\Repositories;

use App\Models\EstimateBill;
use App\Repositories\Interfaces\VehicleMovementRepositoryInterface;
use App\Models\Module;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PetrolPumpTransaction;
use App\Models\ProformaInvoice;
use App\Models\VehicleReceipt;
use App\Models\VehicleBooking;
use App\Models\VehicleMoment;

class VehicleMovementRepository implements VehicleMovementRepositoryInterface
{

    public function getRecentVehicleMovementsByDriverId($orderBy, $order, $limit, $driverId)
    {
        VehicleMoment::with(['vehicle', 'booking', 'driver', 'tripRoute'])
            ->where('vehicle_moments.driver_id', $driverId)
            ->orderBy($orderBy, $order)
            ->limit($limit)
            ->get();
    }

    public function getAllVehicleMovements($request)
    {
        $query = VehicleMoment::with([
            'vehicle',
            'driver.user',
            'helper.user',
            'booking.customer'
        ])->select('vehicle_moments.*');

        // Add select raw for additional fields if needed
        $query->addSelect([
            'v.vehicle_name',
            'd.name as driver_name',
            'h.name as helper_name',
            'c.name as customer_name',
            'vb.start_date',
            'vb.end_date',
            'vb.file_no',
        ])
            ->leftJoin('vehicle_bookings as vb', 'vb.id', '=', 'vehicle_moments.booking_id')
            ->leftJoin('vehicles as v', 'v.id', '=', 'vb.vehicle_id')
            ->leftJoin('crew_profiles as cp_driver', 'cp_driver.id', '=', 'vb.driver_id')
            ->leftJoin('users as d', 'd.id', '=', 'cp_driver.user_id')
            ->leftJoin('crew_profiles as cp_helper', 'cp_helper.id', '=', 'vb.helper_id')
            ->leftJoin('users as h', 'h.id', '=', 'cp_helper.user_id')
            ->leftJoin('customers as c', 'c.id', '=', 'vb.customer_id');

        // Apply filters
        if ($request->filled('vehicle_id')) {
            $query->where('vb.vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('driver_id')) {
            $query->where('vehicle_moments.driver_id', $request->driver_id);
        }

        return $query->orderBy('vehicle_moments.created_at', 'desc')->get();
    }


    public function getVehicleMovementsByDriverId($request, $driverId)
    {
        $query = VehicleMoment::with([
            'vehicle',
            'driver.user',
            'helper.user',
            'booking.customer'
        ])->select('vehicle_moments.*');

        // Add select raw for additional fields
        $query->addSelect([
            'v.vehicle_name',
            'd.name as driver_name',
            'h.name as helper_name',
            'c.name as customer_name',
            'vb.start_date',
            'vb.end_date',
        ])
            ->leftJoin('vehicle_bookings as vb', 'vb.id', '=', 'vehicle_moments.booking_id')
            ->leftJoin('vehicles as v', 'v.id', '=', 'vb.vehicle_id')
            ->leftJoin('crew_profiles as cp_driver', 'cp_driver.id', '=', 'vb.driver_id')
            ->leftJoin('users as d', 'd.id', '=', 'cp_driver.user_id')
            ->leftJoin('crew_profiles as cp_helper', 'cp_helper.id', '=', 'vb.helper_id')
            ->leftJoin('users as h', 'h.id', '=', 'cp_helper.user_id')
            ->leftJoin('customers as c', 'c.id', '=', 'vb.customer_id');

        // Filter by driver ID
        $query->where('vehicle_moments.driver_id', $driverId);

        // Apply additional filters from request
        if ($request->filled('vehicle_id')) {
            $query->where('vb.vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('vehicle_moments.start_datetime', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('vehicle_moments.end_datetime', '<=', $request->end_date);
        }

        // You can also filter by booking status if needed
        if ($request->filled('status')) {
            $query->where('vb.status', $request->status);
        }

        return $query->orderBy('vehicle_moments.created_at', 'desc')->get();
    }

    public function getAllTransactions($request)
    {
        $query = PetrolPumpTransaction::with([
            'petrolPump',
            'vehicle',
            'customer',
            'driver'
        ])->select('petrol_pump_transactions.*');

        // Apply filters
        if ($request->filled('petrol_pump_id')) {
            $query->where('petrol_pump_id', $request->petrol_pump_id);
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }

        // Optional: Filter by vehicle
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        // Optional: Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        return $query->latest('transaction_date')->get();
    }

    public function getTransactionsByDriverId($request, $driverId = null)
    {
        $query = PetrolPumpTransaction::with([
            'petrolPump',
            'vehicle',
            'customer',
            'driver'
        ]);

        // Join only valid relations
        $query->leftJoin('vehicles', 'vehicles.id', '=', 'petrol_pump_transactions.vehicle_id')
            ->leftJoin('crew_profiles', 'crew_profiles.id', '=', 'petrol_pump_transactions.driver_id')
            ->leftJoin('users', 'users.id', '=', 'crew_profiles.user_id');

        // Driver filter (FIXED)
        if ($driverId) {
            $query->where(function ($q) use ($driverId) {
                $q->where('petrol_pump_transactions.driver_id', $driverId)
                    ->orWhere('crew_profiles.id', $driverId)
                    ->orWhere('users.id', $driverId);
            });
        }

        // Filters
        if ($request->filled('petrol_pump_id')) {
            $query->where('petrol_pump_transactions.petrol_pump_id', $request->petrol_pump_id);
        }

        if ($request->filled('transaction_type')) {
            $query->where('petrol_pump_transactions.transaction_type', $request->transaction_type);
        }

        if ($request->filled('invoice_number')) {
            $query->where('petrol_pump_transactions.invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        if ($request->filled('from_date')) {
            $query->whereDate('petrol_pump_transactions.transaction_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('petrol_pump_transactions.transaction_date', '<=', $request->to_date);
        }

        if ($request->filled('vehicle_id')) {
            $query->where('petrol_pump_transactions.vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('customer_id')) {
            $query->where('petrol_pump_transactions.customer_id', $request->customer_id);
        }

        return $query
            ->orderByDesc('petrol_pump_transactions.transaction_date')
            ->select('petrol_pump_transactions.*')
            ->get();
    }
}
