<?php
// app/Services/GpsDService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GpsDService
{
    protected $baseUrl;
    protected $apiKey;
    protected $version;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.gps.base_url'), '/');
        $this->apiKey = config('services.gps.api_key');
        $this->version = config('services.gps.version');
    }

    private function userApi($command)
    {
        return Http::withoutVerifying()->get(
            $this->baseUrl . '/api/api.php',
            [
                'api' => 'user',
                'ver' => $this->version,
                'key' => $this->apiKey,
                'cmd' => $command
            ]
        );
    }

    /**
     * Get all vehicles with their current locations
     */
    public function getAllVehicles()
    {

        try {
            $response =  $this->userApi("USER_GET_OBJECTS");
            if ($response->successful()) {
                $data = $response->json();
                $vehicles = [];
                foreach ($data as $imei => $vehicle) {
                    $vehicles[] = [
                        'imei' => $vehicle['imei'] ?? 'N/A',
                        'name' => $vehicle['name'] ?? 'Unknown Vehicle',
                        'lat' => $vehicle['lat'] ?? null,
                        'lng' => $vehicle['lng'] ?? null,
                        'speed' => $vehicle['speed'] ?? 0,
                        'last_update' => $vehicle['dt_tracker'] ?? null,
                        'altitude' => $vehicle['altitude'] ?? 0,
                        'angle' => $vehicle['angle'] ?? 0,
                        'loc_valid' => $vehicle['loc_valid'] ?? 0,
                        'odometer' => $vehicle['odometer'] ?? 0,
                        'ip' => $vehicle['ip'] ?? null,
                        'port' => $vehicle['port'] ?? null,
                    ];
                }

                return $vehicles;
            }

            return [];
        } catch (\Exception $e) {
            Log::error('GPS Service Error - getAllVehicles: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get single vehicle location
     */
    public function getVehicleLocation($imei)
    {
        try {
            $allVehicles = $this->getAllVehicles();

            foreach ($allVehicles as $vehicle) {
                if ($vehicle['imei'] == $imei) {
                    return $vehicle;
                }
            }
            $response = $this->userApi("OBJECT_GET_LOCATIONS,$imei");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('GPS Service Error - getVehicleLocation: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get vehicle route for a time period
     */
    public function getVehicleRoute($imei, $from, $to, $stopDuration = 1)
    {
        try {
            $response = $this->userApi("OBJECT_GET_ROUTE,$imei,$from,$to,$stopDuration");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('GPS Service Error - getVehicleRoute: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get vehicle messages
     */
    public function getVehicleMessages($imei, $from, $to)
    {
        try {
            $response = $this->userApi("OBJECT_GET_MESSAGES,$imei,$from,$to");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('GPS Service Error - getVehicleMessages: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get vehicle events
     */
    public function getVehicleEvents($imei, $from, $to)
    {
        try {
            $response = $this->userApi("OBJECT_GET_EVENTS,$imei,$from,$to");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('GPS Service Error - getVehicleEvents: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get last 24 hours events for all vehicles
     */
    public function getLastEvents()
    {
        try {
            $response = $this->userApi("OBJECT_GET_LAST_EVENTS");

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (\Exception $e) {
            Log::error('GPS Service Error - getLastEvents: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get address from coordinates
     */
    public function getAddress($lat, $lng)
    {
        try {
            $response = $this->userApi("GET_ADDRESS,$lat,$lng");

            if ($response->successful()) {
                return $response->body();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('GPS Service Error - getAddress: ' . $e->getMessage());
            return null;
        }
    }
}
