<?php

namespace App\Services;

use App\Models\BookingLog;
use App\Models\VehicleMoment;
use App\Models\VehicleQuestionnaireAnswer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class VehicleMomentService
{

    public function getAll()
    {
        return DB::table('vehicle_moments as vm')
            ->select(
                'vm.*',
                'v.vehicle_name',
                'd.name as driver_name',
                'h.name as helper_name',
                'c.name as customer_name',
                'vb.start_date',
                'vb.end_date',
            )
            ->leftJoin('vehicle_bookings as vb', 'vb.id', '=', 'vm.booking_id')
            ->leftJoin('vehicles as v', 'v.id', '=', 'vb.vehicle_id')

            ->leftJoin('crew_profiles as cp_driver', 'cp_driver.id', '=', 'vb.driver_id')
            ->leftJoin('users as d', 'd.id', '=', 'cp_driver.user_id')

            ->leftJoin('crew_profiles as cp_helper', 'cp_helper.id', '=', 'vb.helper_id')
            ->leftJoin('users as h', 'h.id', '=', 'cp_helper.user_id')

            ->leftJoin('customers as c', 'c.id', '=', 'vb.customer_id')

            ->orderBy('vm.created_at', 'desc')
            ->get();
    }
    /**
     * Store vehicle moment with questionnaire answers
     */
    public function store(array $data)
    {
        try {
            DB::beginTransaction();

            // Handle image uploads
            if (isset($data['start_image']) && $data['start_image'] instanceof \Illuminate\Http\UploadedFile) {
                $data['start_image'] = $this->uploadImage($data['start_image'], 'start');
            }

            if (isset($data['end_image']) && $data['end_image'] instanceof \Illuminate\Http\UploadedFile) {
                $data['end_image'] = $this->uploadImage($data['end_image'], 'end');
            }

            if (isset($data['incident_image']) && $data['incident_image'] instanceof \Illuminate\Http\UploadedFile) {
                $data['incident_image'] = $this->uploadImage($data['incident_image'], 'incident');
            }

            // Create vehicle moment
            // Create vehicle moment
            $vehicleMoment = VehicleMoment::create($data);

            //  NEW: Sync with booking if changed
            if (!empty($data['booking_id'])) {

                $booking = DB::table('vehicle_bookings')
                    ->where('id', $data['booking_id'])
                    ->first();

                if ($booking) {

                    $updateData = [];

                    // Check trip_category_id
                    if (
                        isset($data['trip_category_id']) &&
                        $data['trip_category_id'] != $booking->trip_category_id
                    ) {

                        $updateData['trip_category_id'] = $data['trip_category_id'];
                    }

                    // Check trip_route_id
                    if (
                        isset($data['trip_route_id']) &&
                        $data['trip_route_id'] != $booking->trip_route_id
                    ) {

                        $updateData['trip_route_id'] = $data['trip_route_id'];
                    }


                    // Only update if something changed
                    if (!empty($updateData)) {
                        DB::table('vehicle_bookings')
                            ->where('id', $booking->id)
                            ->update($updateData);
                    }
                }


                if (
                    !empty($vehicleMoment->booking_id) &&
                    !empty($vehicleMoment->start_datetime) &&
                    empty($vehicleMoment->end_datetime)
                ) {
                    DB::table('vehicle_bookings')
                        ->where('id', $vehicleMoment->booking_id)
                        ->update([
                            'status' => 'started',
                            'updated_at' => now(),
                        ]);
                }

                if (!empty($vehicleMoment->start_datetime)) {
                    BookingLog::firstOrCreate(
                        [
                            'booking_id' => $vehicleMoment->booking_id,
                            'status'     => 'started',
                        ],
                        [
                            'remarks' => 'Trip started',
                            'created_by' => Auth::user() ? Auth::user()->id : 0,
                        ]
                    );
                }

                if (
                    !empty($vehicleMoment->booking_id) &&
                    !empty($vehicleMoment->end_datetime)
                ) {
                    DB::table('vehicle_bookings')
                        ->where('id', $vehicleMoment->booking_id)
                        ->update([
                            'status' => 'completed',
                            'updated_at' => now(),
                        ]);
                }


                // Completed Log
                if (!empty($vehicleMoment->end_datetime)) {
                    BookingLog::firstOrCreate(
                        [
                            'booking_id' => $vehicleMoment->booking_id,
                            'status'     => 'completed',
                        ],
                        [
                            'remarks' => 'Trip completed',
                            'created_by' => Auth::user() ? Auth::user()->id : 0,
                        ]
                    );
                }
            }





            // $this->storeAttendance($vehicleMoment, $data);

            // Save questionnaire answers if present
            if (isset($data['answers']) && is_array($data['answers'])) {
                $this->saveQuestionnaireAnswers($vehicleMoment->id, $data['answers']);
            }

            DB::commit();

            return $vehicleMoment;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create vehicle moment: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update vehicle moment with questionnaire answers
     */
    public function update($id, array $data)
    {
        try {
            DB::beginTransaction();

            $vehicleMoment = VehicleMoment::findOrFail($id);



            // Handle image uploads
            if (isset($data['start_image']) && $data['start_image'] instanceof \Illuminate\Http\UploadedFile) {
                // Delete old image if exists
                if ($vehicleMoment->start_image) {
                    $this->deleteImage($vehicleMoment->start_image);
                }
                $data['start_image'] = $this->uploadImage($data['start_image'], 'start');
            }

            if (isset($data['end_image']) && $data['end_image'] instanceof \Illuminate\Http\UploadedFile) {
                // Delete old image if exists
                if ($vehicleMoment->end_image) {
                    $this->deleteImage($vehicleMoment->end_image);
                }
                $data['end_image'] = $this->uploadImage($data['end_image'], 'end');
            }

            if (isset($data['incident_image']) && $data['incident_image'] instanceof \Illuminate\Http\UploadedFile) {

                if ($vehicleMoment->incident_image) {
                    $this->deleteImage($vehicleMoment->incident_image);
                }

                $data['incident_image'] = $this->uploadImage($data['incident_image'], 'incident');
            }



            // Update vehicle moment
            $vehicleMoment->update($data);


            if (
                !empty($vehicleMoment->booking_id) &&
                !empty($vehicleMoment->end_datetime)
            ) {
                DB::table('vehicle_bookings')
                    ->where('id', $vehicleMoment->booking_id)
                    ->update([
                        'status' => 'completed',
                        'updated_at' => now(),
                    ]);
            }

            $vehicleMoment->refresh();
            if (!empty($vehicleMoment->booking_id)) {

                // Started Log
                if (!empty($vehicleMoment->start_datetime)) {
                    BookingLog::firstOrCreate(
                        [
                            'booking_id' => $vehicleMoment->booking_id,
                            'status'     => 'started',
                        ],
                        [
                            'remarks' => 'Trip started',
                            'created_by' => Auth::user() ? Auth::user()->id : 0,
                        ]
                    );
                }

                // Completed Log
                if (!empty($vehicleMoment->end_datetime)) {
                    BookingLog::firstOrCreate(
                        [
                            'booking_id' => $vehicleMoment->booking_id,
                            'status'     => 'completed',
                        ],
                        [
                            'remarks' => 'Trip completed',
                            'created_by' => Auth::user() ? Auth::user()->id : 0,
                        ]
                    );
                }
            }

            // DB::table('attendance')
            //     ->where('vehicle_moment_id', $id)
            //     ->delete();
            // $this->storeAttendance($vehicleMoment, $data);

            // Update questionnaire answers if present
            if (isset($data['answers']) && is_array($data['answers'])) {
                // Delete existing answers
                VehicleQuestionnaireAnswer::where('vehicle_moment_id', $vehicleMoment->id)->delete();
                // Save new answers
                $this->saveQuestionnaireAnswers($vehicleMoment->id, $data['answers']);
            }

            DB::commit();

            return $vehicleMoment;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update vehicle moment: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Save questionnaire answers
     */
    private function saveQuestionnaireAnswers($vehicleMomentId, array $answers)
    {
        $questionnaireAnswers = [];

        foreach ($answers as $questionnaireId => $answer) {
            // Handle array answers (like checkboxes)
            if (is_array($answer)) {
                $answer = json_encode($answer);
            }

            // Handle empty answers
            if ($answer === null || $answer === '') {
                continue;
            }

            $questionnaireAnswers[] = [
                'vehicle_moment_id' => $vehicleMomentId,
                'questionnaire_id' => $questionnaireId,
                'answer' => $answer,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if (!empty($questionnaireAnswers)) {
            VehicleQuestionnaireAnswer::insert($questionnaireAnswers);
        }
    }

    /**
     * Upload image to storage
     */
    private function uploadImage($file, $type)
    {
        $path = 'uploads/moments/';
        $name = time() . '_' . $type . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Create directory if it doesn't exist
        if (!file_exists(public_path($path))) {
            mkdir(public_path($path), 0777, true);
        }

        $file->move(public_path($path), $name);

        return $path . $name;
    }

    /**
     * Delete image from storage
     */
    private function deleteImage($imagePath)
    {
        if ($imagePath && file_exists(public_path($imagePath))) {
            unlink(public_path($imagePath));
        }
    }

    /**
     * Get vehicle moment with relations for API
     */
    public function getWithRelations($id)
    {
        return VehicleMoment::with(['booking', 'questionnaireAnswers.questionnaire'])
            ->findOrFail($id);
    }

    /**
     * Get all vehicle moments with filters for API
     */
    public function getAllWithFilters($filters = [])
    {
        $query = VehicleMoment::with(['booking.vehicle', 'booking.driver', 'booking.helper']);

        if (isset($filters['booking_id'])) {
            $query->where('booking_id', $filters['booking_id']);
        }

        if (isset($filters['vehicle_id'])) {
            $query->where('vehicle_id', $filters['vehicle_id']);
        }

        if (isset($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function storeAttendance($moment, $data)
    {
        if (empty($data['start_datetime'])) {
            return;
        }

        $start = \Carbon\Carbon::parse($data['start_datetime'])->startOfDay();
        $end = !empty($data['end_datetime'])
            ? \Carbon\Carbon::parse($data['end_datetime'])->startOfDay()
            : $start;

        $crewIds = [];

        if (!empty($data['driver_id'])) {
            $crewIds[] = $data['driver_id'];
        }

        if (!empty($data['helper_id'])) {
            $crewIds[] = $data['helper_id'];
        }

        $datesToInsert = [];

        foreach ($crewIds as $crewId) {

            $currentDate = $start->copy();

            while ($currentDate->lte($end)) {

                // Check if already exists
                $exists = DB::table('attendance')
                    ->where('vehicle_moment_id', $moment->id)
                    ->where('crew_id', $crewId)
                    ->where('attendance_date', $currentDate->toDateString())
                    ->exists();

                if (!$exists) {
                    $datesToInsert[] = [
                        'crew_id' => $crewId,
                        'vehicle_moment_id' => $moment->id,
                        'booking_id' => $data['booking_id'] ?? null,
                        'attendance_date' => $currentDate->toDateString(),
                        'salary_amount' => 0,
                        'status' => 'present',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                $currentDate->addDay();
            }
        }

        if (!empty($datesToInsert)) {
            DB::table('attendance')->insert($datesToInsert);
        }
    }
}
