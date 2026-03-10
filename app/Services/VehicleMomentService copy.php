<?php

namespace App\Services;

use App\Models\VehicleMoment;

class VehicleMomentService
{

    public function getAll()
    {
        return VehicleMoment::with(['booking.vehicle', 'booking.driver', 'booking.helper'])
            ->latest()
            ->get()
            ->map(function ($moment) {
                // Add computed fields for easier access in views
                $moment->vehicle_name = $moment->booking->vehicle->vehicle_name ?? 'N/A';
                $moment->vehicle_no = $moment->booking->vehicle->vehicle_no ?? 'N/A';
                $moment->driver_name = $moment->booking->driver->name ?? 'N/A';
                $moment->helper_name = $moment->booking->helper->name ?? 'N/A';
                $moment->customer_name = $moment->booking->customer->name ?? 'N/A';
                $moment->start_date = $moment->booking->start_date ?? null;
                $moment->end_date = $moment->booking->end_date ?? null;
                return $moment;
            });
    }

    public function store($data)
    {
        return VehicleMoment::create($data);
    }

    public function find($id)
    {
        return VehicleMoment::findOrFail($id);
    }

    public function update($id, $data)
    {
        $moment = VehicleMoment::findOrFail($id);
        $moment->update($data);

        return $moment;
    }

    public function delete($id)
    {
        $moment = VehicleMoment::findOrFail($id);
        return $moment->delete();
    }
}
