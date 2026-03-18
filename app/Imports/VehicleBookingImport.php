<?php

namespace App\Imports;

// use Illuminate\Support\Collection;
// use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\VehicleBooking;
use App\Models\VehicleMoment;
use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class VehicleBookingImport implements ToModel, WithHeadingRow
{

    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {

            $vehicle = Vehicle::where('vehicle_name', $row['vehicle_name'])->first();

            if (!$vehicle) {
                throw new \Exception("Vehicle not found: " . $row['vehicle_name']);
            }
            $excelDate = $row['start_date'];
            $unixDate = ($excelDate - 25569) * 86400;
            $mysqlDate = date("Y-m-d", $unixDate);
            $excelEndDate = $row['start_date'];
            $unixEndDate = ($excelEndDate - 25569) * 86400;
            $mysqlEndDate = date("Y-m-d", $unixEndDate);
            //Create Booking
            $booking = VehicleBooking::create([
                'vehicle_id'        => $vehicle->id,
                'customer_id'     => $row['customer_id'] ?? null,
                'from_destination'  => $row['from_destination'],
                'to_destination'    => $row['to_destination'],
                'no_of_people'      => $row['no_of_people'] ?? '0',
                'start_date'        => $mysqlDate,
                'start_time'        => $row['start_time'],
                'end_date'          => $mysqlEndDate,
                'driver_id'         => $row['driver_id'],
                'helper_id'         => $row['helper_id'],
                'start_km'          => $row['start_km'],
                'end_km'            => $row['end_km'],
                'rate_per_day'      => $row['rate_per_day'] ?? 0,
                'approx_fuel_litre'      => $row['approx_fuel_litre'] ?? 0,
                'sub_total'      => $row['sub_total'],
                'tax_amount_type'      => $row['tax_amount_type'] ?? null,
                'tax' => $row['tax'] ?? '0',
                'total_amount'      => $row['total_amount'],
                'status'            => $row['status'] ?? "confirmed",
            ]);

            // Create Vehicle Moment
            VehicleMoment::create([
                'booking_id'            => $booking->id,
                'driver_id'             => $row['driver_id'],
                'helper_id'             => $row['helper_id'],
                'vehicle_no'            => $vehicle->id,
                'signage_information'   => $row['signage_information'] ?? null,
                'start_datetime'        => $mysqlDate,
                'start_km'              => $row['start_km'],
                'start_comments'        => $row['start_comments'] ?? null,
                'end_datetime'          => $mysqlEndDate,
                'end_km'                => $row['end_km'],
                'end_comments'          => $row['end_comments'] ?? null,
                'has_incident'          => $row['has_incident'] ?? 0,
                'incident_report'       => $row['incident_report'] ?? null,
            ]);

            return $booking;
        });
    }
}
