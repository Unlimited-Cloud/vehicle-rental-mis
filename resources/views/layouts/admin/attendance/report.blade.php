{{-- resources/views/admin/attendance/report.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Attendance Report</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filter Form -->
        <div class="card card-primary card-outline mb-3">
            <div class="card-header">
                <h5 class="mb-0">Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row">
                    <div class="col-md-3">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-3">
                        <label>Crew Member</label>
                        <select name="crew_id" class="form-control">
                            <option value="">All Crew Members</option>
                            @foreach($crews as $crew)
                                <option value="{{ $crew->id }}" {{ $crewId == $crew->id ? 'selected' : '' }}>
                                    {{ $crew->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-search"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Summary Cards -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $summary['total_present'] }}</h3>
                        <p>Present Days</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $summary['total_absent'] }}</h3>
                        <p>Absent Days</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-times-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $summary['attendance_rate'] }}%</h3>
                        <p>Attendance Rate</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-chart-line"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>Rs. {{ number_format($summary['total_salary'], 2) }}</h3>
                        <p>Total Salary</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-money"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Report Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Attendance Details</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped" id="reportTable">
                    <thead>
                         <tr>
                            <th>#</th>
                            <th>Crew Member</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Salary Amount</th>
                            <th>Bonus</th>
                            <th>Deduction</th>
                            <th>Net Amount</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $attendance->crew->user->name }}</td>
                                <td>
                                    {{ $attendance->attendance_date->format('M d, Y') }}
                                    <br>
                                    <small class="text-muted">{{ $attendance->attendance_date->format('l') }}</small>
                                </td>
                                <td>{!! $attendance->status_badge !!}</td>
                                <td>Rs. {{ number_format($attendance->salary_amount, 2) }}</td>
                                <td>Rs. {{ number_format($attendance->bonus, 2) }}</td>
                                <td>Rs. {{ number_format($attendance->deduction, 2) }}</td>
                                <td><strong>Rs. {{ number_format($attendance->net_amount, 2) }}</strong></td>
                                <td>{{ $attendance->remarks ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No attendance records found</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-light">
                            <th colspan="4" class="text-right">Total:</th>
                            <th>Rs. {{ number_format($attendances->sum('salary_amount'), 2) }}</th>
                            <th>Rs. {{ number_format($attendances->sum('bonus'), 2) }}</th>
                            <th>Rs. {{ number_format($attendances->sum('deduction'), 2) }}</th>
                            <th>Rs. {{ number_format($attendances->sum('net_amount'), 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <!-- Chart Section -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Attendance Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="attendanceChart" style="height: 300px; width: 100%;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Daily Attendance Trend</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="dailyChart" style="height: 300px; width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#reportTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "pageLength": 25
    });
    
    // Prepare data for charts
    @php
        // Prepare distribution data
        $distributionData = [
            $summary['total_present'],
            $summary['total_absent'],
            $summary['total_half_day'],
            $summary['total_holiday'],
            $summary['total_leave']
        ];
        
        // Prepare daily data
        $dailyData = [];
        foreach($attendances as $attendance) {
            $date = $attendance->attendance_date->format('Y-m-d');
            if (!isset($dailyData[$date])) {
                $dailyData[$date] = [
                    'present' => 0,
                    'absent' => 0,
                    'half_day' => 0,
                    'holiday' => 0,
                    'leave' => 0
                ];
            }
            $dailyData[$date][$attendance->status]++;
        }
        
        // Sort daily data by date
        ksort($dailyData);
    @endphp
    
    // Attendance Distribution Chart (Doughnut)
    const ctx1 = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: ['Present', 'Absent', 'Half Day', 'Holiday', 'Leave'],
            datasets: [{
                data: @json($distributionData),
                backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6c757d'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw;
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} days (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    
    // Daily Attendance Trend Chart (Line)
    const dailyLabels = @json(array_keys($dailyData));
    const presentData = @json(array_column($dailyData, 'present'));
    const absentData = @json(array_column($dailyData, 'absent'));
    const halfDayData = @json(array_column($dailyData, 'half_day'));
    const holidayData = @json(array_column($dailyData, 'holiday'));
    const leaveData = @json(array_column($dailyData, 'leave'));
    
    const ctx2 = document.getElementById('dailyChart').getContext('2d');
    
    if (dailyLabels.length > 0) {
        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [
                    {
                        label: 'Present',
                        data: presentData,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Absent',
                        data: absentData,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Half Day',
                        data: halfDayData,
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Holiday',
                        data: holidayData,
                        borderColor: '#17a2b8',
                        backgroundColor: 'rgba(23, 162, 184, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Leave',
                        data: leaveData,
                        borderColor: '#6c757d',
                        backgroundColor: 'rgba(108, 117, 125, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                let value = context.raw;
                                return `${label}: ${value} crew member(s)`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { 
                            stepSize: 1,
                            precision: 0
                        },
                        title: {
                            display: true,
                            text: 'Number of Crew Members',
                            font: {
                                weight: 'bold'
                            }
                        },
                        grid: {
                            drawBorder: true,
                            drawOnChartArea: true
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date',
                            font: {
                                weight: 'bold'
                            }
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45,
                            autoSkip: true,
                            maxTicksLimit: 15
                        }
                    }
                }
            }
        });
    } else {
        ctx2.font = "16px Arial";
        ctx2.fillStyle = "#999";
        ctx2.fillText("No data available for chart", 50, 150);
    }
});
</script>

<style>
.small-box {
    border-radius: 0.25rem;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    display: block;
    margin-bottom: 20px;
    position: relative;
}
.small-box .inner {
    padding: 10px;
}
.small-box .icon {
    position: absolute;
    top: 5px;
    right: 10px;
    font-size: 70px;
    opacity: 0.3;
}
.small-box h3 {
    font-size: 2rem;
    font-weight: bold;
    margin: 0 0 10px 0;
    white-space: nowrap;
    padding: 0;
}
.small-box p {
    font-size: 1rem;
}
.small-box .inner h3, 
.small-box .inner p {
    color: white;
}
.bg-info {
    background-color: #17a2b8 !important;
}
.bg-danger {
    background-color: #dc3545 !important;
}
.bg-success {
    background-color: #28a745 !important;
}
.bg-warning {
    background-color: #ffc107 !important;
}
.bg-primary {
    background-color: #007bff !important;
}
</style>
@endsection