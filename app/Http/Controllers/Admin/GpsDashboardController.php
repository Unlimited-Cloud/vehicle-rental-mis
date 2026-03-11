<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GpsDService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class GpsDashboardController extends Controller
{
    protected $gpsService;

    public function __construct(GpsDService $gpsService)
    {
        $this->gpsService = $gpsService;
    }

    /**
     * Show main fleet dashboard
     */
    public function index()
    {
        Gate::authorize('index_gps');
        try {
            $vehicles = $this->gpsService->getAllVehicles();

            // Calculate statistics
            $stats = [
                'total' => count($vehicles),
                'moving' => 0,
                'stopped' => 0,
                'offline' => 0,
            ];

            foreach ($vehicles as $vehicle) {
                if ($vehicle['speed'] > 0) {
                    $stats['moving']++;
                } elseif ($vehicle['loc_valid'] == 1) {
                    $stats['stopped']++;
                } else {
                    $stats['offline']++;
                }
            }

            return view('layouts.admin.gbs_dashboard.fleet', [
                'vehicles' => $vehicles,
                'stats' => $stats,
                'ungroupedVehicles' => array_slice($vehicles, 0, 9), // First 9 as "ungrouped"
                'lastUpdate' => now()->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard Error: ' . $e->getMessage());

            return view('layouts.admin.gbs_dashboard.fleet', [
                'vehicles' => [],
                'stats' => ['total' => 0, 'moving' => 0, 'stopped' => 0, 'offline' => 0],
                'ungroupedVehicles' => [],
                'lastUpdate' => now()->format('Y-m-d H:i:s'),
                'error' => 'Unable to load fleet data. Please try again later.'
            ]);
        }
    }

    /**
     * AJAX endpoint for live updates
     */
    public function getLiveData()
    {
        try {
            $vehicles = $this->gpsService->getAllVehicles();

            // Format for JavaScript
            $formattedVehicles = collect($vehicles)->map(function ($vehicle) {
                return [
                    'imei' => $vehicle['imei'],
                    'name' => $vehicle['name'],
                    'lat' => $vehicle['lat'],
                    'lng' => $vehicle['lng'],
                    'speed' => $vehicle['speed'],
                    'last_update' => $vehicle['last_update'],
                    'odometer' => $vehicle['odometer'] ?? 0,
                    'status' => $vehicle['speed'] > 0 ? 'moving' : ($vehicle['loc_valid'] ? 'stopped' : 'offline'),
                    'marker_color' => $vehicle['speed'] > 0 ? '#28a745' : ($vehicle['loc_valid'] ? '#ffc107' : '#dc3545')
                ];
            });

            // Calculate updated stats
            $stats = [
                'total' => count($vehicles),
                'moving' => $formattedVehicles->where('status', 'moving')->count(),
                'stopped' => $formattedVehicles->where('status', 'stopped')->count(),
                'offline' => $formattedVehicles->where('status', 'offline')->count(),
            ];

            return response()->json([
                'success' => true,
                'vehicles' => $formattedVehicles,
                'stats' => $stats,
                'last_update' => now()->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            Log::error('Live Data Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load live data'
            ], 500);
        }
    }

    /**
     * Get vehicle details for modal
     */
    public function getVehicleDetails($imei)
    {
        try {
            $location = $this->gpsService->getVehicleLocation($imei);

            if ($location) {
                return response()->json([
                    'success' => true,
                    'data' => $location
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Vehicle Details Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load vehicle details'
            ], 500);
        }
    }

    /**
     * Get vehicle route history
     */
    public function getVehicleHistory(Request $request, $imei)
    {
        try {
            $from = $request->get('from', now()->subDay()->format('Y-m-d H:i:s'));
            $to = $request->get('to', now()->format('Y-m-d H:i:s'));

            $route = $this->gpsService->getVehicleRoute($imei, $from, $to);
            $messages = $this->gpsService->getVehicleMessages($imei, $from, $to);
            $events = $this->gpsService->getVehicleEvents($imei, $from, $to);

            return response()->json([
                'success' => true,
                'data' => [
                    'route' => $route,
                    'messages' => $messages,
                    'events' => $events
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Vehicle History Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load vehicle history'
            ], 500);
        }
    }

    /**
     * Get recent events
     */
    public function getRecentEvents()
    {
        try {
            $events = $this->gpsService->getLastEvents();

            return response()->json([
                'success' => true,
                'data' => $events
            ]);
        } catch (\Exception $e) {
            Log::error('Recent Events Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load events'
            ], 500);
        }
    }
}
