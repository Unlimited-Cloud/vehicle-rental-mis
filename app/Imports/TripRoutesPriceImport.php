<?php

namespace App\Imports;

use App\Models\TripRoute;
use App\Models\TripRouteVehiclePrice;
use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TripRoutesPriceImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $tripRoute = TripRoute::where('title', trim($row['route_title']))->first();
        $vehicle = Vehicle::where('vehicle_name', trim($row['vehicle_name']))->first();

        if (!$tripRoute || !$vehicle) {
            return null;
        }

        return TripRouteVehiclePrice::updateOrCreate(
            [
                'trip_route_id' => $tripRoute->id,
                'vehicle_id'    => $vehicle->id,
            ],
            [
                'price' => round($row['price']),
            ]
        );
    }
}
