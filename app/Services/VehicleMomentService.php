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
            if (isset($data['depot_departure_image']) && $data['depot_departure_image'] instanceof \Illuminate\Http\UploadedFile) {
                $data['depot_departure_image'] = $this->uploadImage($data['depot_departure_image'], 'depot_departure');
            }

            if (isset($data['pickup_arrival_image']) && $data['pickup_arrival_image'] instanceof \Illuminate\Http\UploadedFile) {
                $data['pickup_arrival_image'] = $this->uploadImage($data['pickup_arrival_image'], 'pickup_arrival');
            }

            if (isset($data['dropoff_image']) && $data['dropoff_image'] instanceof \Illuminate\Http\UploadedFile) {
                $data['dropoff_image'] = $this->uploadImage($data['dropoff_image'], 'dropoff');
            }

            if (isset($data['incident_image']) && $data['incident_image'] instanceof \Illuminate\Http\UploadedFile) {
                $data['incident_image'] = $this->uploadImage($data['incident_image'], 'incident');
            }

            // Create vehicle moment
            $vehicleMoment = VehicleMoment::create($data);

            // Sync booking status based on trip progress
            if (!empty($vehicleMoment->booking_id)) {
                if (!empty($vehicleMoment->depot_departure_datetime) && empty($vehicleMoment->dropoff_datetime)) {
                    DB::table('vehicle_bookings')
                        ->where('id', $vehicleMoment->booking_id)
                        ->update([
                            'status' => 'started',
                            'updated_at' => now(),
                        ]);
                }

                if (!empty($vehicleMoment->dropoff_datetime)) {
                    DB::table('vehicle_bookings')
                        ->where('id', $vehicleMoment->booking_id)
                        ->update([
                            'status' => 'completed',
                            'updated_at' => now(),
                        ]);
                }

                // Create booking logs
                if (!empty($vehicleMoment->depot_departure_datetime)) {
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

                if (!empty($vehicleMoment->dropoff_datetime)) {
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

            // Store allowances in attendance table
            $this->storeAllowances($vehicleMoment, $data);

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
            if (isset($data['depot_departure_image']) && $data['depot_departure_image'] instanceof \Illuminate\Http\UploadedFile) {
                if ($vehicleMoment->depot_departure_image) {
                    $this->deleteImage($vehicleMoment->depot_departure_image);
                }
                $data['depot_departure_image'] = $this->uploadImage($data['depot_departure_image'], 'depot_departure');
            }

            if (isset($data['pickup_arrival_image']) && $data['pickup_arrival_image'] instanceof \Illuminate\Http\UploadedFile) {
                if ($vehicleMoment->pickup_arrival_image) {
                    $this->deleteImage($vehicleMoment->pickup_arrival_image);
                }
                $data['pickup_arrival_image'] = $this->uploadImage($data['pickup_arrival_image'], 'pickup_arrival');
            }

            if (isset($data['dropoff_image']) && $data['dropoff_image'] instanceof \Illuminate\Http\UploadedFile) {
                if ($vehicleMoment->dropoff_image) {
                    $this->deleteImage($vehicleMoment->dropoff_image);
                }
                $data['dropoff_image'] = $this->uploadImage($data['dropoff_image'], 'dropoff');
            }

            if (isset($data['incident_image']) && $data['incident_image'] instanceof \Illuminate\Http\UploadedFile) {
                if ($vehicleMoment->incident_image) {
                    $this->deleteImage($vehicleMoment->incident_image);
                }
                $data['incident_image'] = $this->uploadImage($data['incident_image'], 'incident');
            }

            // Update vehicle moment
            $vehicleMoment->update($data);
            $vehicleMoment->refresh();

            // Sync booking status based on trip progress
            if (!empty($vehicleMoment->booking_id)) {
                if (!empty($vehicleMoment->depot_departure_datetime) && empty($vehicleMoment->dropoff_datetime)) {
                    DB::table('vehicle_bookings')
                        ->where('id', $vehicleMoment->booking_id)
                        ->update([
                            'status' => 'started',
                            'updated_at' => now(),
                        ]);
                }

                if (!empty($vehicleMoment->dropoff_datetime)) {
                    DB::table('vehicle_bookings')
                        ->where('id', $vehicleMoment->booking_id)
                        ->update([
                            'status' => 'completed',
                            'updated_at' => now(),
                        ]);
                }

                if (!empty($vehicleMoment->depot_departure_datetime)) {
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

                if (!empty($vehicleMoment->dropoff_datetime)) {
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

            // Delete existing attendance records and recreate
            DB::table('attendance')
                ->where('vehicle_moment_id', $id)
                ->delete();

            $this->storeAllowances($vehicleMoment, $data);

            // Update questionnaire answers if present
            if (isset($data['answers']) && is_array($data['answers'])) {
                VehicleQuestionnaireAnswer::where('vehicle_moment_id', $vehicleMoment->id)->delete();
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
     * Store allowances in attendance table
     */
    private function storeAllowances($moment, array $data)
    {
        // Get attendance date
        $attendanceDate = $data['attendance_date'] ?? now()->format('Y-m-d');

        // If start_datetime is available, use it as attendance date
        // if (!empty($moment->start_datetime)) {
        //     $attendanceDate = \Carbon\Carbon::parse($moment->start_datetime)->format('Y-m-d');
        // }

        $attendanceRecords = [];

        // Store driver allowance
        $driverAllowance = $data['driver_allowance'] ?? 0;
        if ($driverAllowance > 0 || !empty($data['driver_id'])) {
            $driverAttendance = DB::table('attendance')->insert(
                [
                    'crew_id' => $data['driver_id'],
                    'attendance_date' => $attendanceDate,
                    'vehicle_moment_id' => $moment->id,
                    'booking_id' => $moment->booking_id,
                    'status' => 'present',
                    'allowances' => $driverAllowance,
                    'remarks' => $data['driver_remarks'] ?? ' (Driver)',
                    // 'salary_amount' => $data['driver_salary_amount'] ?? 0,
                    // 'bonus' => $data['driver_bonus'] ?? 0,
                    // 'deduction' => $data['driver_deduction'] ?? 0,
                    // 'net_amount' => ($data['driver_salary_amount'] ?? 0) + ($data['driver_bonus'] ?? 0) - ($data['driver_deduction'] ?? 0) + $driverAllowance,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $attendanceRecords[] = $driverAttendance;
        }

        // Store helper allowance
        $helperAllowance = $data['helper_allowance'] ?? 0;
        if (!empty($data['helper_id']) && ($helperAllowance > 0 || !empty($data['helper_id']))) {
            $helperAttendance = DB::table('attendance')->insert(
                [
                    'crew_id' => $data['helper_id'],
                    'attendance_date' => $attendanceDate,
                    // 'helper_id' => null,
                    'vehicle_moment_id' => $moment->id,
                    'booking_id' => $moment->booking_id,
                    'status' => 'present',
                    'allowances' => $helperAllowance,
                    'remarks' => $data['helper_remarks'] ?? ' (Helper)',
                    // 'salary_amount' => $data['helper_salary_amount'] ?? 0,
                    // 'bonus' => $data['helper_bonus'] ?? 0,
                    // 'deduction' => $data['helper_deduction'] ?? 0,
                    // 'net_amount' => ($data['helper_salary_amount'] ?? 0) + ($data['helper_bonus'] ?? 0) - ($data['helper_deduction'] ?? 0) + $helperAllowance,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $attendanceRecords[] = $helperAttendance;
        }

        return $attendanceRecords;
    }

    /**
     * Save questionnaire answers
     */
    private function saveQuestionnaireAnswers($vehicleMomentId, array $answers)
    {
        $questionnaireAnswers = [];

        foreach ($answers as $questionnaireId => $answer) {
            if (is_array($answer)) {
                $answer = json_encode($answer);
            }

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
}
