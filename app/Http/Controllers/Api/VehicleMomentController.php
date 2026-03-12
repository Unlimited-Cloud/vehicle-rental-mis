<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Questionnaire;
use App\Services\VehicleMomentService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\CrewProfile;
use App\Models\VehicleBooking;
use App\Services\ProformaService;
use Barryvdh\DomPDF\Facade\Pdf;

class VehicleMomentController extends Controller
{
    protected $service;
    protected $pservice;

    public function __construct(VehicleMomentService $service, ProformaService $pservice)
    {
        $this->service = $service;
        $this->pservice = $pservice;
    }

    /**
     * GET /api/vehicle-moments
     */
    public function index(Request $request)
    {
        try {

            $filters = $request->only([
                'booking_id',
                'vehicle_id',
                'from_date',
                'to_date'
            ]);

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

    /**
     * GET /api/vehicle-moments/{id}
     */
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

    /**
     * POST /api/vehicle-moments
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:vehicle_bookings,id',
            'driver_id' => 'nullable|exists:crew_profiles,id',
            'helper_id' => 'nullable|exists:crew_profiles,id',

            'vehicle_no' => 'nullable|string',
            'signage_information' => 'nullable|string',

            'start_datetime' => 'required|date',
            'start_km' => 'required|numeric',

            'end_datetime' => 'nullable|date',
            'end_km' => 'nullable|numeric',

            'start_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'end_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            'answers' => 'sometimes|array',

            'has_incident' => 'nullable|boolean',
            'incident_report' => 'nullable|string'
        ]);

        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {

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



    /**
     * PUT /api/vehicle-moments/{id}
     */
    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [

            'start_datetime' => 'nullable|date',
            'start_km' => 'nullable|numeric',

            'end_datetime' => 'nullable|date',
            'end_km' => 'nullable|numeric',

            'start_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'end_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            'answers' => 'sometimes|array',

            'has_incident' => 'nullable|boolean',
            'incident_report' => 'nullable|string'
        ]);

        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            $vehicleMoment = $this->service->update($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Vehicle moment updated successfully',
                'data' => $this->service->getWithRelations($vehicleMoment->id)
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to update vehicle moment'
            ], 500);
        }
    }

    /**
     * DELETE /api/vehicle-moments/{id}
     */
    public function destroy($id)
    {
        try {

            $vehicleMoment = $this->service->getWithRelations($id);
            $vehicleMoment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Vehicle moment deleted successfully'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete vehicle moment'
            ], 500);
        }
    }



    public function getHelpers()
    {
        $helpers = DB::table('crew_profiles')
            ->join('users', 'crew_profiles.user_id', '=', 'users.id')
            ->where('crew_profiles.role', 'helper')
            ->select(
                'crew_profiles.id as crew_profile_id',
                'crew_profiles.role',
                'users.id as user_id',
                'users.name',
                'users.email'
            )
            ->get();

        return response()->json([
            'success' => true,
            'data' => $helpers
        ]);
    }

    public function getDriverBookings($driverId)
    {
        $bookings = VehicleBooking::where('driver_id', $driverId)
            ->with([
                'vehicle',
                'driver.user',
                'helper.user',
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    public function getAllQuestionnaire()
    {
        $questionnaires = Questionnaire::where('is_active', '1')->get();

        return response()->json([
            'success' => true,
            'data' => $questionnaires
        ]);
    }

    public function getQuestionnaire($id)
    {
        $questionnaire = Questionnaire::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $questionnaire
        ]);
    }



    public function generateFromBooking(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:vehicle_bookings,id'
        ]);

        $booking = VehicleBooking::with([
            'vehicle',
            'customer'
        ])->findOrFail($request->booking_id);

        $invoice = $this->pservice->createProforma($booking);

        $pdf = Pdf::loadView(
            'layouts.admin.invoices.proforma_pdf',
            compact('invoice')
        );

        return $pdf->download($invoice->invoice_number . '.pdf');
    }
}
