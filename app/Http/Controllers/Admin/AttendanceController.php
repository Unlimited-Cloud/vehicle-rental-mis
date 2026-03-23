<?php
// app/Http/Controllers/Admin/AttendanceController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CrewProfile;
use App\Models\VehicleBooking;
use App\Models\VehicleMoment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $crews = CrewProfile::with('user')->get();
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $selectedCrewId = $request->input('crew_id');

        // Date range
        $startDate = $request->input('start_date', Carbon::parse($currentMonth . '-01')->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::parse($currentMonth . '-01')->endOfMonth()->format('Y-m-d'));

        $attendances = Attendance::with(['crew.user', 'booking', 'vehicleMoment'])
            ->when($selectedCrewId, function ($query) use ($selectedCrewId) {
                return $query->where('crew_id', $selectedCrewId);
            })
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get();

        $summary = $this->getAttendanceSummary($attendances);

        return view('layouts.admin.attendance.index', compact('crews', 'attendances', 'currentMonth', 'selectedCrewId', 'startDate', 'endDate', 'summary'));
    }

    public function fetchEvents(Request $request)
    {
        $query = Attendance::with(['crew.user']);

        if ($request->crew_id) {
            $query->where('crew_id', $request->crew_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('attendance_date', [$request->start_date, $request->end_date]);
        } elseif ($request->month) {
            $query->whereYear('attendance_date', substr($request->month, 0, 4))
                ->whereMonth('attendance_date', substr($request->month, 5, 2));
        }

        $attendances = $query->get();

        $events = [];
        $crewColors = [];

        foreach ($attendances as $attendance) {
            // Generate consistent color for each crew member
            if (!isset($crewColors[$attendance->crew_id])) {
                $colorIndex = $attendance->crew_id % 15;
                $crewColors[$attendance->crew_id] = $this->getCrewColor($colorIndex);
            }

            $statusColor = $this->getStatusColor($attendance->status);

            $events[] = [
                'id' => $attendance->id,
                'title' => $attendance->crew->user->name . ' - ' . ucfirst($attendance->status),
                'start' => $attendance->attendance_date,
                'end' => $attendance->attendance_date,
                'color' => $crewColors[$attendance->crew_id],
                'textColor' => '#ffffff',
                'borderColor' => $statusColor,
                'extendedProps' => [
                    'crew_id' => $attendance->crew_id,
                    'crew_name' => $attendance->crew->user->name,
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
    }

    public function convertAdToBs(Request $request)
    {
        $date = $request->date;
        $cacheKey = 'nepali_date_' . $date;

        $converted = Cache::remember($cacheKey, now()->addDays(30), function () use ($date) {
            return $this->convertToNepali($date);
        });

        return response()->json([
            'success' => true,
            'display' => $converted['display'],
            'nepali' => $converted['nepali'],
            'year' => $converted['year'],
            'month' => $converted['month'],
            'day' => $converted['day'],
        ]);
    }

    public function convertMultipleAdToBs(Request $request)
    {
        $results = [];

        foreach ($request->dates as $date) {
            $cacheKey = 'nepali_date_' . $date;

            $converted = Cache::remember($cacheKey, now()->addDays(30), function () use ($date) {
                return $this->convertToNepali($date);
            });

            $results[$date] = [
                'day' => $converted['day'],
                'month' => $converted['month'],
                'year' => $converted['year'],
                'display' => $converted['display'],
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    private function convertToNepali($adDate)
    {
        // You can implement your actual Nepali date conversion logic here
        // For now, this is a placeholder
        $date = Carbon::parse($adDate);
        $nepaliYear = $date->year + 57;
        $nepaliMonth = $date->month + 8;
        $nepaliDay = $date->day;

        $monthNames = [
            1 => 'बैशाख',
            2 => 'जेठ',
            3 => 'असार',
            4 => 'साउन',
            5 => 'भदौ',
            6 => 'असोज',
            7 => 'कात्तिक',
            8 => 'मंसिर',
            9 => 'पुस',
            10 => 'माघ',
            11 => 'फागुन',
            12 => 'चैत'
        ];

        if ($nepaliMonth > 12) {
            $nepaliMonth -= 12;
            $nepaliYear += 1;
        }

        $nepaliMonthName = $monthNames[$nepaliMonth] ?? 'बैशाख';

        $nepaliNumbers = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];
        $nepaliYearStr = implode('', array_map(function ($d) use ($nepaliNumbers) {
            return $nepaliNumbers[$d];
        }, str_split($nepaliYear)));

        $nepaliDayStr = $nepaliNumbers[$nepaliDay % 10];
        if ($nepaliDay >= 10) {
            $nepaliDayStr = $nepaliNumbers[floor($nepaliDay / 10)] . $nepaliNumbers[$nepaliDay % 10];
        }

        return [
            'nepali' => "{$nepaliYearStr} {$nepaliMonthName} {$nepaliDayStr}",
            'display' => "{$nepaliYearStr} {$nepaliMonthName} {$nepaliDayStr}",
            'year' => $nepaliYear,
            'month' => $nepaliMonth,
            'day' => $nepaliDay,
        ];
    }

    private function getCrewColor($index)
    {
        $colors = [
            '#3498db',
            '#e74c3c',
            '#2ecc71',
            '#f39c12',
            '#9b59b6',
            '#1abc9c',
            '#e67e22',
            '#34495e',
            '#16a085',
            '#27ae60',
            '#2980b9',
            '#8e44ad',
            '#2c3e50',
            '#d35400',
            '#c0392b'
        ];
        return $colors[$index % count($colors)];
    }

    private function getStatusColor($status)
    {
        return match ($status) {
            'present' => '#28a745',
            'absent' => '#dc3545',
            'half_day' => '#ffc107',
            'holiday' => '#17a2b8',
            'leave' => '#6c757d',
            default => '#6c757d',
        };
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

    public function store(Request $request)
    {
        $request->validate([
            'crew_id' => 'required|exists:crews,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:present,absent,half_day,holiday,leave',
            'salary_amount' => 'nullable|numeric',
            'bonus' => 'nullable|numeric',
            'deduction' => 'nullable|numeric',
            'remarks' => 'nullable|string',
        ]);

        $attendance = Attendance::updateOrCreate(
            [
                'crew_id' => $request->crew_id,
                'attendance_date' => $request->attendance_date,
            ],
            $request->all()
        );

        return response()->json(['success' => true, 'attendance' => $attendance]);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['crew.user', 'booking', 'vehicleMoment'])->findOrFail($id);
        return response()->json($attendance);
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->update($request->all());
        return response()->json(['success' => true, 'attendance' => $attendance]);
    }

    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();
        return response()->json(['success' => true]);
    }
}
