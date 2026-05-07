<?php
// app/Http/Controllers/Admin/AttendanceController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CrewProfile;
use App\Models\VehicleBooking;
use App\Models\VehicleMoment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;
use App\Helpers\AttendanceNepaliHelper; // Use the new helper
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    private $currentUserId;
    private $currentUserIsCustomer;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->currentUserId = Auth::user()->id;
            $this->currentUserIsCustomer = !empty(Auth::user()->customer_id) ? 'Y' : 'N';
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        Gate::authorize('index_attendance');

        $crews = CrewProfile::with('user')->orderBy('id')->get();
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $selectedCrewId = $request->input('crew_id');

        $startDate = $request->input('start_date', Carbon::parse($currentMonth . '-01')->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::parse($currentMonth . '-01')->endOfMonth()->format('Y-m-d'));

        $attendances = Attendance::with(['crew.user', 'booking', 'vehicleMoment'])
            ->when($selectedCrewId, function ($query) use ($selectedCrewId) {
                return $query->where('crew_id', $selectedCrewId);
            })
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->start_date, function ($query) use ($request) {
                return $query->whereDate('attendance_date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($query) use ($request) {
                return $query->whereDate('attendance_date', '<=', $request->end_date);
            })
            ->orderBy('attendance_date', 'desc')
            ->get();


        $summary = $this->getAttendanceSummary($attendances);

        return view('layouts.admin.attendance.index', compact('crews', 'attendances', 'currentMonth', 'selectedCrewId', 'startDate', 'endDate', 'summary'));
    }

    public function fetchEvents(Request $request)
    {
        try {
            $query = Attendance::with(['crew.user', 'booking', 'vehicleMoment']);

            if ($request->crew_id) {
                $query->where('crew_id', $request->crew_id);
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->start_date && $request->end_date) {
                $query->whereBetween('attendance_date', [$request->start_date, $request->end_date]);
            } elseif ($request->month) {
                $year = substr($request->month, 0, 4);
                $month = substr($request->month, 5, 2);
                $query->whereYear('attendance_date', $year)
                    ->whereMonth('attendance_date', $month);
            }

            $attendances = $query->get();

            $events = [];

            // Generate colors for crews
            $crewColors = [];
            $crews = CrewProfile::with('user')->get();
            foreach ($crews as $crew) {
                $crewColors[$crew->id] = '#' . substr(md5($crew->id), 0, 6);
            }

            $statusColors = [
                'present' => '#28a745',
                'absent' => '#dc3545',
                'half_day' => '#ffc107',
                'holiday' => '#17a2b8',
                'leave' => '#6c757d'
            ];

            foreach ($attendances as $attendance) {
                $statusColor = $statusColors[$attendance->status] ?? '#6c757d';

                // Make sure we have the crew name
                $crewName = $attendance->crew && $attendance->crew->user
                    ? $attendance->crew->user->name
                    : 'Unknown';

                // Convert date to string format YYYY-MM-DD
                $dateString = date('Y-m-d', strtotime($attendance->attendance_date));

                $events[] = [
                    'id' => $attendance->id,
                    'crew_id' => (int)$attendance->crew_id,
                    'crew_name' => $crewName,
                    'status' => $attendance->status,
                    'start' => $dateString,
                    'end' => $dateString,
                    'color' => $crewColors[$attendance->crew_id] ?? '#3498db',
                    'textColor' => '#ffffff',
                    'borderColor' => $statusColor,
                    'extendedProps' => [
                        'crew_id' => (int)$attendance->crew_id,
                        'crew_name' => $crewName,
                        'status' => $attendance->status,
                        'salary_amount' => $attendance->salary_amount,
                        'bonus' => $attendance->bonus,
                        'deduction' => $attendance->deduction,
                        'net_amount' => $attendance->net_amount,
                        'remarks' => $attendance->remarks,
                        'booking_id' => $attendance->booking_id,
                        'vehicle_moment_id' => $attendance->vehicle_moment_id,
                    ]
                ];
            }

            return response()->json($events);
        } catch (\Exception $e) {
            \Log::error('Attendance fetchEvents error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function create(Request $request)
    {
        Gate::authorize('create_attendance');
        $crews = CrewProfile::with('user')->get();
        $bookings = VehicleBooking::with('vehicle', 'customer')->orderBy('created_at', 'desc')->limit(100)->get();
        $vehicleMoments = VehicleMoment::with('vehicle')->orderBy('created_at', 'desc')->limit(100)->get();

        $selectedDate = $request->date ?? Carbon::now()->format('Y-m-d');
        $selectedCrewId = $request->crew_id;

        return view('layouts.admin.attendance.create', compact('crews', 'bookings', 'vehicleMoments', 'selectedDate', 'selectedCrewId'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create_attendance');

        $request->validate([
            'crew_id' => 'required|exists:crew_profiles,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:present,absent,half_day,holiday,leave',
            'salary_amount' => 'nullable|numeric',
            'bonus' => 'nullable|numeric',
            'deduction' => 'nullable|numeric',
            'remarks' => 'nullable|string',
            'booking_id' => 'nullable|exists:vehicle_bookings,id',
            'vehicle_moment_id' => 'nullable|exists:vehicle_moments,id',
        ]);

        $salaryAmount = $request->salary_amount ?? 0;
        $bonus = $request->bonus ?? 0;
        $deduction = $request->deduction ?? 0;
        $netAmount = $salaryAmount + $bonus - $deduction;

        $attendance = Attendance::updateOrCreate(
            [
                'crew_id' => $request->crew_id,
                'attendance_date' => $request->attendance_date,
            ],
            [
                'status' => $request->status,
                'salary_amount' => $salaryAmount,
                'bonus' => $bonus,
                'deduction' => $deduction,
                'net_amount' => $netAmount,
                'remarks' => $request->remarks,
                'booking_id' => $request->booking_id,
                'vehicle_moment_id' => $request->vehicle_moment_id,
            ]
        );

        if ($request->ajax()) {
            return response()->json(['success' => true, 'attendance' => $attendance]);
        }

        return redirect()->route('admin.attendance.index')
            ->with('success', 'Attendance marked successfully.');
    }

    public function show($id)
    {
        Gate::authorize('read_attendance');
        $attendance = Attendance::with(['crew.user', 'booking', 'vehicleMoment'])->findOrFail($id);

        if (request()->ajax()) {
            // Use the AttendanceNepaliHelper
            $nepaliDate = AttendanceNepaliHelper::convertToNepali($attendance->attendance_date);

            // Prepare vehicle moment data safely without accessing non-existent methods
            $vehicleMomentData = null;
            if ($attendance->vehicleMoment) {
                $vehicleMomentData = [
                    'id' => $attendance->vehicleMoment->id,
                    'moment_date' => $attendance->vehicleMoment->moment_date ?? null,
                    'moment_type' => $attendance->vehicleMoment->moment_type ?? null,
                    'vehicle_id' => $attendance->vehicleMoment->vehicle_id ?? null,
                    'vehicle_name' => $attendance->vehicleMoment->vehicle->vehicle_name ?? null,
                    'description' => $attendance->vehicleMoment->description ?? null,
                ];
            }

            // Prepare booking data safely
            $bookingData = null;
            if ($attendance->booking) {
                $bookingData = [
                    'id' => $attendance->booking->id,
                    'booking_number' => $attendance->booking->booking_number ?? null,
                    'vehicle_name' => $attendance->booking->vehicle->vehicle_name ?? null,
                    'customer_name' => $attendance->booking->customer->name ?? null,
                    'start_date' => $attendance->booking->start_date ?? null,
                    'end_date' => $attendance->booking->end_date ?? null,
                ];
            }

            return response()->json([
                'id' => $attendance->id,
                'crew_id' => $attendance->crew_id,
                'crew' => [
                    'id' => $attendance->crew->id ?? null,
                    'user' => [
                        'name' => $attendance->crew->user->name ?? null,
                        'email' => $attendance->crew->user->email ?? null,
                        'phone' => $attendance->crew->user->phone ?? null,
                    ]
                ],
                'attendance_date' => $attendance->attendance_date,
                'nepali_date' => $nepaliDate['display'] ?? $attendance->attendance_date,
                'status' => $attendance->status,
                'salary_amount' => $attendance->salary_amount,
                'bonus' => $attendance->bonus,
                'deduction' => $attendance->deduction,
                'allowances' => $attendance->allowances,
                'net_amount' => $attendance->net_amount,
                'remarks' => $attendance->remarks,
                'booking_id' => $attendance->booking_id,
                'booking' => $bookingData,
                'vehicle_moment_id' => $attendance->vehicle_moment_id,
                'vehicle_moment' => $vehicleMomentData,
            ]);
        }

        return view('admin.attendance.show', compact('attendance'));
    }

    public function edit($id)
    {
        Gate::authorize('update_attendance');
        $attendance = Attendance::with(['crew.user', 'booking', 'vehicleMoment'])->findOrFail($id);
        $crews = CrewProfile::with('user')->get();
        $bookings = VehicleBooking::with('vehicle', 'customer')->orderBy('created_at', 'desc')->limit(100)->get();
        $vehicleMoments = VehicleMoment::with('vehicle')->orderBy('created_at', 'desc')->limit(100)->get();

        return view('layouts.admin.attendance.create', compact('attendance', 'crews', 'bookings', 'vehicleMoments'));
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('update_attendance');

        $request->validate([
            'crew_id' => 'required|exists:crew_profiles,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:present,absent,half_day,holiday,leave',
            'salary_amount' => 'nullable|numeric',
            'bonus' => 'nullable|numeric',
            'deduction' => 'nullable|numeric',
            'remarks' => 'nullable|string',
            'booking_id' => 'nullable|exists:vehicle_bookings,id',
            'vehicle_moment_id' => 'nullable|exists:vehicle_moments,id',
        ]);

        $attendance = Attendance::findOrFail($id);

        $salaryAmount = $request->salary_amount ?? 0;
        $bonus = $request->bonus ?? 0;
        $deduction = $request->deduction ?? 0;
        $netAmount = $salaryAmount + $bonus - $deduction;

        $attendance->update([
            'crew_id' => $request->crew_id,
            'attendance_date' => $request->attendance_date,
            'status' => $request->status,
            'salary_amount' => $salaryAmount,
            'bonus' => $bonus,
            'deduction' => $deduction,
            'net_amount' => $netAmount,
            'remarks' => $request->remarks,
            'booking_id' => $request->booking_id,
            'vehicle_moment_id' => $request->vehicle_moment_id,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'attendance' => $attendance]);
        }

        return redirect()->route('admin.attendance.index')
            ->with('success', 'Attendance updated successfully.');
    }

    public function destroy($id)
    {
        Gate::authorize('delete_attendance');
        try {
            $attendance = Attendance::findOrFail($id);
            $attendance->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Attendance deleted successfully.'
                ]);
            }

            return back()->with('success', 'Attendance deleted successfully.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting attendance: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error deleting attendance.');
        }
    }

    public function export(Request $request)
    {
        Gate::authorize('export_attendance');
        $fileName = 'attendance_' . date('Y-m-d_His') . '.xlsx';
        // return Excel::download(new AttendanceExport($request), $fileName);
    }

    public function convertAdToBs(Request $request)
    {
        try {
            $date = $request->date;

            // Use the new AttendanceNepaliHelper
            $converted = AttendanceNepaliHelper::convertToNepali($date);

            return response()->json([
                'success' => true,
                'display' => $converted['display'],
                'nepali' => $converted['nepali'],
                'year' => $converted['year'],
                'month' => $converted['month'],
                'day' => $converted['day'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'display' => $request->date,
                'nepali' => $request->date,
                'year' => '',
                'month' => '',
                'day' => '',
            ]);
        }
    }

    public function convertMultipleAdToBs(Request $request)
    {
        try {
            // Use the new AttendanceNepaliHelper
            $results = AttendanceNepaliHelper::convertMultiple($request->dates);

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ]);
        }
    }

    private function getAttendanceSummary($attendances)
    {
        return [
            'total_present' => $attendances->where('status', 'present')->count(),
            'total_absent' => $attendances->where('status', 'absent')->count(),
            'total_half_day' => $attendances->where('status', 'half_day')->count(),
            'total_holiday' => $attendances->where('status', 'holiday')->count(),
            'total_leave' => $attendances->where('status', 'leave')->count(),
            'total_salary' => $attendances->sum('net_amount'),
            'total_bonus' => $attendances->sum('bonus'),
            'total_deduction' => $attendances->sum('deduction'),
            'attendance_rate' => $attendances->count() > 0
                ? round(($attendances->whereIn('status', ['present', 'half_day'])->count() / $attendances->count()) * 100, 2)
                : 0
        ];
    }

    public function createAllowance(Request $request)
    {
        Gate::authorize('create_attendance');

        $vehicleMomentId = $request->vehicle_moment_id;
        $vehicleMoment = VehicleMoment::with('vehicle', 'driver')->findOrFail($vehicleMomentId);

        // Get crew_id from vehicle_moment
        $crewId = $vehicleMoment->driver_id;

        // Check if attendance already exists for today
        $today = Carbon::now()->format('Y-m-d');
        $existingAttendance = Attendance::where('crew_id', $crewId)
            ->where('attendance_date', $today)
            ->first();

        return view('layouts.admin.attendance.allowance_form', compact('vehicleMoment', 'crewId', 'today', 'existingAttendance'));
    }

    public function storeAllowance(Request $request)
    {
        Gate::authorize('create_attendance');

        $request->validate([
            'crew_id' => 'required|exists:crew_profiles,id',
            'vehicle_moment_id' => 'required|exists:vehicle_moments,id',
            'attendance_date' => 'required|date',
            'allowances' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        $allowances = $request->allowances ?? 0;

        // Calculate net amount
        $attendance = Attendance::updateOrCreate(
            [
                'crew_id' => $request->crew_id,
                'attendance_date' => $request->attendance_date,
            ],
            [
                'vehicle_moment_id' => $request->vehicle_moment_id,
                'booking_id' => $request->booking_id,
                'status' => 'present', // Default to present
                'allowances' => $allowances,
                'remarks' => $request->remarks,
                'salary_amount' => $request->salary_amount ?? 0,
                'bonus' => $request->bonus ?? 0,
                'deduction' => $request->deduction ?? 0,
                'net_amount' => ($request->salary_amount ?? 0) + ($request->bonus ?? 0) - ($request->deduction ?? 0) + $allowances,
            ]
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'attendance' => $attendance,
                'message' => 'Allowance added successfully!'
            ]);
        }

        return redirect()->route('admin.attendance.index')
            ->with('success', 'Allowance/Bhatta added successfully.');
    }
}
