<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\VehicleMomentService;
use Illuminate\Support\Facades\Validator;

class VehicleMomentController extends Controller
{
    protected $service;

    public function __construct(VehicleMomentService $service)
    {
        $this->service = $service;
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'booking_id' => 'required|exists:vehicle_bookings,id',
                'vehicle_id' => 'required|exists:vehicles,id',
                'start_datetime' => 'required|date',
                'start_km' => 'required|numeric',
                'end_datetime' => 'required|date',
                'end_km' => 'required|numeric',
                'answers' => 'sometimes|array',
                'start_image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
                'end_image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $vehicleMoment = $this->service->store($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Vehicle moment created successfully',
                'data' => $this->service->getWithRelations($vehicleMoment->id)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create vehicle moment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $vehicleMoment = $this->service->getWithRelations($id);

            return response()->json([
                'success' => true,
                'data' => $vehicleMoment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle moment not found'
            ], 404);
        }
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['booking_id', 'vehicle_id', 'from_date', 'to_date']);
            $vehicleMoments = $this->service->getAllWithFilters($filters);

            return response()->json([
                'success' => true,
                'data' => $vehicleMoments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch vehicle moments'
            ], 500);
        }
    }
}
