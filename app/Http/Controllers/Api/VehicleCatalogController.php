<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\FuelType;
use App\Models\Seater;
use App\Models\TripRouteVehicleTypePrice;
use App\Models\VehicleBooking;
use App\Models\VehicleCatalog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class VehicleCatalogController extends Controller
{

    public function vehicleCatalogByBrand(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'brand_id' => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // get brand
        $brand = Brand::where('id', $request->brand_id)->first();

        if (!$brand) {
            return response()->json([
                'status' => false,
                'message' => 'Brand not found'
            ], 404);
        }

        // match with vehicle.brand (string)
        $vehiclecatalogs = VehicleCatalog::whereRaw('LOWER(brand) = ?', [strtolower($brand->name)])
            ->get();

        if ($vehiclecatalogs->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No vehicle catalog found for this brand'
            ]);
        }

        return response()->json([
            'status' => true,
            'brand_name' => $brand->name,
            'logo' => $brand->logo ? asset('uploads/brands/' . $brand->logo) : null,
            'vehiclecatalogs' => $vehiclecatalogs
        ]);
    }


    public function vehicleCatalogBySeaters(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'seater' => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // get seater
        $seater = Seater::where('name', $request->seater)->first();

        if (!$seater) {
            return response()->json([
                'status' => false,
                'message' => 'Seater not found'
            ], 404);
        }

        // match with vehicle.brand (string)
        $vehiclecatalogs = VehicleCatalog::whereRaw('LOWER(seater) = ?', [strtolower($seater->name)])
            ->get();

        if ($vehiclecatalogs->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No vehicles catalog found for this seater'
            ]);
        }

        return response()->json([
            'status' => true,
            'seater_name' => $seater->name,
            'logo' => $seater->logo ? asset('uploads/seaters/' . $seater->logo) : null,
            'vehiclecatalogs' => $vehiclecatalogs,
        ]);
    }



    public function vehicleCatalogByTransmission(Request $request)
    {
        $request->validate([
            'transmission_id' => 'required'
        ]);

        // get brand
        $transmission = FuelType::where('id', $request->transmission_id)->first();

        if (!$transmission) {
            return response()->json([
                'status' => false,
                'message' => 'Transmission not found'
            ], 404);
        }

        // match with vehicle.brand (string)
        $vehiclecatalog = VehicleCatalog::whereRaw('LOWER(fuel_type) = ?', [strtolower($transmission->name)])
            ->get();

        if ($vehiclecatalog->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No vehicles found for this transmission'
            ]);
        }

        $brands = Brand::pluck('logo', 'name');

        $vehiclecatalog->transform(function ($vehicle) use ($brands) {
            $logo = $brands[$vehicle->brand] ?? null;

            $vehicle->brand_logo = $logo
                ? asset('uploads/brands/' . $logo)
                : null;

            return $vehicle;
        });

        return response()->json([
            'status' => true,
            'transmission_name' => $transmission->name,
            'transmission_logo' => $transmission->logo ? asset('uploads/fuel-types/' . $transmission->logo) : null,
            'vehiclecatalogs' => $vehiclecatalog
        ]);
    }



    public function mostPopularVehiclesByCatalog()
    {

        $counts = DB::table('vehicles')
            ->join('vehicle_bookings', 'vehicle_bookings.vehicle_id', '=', 'vehicles.id')
            ->select(
                'vehicles.brand',
                'vehicles.seater',
                'vehicles.fuel_type',
                DB::raw('COUNT(*) as total_bookings')
            )
            ->groupBy(
                'vehicles.brand',
                'vehicles.seater',
                'vehicles.fuel_type'
            );

        $popularCatalogs = VehicleCatalog::query()
            ->leftJoinSub($counts, 'booking_counts', function ($join) {
                $join->on('vehicle_catalogs.brand', '=', 'booking_counts.brand')
                    ->on('vehicle_catalogs.seater', '=', 'booking_counts.seater')
                    ->on('vehicle_catalogs.fuel_type', '=', 'booking_counts.fuel_type');
            })
            ->select(
                'vehicle_catalogs.*',
                DB::raw('COALESCE(booking_counts.total_bookings, 0) as total_bookings')
            )
            ->orderByDesc('total_bookings')
            ->limit(5)
            ->get();
        return response()->json([
            'status' => 'success',
            'data' => $popularCatalogs
        ]);
    }


    public function getVehicleCatalogPrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_type' => 'nullable|exists:fuel_type,name',
            'seater' => 'required|exists:seaters,name',
            'brand' => 'required|exists:brands,name',
            'est_km'     => 'required|numeric|min:0',
            'est_hour'   => 'nullable|numeric|min:0',
            'overnight'  => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }


        $query = TripRouteVehicleTypePrice::where('brand', $request->brand);

        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }

        if ($request->filled('seater')) {
            $query->where('seater', $request->seater);
        }

        $pricing = $query->first();

        if (!$pricing) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pricing not configured for selected vehicle type.',
            ], 404);
        }

        $kmCharge = $request->est_km * ($pricing->per_km ?? 0);

        $hourCharge = ($request->est_hour ?? 0) * ($pricing->per_hour ?? 0);

        $overnightCharge = ($request->boolean('overnight'))
            ? $pricing->overnight_price
            : 0;

        $subTotal = $kmCharge + $hourCharge + $overnightCharge;

        $vatPercentage = 13;
        $vatAmount = round(($subTotal * $vatPercentage) / 100, 2);
        $total = round($subTotal + $vatAmount, 2);

        return response()->json([
            'status' => 'success',
            'vehicle_type' => $request->vehicle_type,
            'brand' => $request->brand ?? null,
            'seater' => $request->seater ?? null,
            'est_km' => $request->est_km,
            'est_hour' => $request->est_hour ?? 0,
            'overnight' => $request->boolean('overnight'),

            'rate_per_km' => $pricing->per_km,
            'rate_per_hour' => $pricing->per_hour,
            'overnight_charge' => $pricing->overnight_price,

            'km_charge' => number_format($kmCharge, 2),
            'hour_charge' => number_format($hourCharge, 2),
            'overnight_charge_applied' => number_format($overnightCharge, 2),

            'sub_total' => number_format($subTotal, 2),
            'vat' => number_format($vatAmount, 2),
            'total_price' => number_format($total, 2),
        ]);
    }


    public function VehicleCatalogDetailById($id)
    {
        $vehiclecatalog = VehicleCatalog::where('id', $id)->first();

        if (!$vehiclecatalog) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vehicle not found'
            ], 404);
        }

        $data = $vehiclecatalog->toArray();

        // Define boolean fields
        $booleanFields = [
            'dash_cam',
            'ebs',
            'air_conditioning',
            'reverse_camera',
            'camera_360',
            'emergency_braking_system',
            'hillside_braking_system',
            'hill_descent_control',
            'passenger_insured', // Also include this if it's a boolean field
            'is_helper_needed', // If this is boolean
        ];

        // Convert all boolean fields to 'Yes'/'No'
        foreach ($booleanFields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $data[$field] ? 'Yes' : 'No';
            }
        }

        $price = TripRouteVehicleTypePrice::where('brand', $vehiclecatalog->brand)
            ->where('seater', $vehiclecatalog->seater)
            ->first([
                'per_km',
                'per_hour',
                'overnight_price'
            ]);

        // Add pricing fields
        $data['per_km'] = $price?->per_km;
        $data['per_hour'] = $price?->per_hour;
        $data['overnight_price'] = $price?->overnight_price;

        return response()->json([
            'status' => 'success',
            'message' => 'Vehicle Catalog Fetched Successfully',
            'data' => $data
        ]);
    }



    public function vehicleCatalogSorting(Request $request)
    {
        $sortBy = $request->input('sort_by');
        $sortOrder = strtolower($request->input('sort_order', 'asc'));
        $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'asc';

        $vehiclecatalog = VehicleCatalog::query();

        switch ($sortBy) {
            case 'seater':
                $vehiclecatalog->orderBy('seater', $sortOrder);
                break;

            case 'brand':
                $vehiclecatalog->orderBy('brand', $sortOrder);
                break;

            case 'age':
                $vehiclecatalog->orderBy('year', $sortOrder);
                break;
        }

        return response()->json([
            'success' => true,
            'data' => $vehiclecatalog->get()
        ]);
    }



    public function CatalogBookingbyAllStatus($customer_id)
    {
        $customer = Customer::where('customer_uuid', $customer_id)->first();

        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Customer not found',
                'data' => []
            ], 404);
        }

        $bookings = VehicleBooking::where('customer_id', $customer->id)
            ->whereNull('vehicle_bookings.deleted_at')
            ->latest()
            ->get([
                'id',
                'file_no',
                'status',
                'vehicle_id',
                'driver_id',
                'brand',
                'seater',
                'start_date',
                'start_time',
                'end_date',
                'end_time',
                'from_destination',
                'to_destination',
                'rate_per_day',
                'tax',
                'discount',
                'total_amount',
                'payment_status'
            ]);

        // Fetch all catalogs once
        $catalogs = VehicleCatalog::get([
            'brand',
            'seater',
            'image',
            'description',
            'car_images',
            'model',
            'year',
            'fuel_type',
            'transmission',
            'number_plate_color'
        ]);

        // Match by brand + seater
        $bookings->transform(function ($booking) use ($catalogs) {

            $catalog = $catalogs->first(function ($item) use ($booking) {
                return strtolower(trim($item->brand)) == strtolower(trim($booking->brand))
                    && (string)$item->seater === (string)$booking->seater;
            });

            $booking->vehicle_catalog = $catalog;

            return $booking;
        });


        return response()->json([
            'status' => true,
            'data' => $bookings
        ]);
    }


    public function BookingbyStatus($status, $customer_id)
    {
        $validStatuses = ['pending', 'confirmed', 'cancelled', 'started', 'completed', 'paid'];

        if (!in_array($status, $validStatuses)) {
            return response()->json([
                'message' => 'Invalid status'
            ], 400);
        }


        $customer = Customer::where('customer_uuid', $customer_id)->first();

        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Customer not found',
                'data' => []
            ], 404);
        }
        $customer_id = $customer->id;
        $query = VehicleBooking::query()->where('customer_id', $customer_id);


        if ($status === 'completed') {
            $query->whereHas('vehicleMoment', function ($q) {
                $q->whereNotNull('end_datetime');
            });
        } elseif ($status === 'started') {
            $query->whereHas('vehicleMoment', function ($q) {
                $q->whereNotNull('start_datetime')
                    ->whereNull('end_datetime');
            });
        } elseif ($status === 'paid') {
            $query->where('payment_status', 1);
        } else {
            $query->where('status', $status);
        }

        $bookings = $query->with([
            'tripRoute:id,title',
            'vehicle:id,vehicle_name,image,car_images',
            'driver:id,user_id,experience,age',
            'driver.user:id,name',
            'vehicleMoment:id,booking_id,end_datetime,start_datetime'
        ])
            ->get([
                'id',
                'file_no',
                'status',
                'trip_route_id',
                'vehicle_id',
                'driver_id',
                'start_date',
                'start_time',
                'end_date',
                'rate_per_day',
                'tax',
                'discount',
                'total_amount',
                'payment_status'
            ]);

        $bookings->each(function ($booking) {
            if ($booking->vehicleMoment) {

                if (
                    !empty($booking->vehicleMoment->start_datetime) &&
                    empty($booking->vehicleMoment->end_datetime)
                ) {
                    $booking->status = 'started';
                }

                if (!empty($booking->vehicleMoment->end_datetime)) {
                    $booking->status = 'completed';
                }
            }
            if ($booking->payment_status == 1) {
                $booking->status = 'paid';
            }
        });

        return response()->json($bookings);
    }
}
