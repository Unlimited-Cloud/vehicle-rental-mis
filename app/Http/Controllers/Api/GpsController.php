<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GpsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

use App\Models\Vehicle;


class GpsController extends Controller
{
    protected $gps;

    public function __construct(GpsService $gps)
    {
        $this->gps = $gps;
    }

    protected function apiResponse($data = null, $message = '', $status = true, $code = 200)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    //Server APIS
    public function addUser(Request $request)
    {
        try {
            $request->validate(['email' => 'required|email']);
            $result = $this->gps->createUserIfNotExists($request->email);

            return $this->apiResponse($result, 'User added successfully.');
        } catch (ValidationException $e) {
            return $this->apiResponse(null, $e->getMessage(), false, 422);
        } catch (\Exception $e) {
            Log::error('GPS Add User Error: ' . $e->getMessage());
            return $this->apiResponse(null, 'Failed to add user.', false, 500);
        }
    }

    public function addVehicle(Request $request)
    {
        try {
            $response = $this->gps->addObject($request->imei, $request->name);
            return $this->apiResponse($response->json(), 'Vehicle added successfully.');
        } catch (\Exception $e) {
            Log::error('GPS Add Vehicle Error: ' . $e->getMessage());
            return $this->apiResponse(null, 'Failed to add vehicle.', false, 500);
        }
    }

    public function assignVehicle(Request $request)
    {
        try {
            $response = $this->gps->assignObjectToUser($request->email, $request->imei);
            return $this->apiResponse($response->json(), 'Vehicle assigned successfully.');
        } catch (\Exception $e) {
            Log::error('GPS Assign Vehicle Error: ' . $e->getMessage());
            return $this->apiResponse(null, 'Failed to assign vehicle.', false, 500);
        }
    }


    //Users APIS
    public function getLocation(Request $request)
    {
        try {
            $response = $this->gps->getObjectLocations($request->imei ?? '*');
            return $this->apiResponse($response->json(), 'Location fetched successfully.');
        } catch (\Exception $e) {
            Log::error('GPS Get Location Error: ' . $e->getMessage());
            return $this->apiResponse(null, 'Failed to fetch location.', false, 500);
        }
    }

    public function getRoute(Request $request)
    {
        try {
            $response = $this->gps->getRoute(
                $request->imei,
                $request->from,
                $request->to,
                $request->time ?? '1'
            );
            return $this->apiResponse($response->json(), 'Route fetched successfully.');
        } catch (\Exception $e) {
            Log::error('GPS Get Route Error: ' . $e->getMessage());
            return $this->apiResponse(null, 'Failed to fetch route.', false, 500);
        }
    }

    public function getAddress(Request $request)
    {
        try {
            $request->validate([
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
            ]);
            $response = $this->gps->getAddress($request->lat, $request->lng);
            return $this->apiResponse($response->json(), 'Address fetched successfully.');
        } catch (ValidationException $e) {
            return $this->apiResponse(null, $e->getMessage(), false, 422);
        } catch (\Exception $e) {
            Log::error('GPS Get Address Error: ' . $e->getMessage());
            return $this->apiResponse(null, 'Failed to fetch address.', false, 500);
        }
    }

    // public function getUserObjects()
    // {
    //     try {
    //         $response = $this->gps->getUserObjects();
    //         $data = json_decode($response->body(), true);
    //         return $this->apiResponse($data, 'User objects fetched successfully.');
    //     } catch (\Exception $e) {
    //         Log::error('GPS Get User Objects Error: ' . $e->getMessage());
    //         return $this->apiResponse(null, 'Failed to fetch user objects.', false, 500);
    //     }
    // }

    public function getUserObjects(Request $request)
    {
        try {
            $response = $this->gps->getUserObjects();
            $data = json_decode($response->body(), true);
            $imei = $request->input('imei');

            if ($imei) {
                $filtered = collect($data)->firstWhere('imei', $imei);

                if (!$filtered) {
                    return $this->apiResponse(null, 'IMEI not found.', false, 404);
                }

                return $this->apiResponse($filtered, 'User object fetched successfully.');
            }

            return $this->apiResponse($data, 'User objects fetched successfully.');
        } catch (\Exception $e) {
            Log::error('GPS Get User Objects Error: ' . $e->getMessage());
            return $this->apiResponse(null, 'Failed to fetch user objects.', false, 500);
        }
    }


    public function getObjectCommands(Request $request)
    {
        try {
            $request->validate([
                'imei' => 'required|string',
            ]);

            $response = $this->gps->getObjectCommands($request->imei);

            return $this->apiResponse(json_decode($response->body(), true), 'Object commands fetched successfully.');
        } catch (ValidationException $e) {
            return $this->apiResponse(null, $e->getMessage(), false, 422);
        } catch (\Exception $e) {
            Log::error('GPS Get Object Commands Error: ' . $e->getMessage());
            return $this->apiResponse(null, 'Failed to fetch object commands.', false, 500);
        }
    }

    public function getMessages(Request $request)
    {
        try {
            $response = $this->gps->getObjectMessages(
                $request->imei,
                $request->from,
                $request->to
            );
            return $this->apiResponse(json_decode($response->body(), true), 'Messages fetched successfully.');
        } catch (\Exception $e) {
            Log::error('GPS Get Messages Error: ' . $e->getMessage());
            return $this->apiResponse(null, 'Failed to fetch messages.', false, 500);
        }
    }

    public function getEvents(Request $request)
    {
        try {
            $response = $this->gps->getObjectEvents(
                $request->imei,
                $request->from,
                $request->to
            );
            return $this->apiResponse(json_decode($response->body(), true), 'Events fetched successfully.');
        } catch (\Exception $e) {
            Log::error('GPS Get Events Error: ' . $e->getMessage());
            return $this->apiResponse(null, 'Failed to fetch events.', false, 500);
        }
    }

    public function getLastEvents()
    {
        try {
            $response = $this->gps->getLastEvents();
            return $this->apiResponse(json_decode($response->body(), true), 'Last 24h events fetched successfully.');
        } catch (\Exception $e) {
            Log::error('GPS Get Last Events Error: ' . $e->getMessage());
            return $this->apiResponse(null, 'Failed to fetch last events.', false, 500);
        }
    }

    public function getMarkers()
    {
        try {
            $response = $this->gps->getMarkers();
            return $this->apiResponse(json_decode($response->body(), true), 'Markers fetched successfully.');
        } catch (\Exception $e) {
            Log::error('GPS Get Markers Error: ' . $e->getMessage());
            return $this->apiResponse(null, 'Failed to fetch markers.', false, 500);
        }
    }

    public function getSavedRoutes()
    {
        try {
            $response = $this->gps->getSavedRoutes();
            return $this->apiResponse(json_decode($response->body(), true), 'Saved routes fetched successfully.');
        } catch (\Exception $e) {
            Log::error('GPS Get Saved Routes Error: ' . $e->getMessage());
            return $this->apiResponse(null, 'Failed to fetch saved routes.', false, 500);
        }
    }

    public function getZones()
    {
        try {
            $response = $this->gps->getZones();
            return $this->apiResponse(json_decode($response->body(), true), 'Zones fetched successfully.');
        } catch (\Exception $e) {
            Log::error('GPS Get Zones Error: ' . $e->getMessage());
            return $this->apiResponse(null, 'Failed to fetch zones.', false, 500);
        }
    }

    public function sendGprs(Request $request)
    {
        try {
            $response = $this->gps->sendGprsCommand(
                $request->imei,
                $request->command_name,
                $request->type,
                $request->command
            );
            return $this->apiResponse(json_decode($response->body(), true), 'GPRS command sent successfully.');
        } catch (\Exception $e) {
            Log::error('GPS Send GPRS Error: ' . $e->getMessage());
            return $this->apiResponse(null, 'Failed to send GPRS command.', false, 500);
        }
    }

    public function sendSms(Request $request)
    {
        try {
            $response = $this->gps->sendSmsCommand(
                $request->imei,
                $request->command_name,
                $request->command
            );
            return $this->apiResponse(json_decode($response->body(), true), 'SMS command sent successfully.');
        } catch (\Exception $e) {
            Log::error('GPS Send SMS Error: ' . $e->getMessage());
            return $this->apiResponse(null, 'Failed to send SMS command.', false, 500);
        }
    }

    public function getFleetLocations()
    {
        $response = $this->gps->getUserObjects();
        $data = [];
        $responseJson = $response->json();
        foreach ($responseJson as $imei => $vehicle) {
            $data[] = [
                'id' => $imei,
                'imei' => $vehicle['imei'] ?? null,
                'name' => $vehicle['name'] ?? null,
                'lat' => $vehicle['lat'] ?? null,
                'lng' => $vehicle['lng'] ?? null,
                'ip' => $vehicle['ip'] ?? null,
                'port' => $vehicle['port'] ?? null,
                'altitude' => $vehicle['altitude'] ?? 0,
                'speed' => $vehicle['speed'] ?? 0,
                'angle' => $vehicle['angle'] ?? 0,
                'dt_tracker' => $vehicle['dt_tracker'] ?? null,
                'dt_last_stop' => $vehicle['dt_last_stop'] ?? null,
                'dt_last_idle' => $vehicle['dt_last_idle'] ?? null,
                'dt_last_move' => $vehicle['dt_last_move'] ?? null,
                'odometer' => $vehicle['odometer'] ?? null,
                'loc_valid' => $vehicle['loc_valid'] ?? 0,
            ];
        }


        return response()->json([
            'status' => true,
            'message' => 'Fleet locations fetched successfully',
            'data' => $data,
        ]);
    }


    public function nearestFleet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'unit' => 'nullable|in:km,mi'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $lat = (float) $request->lat;
        $lng = (float) $request->lng;
        $unit = $request->unit ?? 'km';
        $earthRadius = ($unit === 'km') ? 6371 : 3959;

        try {
            $fleetResponse = $this->getFleetLocations();
            $fleetData = [];

            if ($fleetResponse instanceof \Illuminate\Http\JsonResponse) {
                $responseArray = $fleetResponse->getData(true);

                if (
                    isset($responseArray['status']) &&
                    $responseArray['status'] === true &&
                    isset($responseArray['data'])
                ) {
                    $fleetData = $responseArray['data'];
                }
            }

            if (empty($fleetData)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No vehicles found',
                    'data' => null
                ]);
            }

            $nearestVehicle = null;
            $minDistance = PHP_FLOAT_MAX;

            foreach ($fleetData as $vehicle) {

                if (
                    !isset($vehicle['loc_valid']) ||
                    $vehicle['loc_valid'] != '1' ||
                    !isset($vehicle['lat'], $vehicle['lng']) ||
                    !is_numeric($vehicle['lat']) ||
                    !is_numeric($vehicle['lng']) ||
                    $vehicle['lat'] == 0 ||
                    $vehicle['lng'] == 0
                ) {
                    continue;
                }

                $distance = $this->calculateDistance(
                    $lat,
                    $lng,
                    (float) $vehicle['lat'],
                    (float) $vehicle['lng'],
                    $earthRadius
                );

                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $nearestVehicle = $vehicle;
                }
            }

            if (!$nearestVehicle) {
                return response()->json([
                    'status' => false,
                    'message' => 'No valid vehicle locations found',
                    'data' => null
                ]);
            }

            $nearestVehicle['distance'] = round($minDistance, 2);
            $nearestVehicle['distance_unit'] = $unit;

            return response()->json([
                'status' => true,
                'message' => 'Nearest vehicle fetched successfully',
                'data' => [
                    'search_point' => [
                        'lat' => $lat,
                        'lng' => $lng
                    ],
                    'vehicle' => $nearestVehicle
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in nearestFleet: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Error finding nearest vehicle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2, $earthRadius = 6371)
    {
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }


    public function vehicleDistanceFromGps(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'unit' => 'nullable|in:km,mi'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $vehicle = Vehicle::findOrFail($request->vehicle_id);

            if (!$vehicle->imei) {
                return response()->json([
                    'status' => false,
                    'message' => 'Vehicle IMEI not found'
                ]);
            }

            $fleetResponse = $this->getFleetLocations();
            $fleetData = $fleetResponse->getData(true)['data'] ?? [];

            $imei = $vehicle->imei;
            $vehicleGps = collect($fleetData)->firstWhere('imei', $imei);

            // check GPS device by IMEI
            if (!isset($vehicleGps)) {
                return response()->json([
                    'status' => false,
                    'message' => 'GPS data not found for this vehicle'
                ]);
            }


            $gps = collect($fleetData)->firstWhere('imei', $imei);

            if (
                $gps['loc_valid'] != '1' ||
                !is_numeric($gps['lat']) ||
                !is_numeric($gps['lng'])
            ) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid GPS location'
                ]);
            }



            $unit = $request->unit ?? 'km';
            $earthRadius = $unit === 'km' ? 6371 : 3959;

            $distance = $this->calculateDistance(
                $request->lat,
                $request->lng,
                (float) $gps['lat'],
                (float) $gps['lng'],
                $earthRadius
            );

            $speed = (float) ($gps['speed'] ?? 0);

            $etaMinutes = null;

            if ($speed > 0) {
                $etaHours = $distance / $speed;
                $etaMinutes = round($etaHours * 60);
            }

            return response()->json([
                'status' => true,
                'message' => 'Vehicle GPS distance calculated',
                'data' => [
                    'vehicle_id' => $vehicle->id,
                    'imei' => $imei,
                    'gps_location' => [
                        'lat' => (float) $gps['lat'],
                        'lng' => (float) $gps['lng'],
                        'speed' => $gps['speed'],
                        'last_update' => $gps['dt_tracker']
                    ],
                    'search_location' => [
                        'lat' => (float) $request->lat,
                        'lng' => (float) $request->lng
                    ],
                    'distance' => round($distance, 2),
                    'eta_minutes' => $etaMinutes,
                    'unit' => $unit
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('vehicleDistanceFromGps error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
