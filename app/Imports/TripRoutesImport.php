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
            'car_price'      => $row['car_price'],
            'hiace_price'    => $row['hiace_jeep_price'],
            'coaster_price'  => $row['coaster_price'],
            'bus_price'      => $row['bus_price'],
            'van_price'      => $row['van_price'],
        ]);
    }
}
