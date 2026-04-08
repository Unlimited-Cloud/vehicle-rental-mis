<?php

namespace App\Imports;

use App\Models\TripRoute;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TripRoutesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new TripRoute([
            'trip_category_id'    => $row['category_id'], // Excel column 'category_id'
            'title'          => $row['route_title'], // Excel column 'route_title'
            'km'             => $row['km'],
            'car_price'      => round($row['car_price']),
            'hiace_price'    => round($row['hiace_jeep_price']),
            'coaster_price'  => round($row['coaster_price']),
            'bus_price'      => round($row['bus_price']),
            'van_price'      => round($row['van_price']),
        ]);
    }
}
