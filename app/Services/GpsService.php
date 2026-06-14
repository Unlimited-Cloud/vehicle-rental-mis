<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GpsService
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

    private function serverApi($command)
    {
        return Http::withoutVerifying()->get(
            $this->baseUrl . '/api/api.php',
            [
                'api' => 'server',
                'ver' => $this->version,
                'key' => $this->apiKey,
                'cmd' => $command
            ]
        );
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
    // ================= SERVER API =================

    public function addUser($email)
    {
        return $this->serverApi("ADD_USER,$email,true");
    }

    public function checkUserExists($email)
    {
        return $this->serverApi("CHECK_USER_EXISTS,$email");
    }

    public function createUserIfNotExists($email)
    {
        $exists = $this->checkUserExists($email)->body();

        if ($exists == true) {
            return [
                'status' => 'exists'
            ];
        }

        $created = $this->addUser($email)->json();

        return [
            'status' => 'created',
            'data' => $created
        ];
    }

    public function addObject($imei, $name)
    {
        return $this->serverApi("ADD_OBJECT,$imei,$name,false,2030-01-01");
    }

    public function assignObjectToUser($email, $imei)
    {
        return $this->serverApi("ADD_USER_OBJECT,$email,$imei");
    }



    // ================= USER API =================

    public function getObjectLocations($imei = '*')
    {
        return $this->userApi("OBJECT_GET_LOCATIONS,$imei");
    }

    public function getObjectCommands($imei)
    {
        return $this->userApi("OBJECT_GET_CMDS,$imei");
    }

    public function getRoute($imei, $from, $to)
    {
        return $this->userApi("OBJECT_GET_ROUTE,$imei,$from,$to,1");
    }

    public function getAddress($lat, $lng)
    {
        return $this->userApi("GET_ADDRESS,$lat,$lng");
    }

    //  Get all user objects (vehicles)
    public function getUserObjects()
    {
        return $this->userApi("USER_GET_OBJECTS");
    }

    //  Get object messages
    public function getObjectMessages($imei, $from, $to)
    {
        return $this->userApi("OBJECT_GET_MESSAGES,$imei,$from,$to");
    }

    //  Get object events
    public function getObjectEvents($imei, $from, $to)
    {
        return $this->userApi("OBJECT_GET_EVENTS,$imei,$from,$to");
    }

    //  Get last 24h events
    public function getLastEvents()
    {
        return $this->userApi("OBJECT_GET_LAST_EVENTS");
    }

    //  Get markers
    public function getMarkers()
    {
        return $this->userApi("USER_GET_MARKERS");
    }

    //  Get saved routes
    public function getSavedRoutes()
    {
        return $this->userApi("USER_GET_ROUTES");
    }

    //  Get zones (geofences)
    public function getZones()
    {
        return $this->userApi("USER_GET_ZONES");
    }

    //  Send GPRS command
    public function sendGprsCommand($imei, $commandName, $type, $command)
    {
        return $this->userApi("OBJECT_CMD_GPRS,$imei,$commandName,$type,\"$command\"");
    }

    // Send SMS command
    public function sendSmsCommand($imei, $commandName, $command)
    {
        return $this->userApi("OBJECT_CMD_SMS,$imei,$commandName,\"$command\"");
    }
}
