{{-- resources/views/admin/attendance/index.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')

@if(auth()->user()->can('manage_attendance'))
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1>Attendance Management</h1>
        <div>
            <button class="btn btn-outline-secondary btn-sm" id="listViewBtn">
                <i class="fa fa-list"></i> List View
            </button>
            <button class="btn btn-outline-success btn-sm" id="calendarViewBtn">
                <i class="fa fa-calendar"></i> Calendar View
            </button>
            <a href="#" class="btn btn-primary btn-sm" onclick="openMarkAttendanceModal()">
                <i class="fa fa-plus"></i> Mark Attendance
            </a>
            <a href="{{ route('admin.attendance.export') }}" class="btn btn-success btn-sm">
                <i class="fa fa-file-excel"></i> Export Excel
            </a>
        </div>
    </div>
</div>
@endif

<section class="content">
<div class="container-fluid">

@include('layouts.admin_theme.alert')

<div class="card card-primary card-outline">
<div class="card-body">

<!-- ================= FILTER MODAL ================= -->
<div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="filterModalLabel">
                    <i class="fa fa-filter"></i> Advanced Filters
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="filterForm" onsubmit="applyFilter(); return false;">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Crew Member</label>
                        <select id="crewFilter" class="form-control">
                            <option value="">All Crew Members</option>
                            @foreach($crews as $crew)
                                <option value="{{ $crew->id }}" {{ request('crew_id') == $crew->id ? 'selected' : '' }}>{{ $crew->user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select id="statusFilter" class="form-control">
                            <option value="">All Status</option>
                            <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                            <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                            <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                            <option value="holiday" {{ request('status') == 'holiday' ? 'selected' : '' }}>Holiday</option>
                            <option value="leave" {{ request('status') == 'leave' ? 'selected' : '' }}>Leave</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Date Range</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="date" id="startDateFilter" class="form-control" placeholder="From" value="{{ request('start_date', $startDate ?? '') }}">
                            </div>
                            <div class="col-6">
                                <input type="date" id="endDateFilter" class="form-control" placeholder="To" value="{{ request('end_date', $endDate ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Remarks</label>
                        <input type="text" id="remarksFilter" class="form-control" placeholder="Search remarks..." value="{{ request('remarks') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="clearFilter()">
                        <i class="fa fa-refresh"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-filter"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="mb-3 d-flex gap-2">
  <button class="btn btn-info btn-sm mr-2" data-toggle="modal" data-target="#filterModal">
    <i class="fa fa-filter"></i> Advanced Filters
  </button>
  <button type="button" class="btn btn-secondary btn-sm" onclick="clearFilter()">
    <i class="fa fa-refresh"></i> Reset
  </button>
</div>

<!-- ================= CREW LEGEND ================= -->
<div id="crewLegend" class="mb-4">
    <strong>Crew Legend:</strong>
    <div class="d-flex flex-wrap mt-2">
        @foreach($crews as $crew)
            @php
                $color = '#'.substr(md5($crew->id),0,6);
                $isActive = request('crew_id') == $crew->id;
            @endphp
            <div style="display:flex;align-items:center;margin-right:20px;margin-bottom:5px; cursor: pointer;" 
                 onclick="filterByCrew({{ $crew->id }})"
                 class="crew-legend-item {{ $isActive ? 'active-legend' : '' }}"
                 data-crew-id="{{ $crew->id }}">
                <div style="width:15px;height:15px; background:{{ $color }}; margin-right:6px;border-radius:3px;"></div>
                <span>{{ $crew->user->name }}</span>
                @if($isActive)
                    <i class="fa fa-check-circle text-success ml-1" style="font-size: 12px;"></i>
                @endif
            </div>
        @endforeach
        <div style="display:flex;align-items:center;margin-right:20px;margin-bottom:5px; cursor: pointer;" 
             onclick="clearCrewFilter()"
             class="crew-legend-item">
            <div style="width:15px;height:15px; background:#6c757d; margin-right:6px;border-radius:3px;"></div>
            <span>Clear Filter</span>
        </div>
    </div>
</div>

<!-- ================= SUMMARY CARDS ================= -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $summary['total_present'] ?? 0 }}</h3>
                <p>Present</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $summary['total_absent'] ?? 0 }}</h3>
                <p>Absent</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $summary['total_half_day'] ?? 0 }}</h3>
                <p>Half Day</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $summary['total_holiday'] ?? 0 }}</h3>
                <p>Holiday</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ $summary['total_leave'] ?? 0 }}</h3>
                <p>Leave</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>Rs. {{ number_format($summary['total_salary'] ?? 0, 2) }}</h3>
                <p>Total Salary</p>
            </div>
        </div>
    </div>
</div>

<!-- ================= LIST VIEW ================= -->
<div id="tableView" class="attendance-list-view">
    <div class="table-responsive">
        <table id="attendanceDataTable" class="table table-bordered table-striped show-search-bar">
            <thead>
                    <th>#</th>
                    <th>Crew Member</th>
                    <th>Date (AD/BS)</th>
                    <th>Status</th>
                    {{-- <th>Salary Amount</th>
                    <th>Bonus</th>
                    <th>Deduction</th>
                    <th>Net Amount</th> --}}
                    {{-- <th>Remarks</th> --}}
                    <th>Actions</th>
                 </tr>
            </thead>
            <tbody id="attendanceTableBody">
                @forelse($attendances as $i => $attendance)
                    @php
                        $statusColor = match($attendance->status) {
                            'present' => '#28a745',
                            'absent' => '#dc3545',
                            'half_day' => '#ffc107',
                            'holiday' => '#17a2b8',
                            'leave' => '#6c757d',
                            default => '#6c757d',
                        };
                        $crewColor = '#'.substr(md5($attendance->crew_id),0,6);
                    @endphp
                    <tr data-attendance-id="{{ $attendance->id }}" data-attendance-date="{{ $attendance->attendance_date }}">
                         <td>{{ $i+1 }}</td>
                         <td>
                            <div style="display: flex; align-items: center;">
                                <div style="width:12px;height:12px; background:{{ $crewColor }}; border-radius:3px; margin-right:6px;"></div>
                                {{ $attendance->crew->user->name ?? '' }}
                            </div>
                         </td>
                        <td class="date-cell">
                            <span class="ad-date">{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('M d, Y') }}</span>
                            <br>
                            <small class="bs-date text-muted">Loading...</small>
                         </td>
                         <td>
                            <span class="badge" style="background-color: {{ $statusColor }}; color: white; padding: 5px 10px;">
                                {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                            </span>
                         </td>
                         {{-- <td>Rs. {{ number_format($attendance->salary_amount, 2) }}</td>
                         <td>Rs. {{ number_format($attendance->bonus, 2) }}</td>
                         <td>Rs. {{ number_format($attendance->deduction, 2) }}</td>
                         <td><strong>Rs. {{ number_format($attendance->net_amount, 2) }}</strong></td> --}}
                         {{-- <td>{{ $attendance->remarks ?? '-' }}</td> --}}
                         <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                    Actions
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#" onclick="viewAttendance({{ $attendance->id }})">
                                        <i class="fas fa-eye text-info mr-2"></i> View
                                    </a>
                                    <a class="dropdown-item" href="#" onclick="editAttendance({{ $attendance->id }})">
                                        <i class="fas fa-edit text-primary mr-2"></i> Edit
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <button class="dropdown-item text-danger" onclick="deleteAttendance({{ $attendance->id }})">
                                        <i class="fas fa-trash mr-2"></i> Delete
                                    </button>
                                </div>
                            </div>
                         </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">No attendance records found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ================= CALENDAR VIEW ================= -->
<div id="calendarView" class="attendance-calendar-view" style="display: none;">
    <div class="calendar-header mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <button class="btn btn-sm btn-outline-primary" id="prevMonthBtn">
                <i class="fa fa-chevron-left"></i> Previous Month
            </button>
            <div class="text-center">
                <h4 id="currentMonthDisplay" class="mb-0">{{ date('F Y') }}</h4>
                <h5 id="currentNepaliMonthDisplay" class="mb-0 nepali-date" style="color:#198754; font-size:14px;">Loading...</h5>
            </div>
            <button class="btn btn-sm btn-outline-primary" id="nextMonthBtn">
                Next Month <i class="fa fa-chevron-right"></i>
            </button>
        </div>
    </div>
    <div class="mb-3">
        <small class="text-muted">Click on any empty cell to mark attendance. Click on attendance block to view details.</small>
    </div>
    <div style="overflow-x: auto; max-height: 600px; overflow-y: auto;">
        <div id="calendarGridContainer"></div>
    </div>
</div>

</div>
</div>
</div>
</section>

<!-- ================= ATTENDANCE DETAILS MODAL ================= -->
<div class="modal fade" id="attendanceDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa fa-calendar-check-o"></i> Attendance Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="attendanceModalBody">
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin fa-2x"></i> Loading...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fa fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="editAttendanceBtn">
                    <i class="fa fa-edit"></i> Edit Attendance
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MARK ATTENDANCE MODAL ================= -->
<div class="modal fade" id="markAttendanceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Mark Attendance</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="markAttendanceForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Crew Member *</label>
                        <select id="markCrewSelect" name="crew_id" class="form-control" required>
                            <option value="">Select Crew Member</option>
                            @foreach($crews as $crew)
                                <option value="{{ $crew->id }}">{{ $crew->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date *</label>
                        <input type="date" id="markDateInput" name="attendance_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select id="markStatus" name="status" class="form-control" required>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="half_day">Half Day</option>
                            <option value="holiday">Holiday</option>
                            <option value="leave">Leave</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Salary Amount (Rs.)</label>
                        <input type="number" step="0.01" id="markSalary" name="salary_amount" class="form-control" value="0">
                    </div>
                    <div class="form-group">
                        <label>Bonus (Rs.)</label>
                        <input type="number" step="0.01" id="markBonus" name="bonus" class="form-control" value="0">
                    </div>
                    <div class="form-group">
                        <label>Deduction (Rs.)</label>
                        <input type="number" step="0.01" id="markDeduction" name="deduction" class="form-control" value="0">
                    </div>
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea id="markRemarks" name="remarks" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<style>
.nepali-date {
    font-family: 'Hind Siliguri', 'Preeti', 'Arial Unicode MS', sans-serif;
    font-size: 11px;
    color: #198754;
    font-weight: 500;
    line-height: 1.3;
}

.calendar-grid th, .calendar-grid td {
    min-width: 80px;
    text-align: center;
    font-size: 12px;
    padding: 8px 4px;
    vertical-align: middle;
    border: 1px solid #dee2e6;
}

.crew-column {
    min-width: 180px;
    font-weight: 600;
    background: #f8f9fa;
    position: sticky;
    left: 0;
    z-index: 5;
    border-right: 2px solid #dee2e6;
}

.attendance-block {
    color: #fff;
    font-size: 11px;
    padding: 6px 4px;
    border-radius: 4px;
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
    transition: all 0.2s;
    margin: 2px 0;
    font-weight: 500;
    text-align: left;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.attendance-block:hover {
    opacity: 0.9;
    transform: scale(1.02);
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.attendance-block i {
    margin-right: 3px;
}

.calendar-empty-cell {
    cursor: pointer;
    background-color: #f9f9f9;
    transition: background-color 0.2s;
}

.calendar-empty-cell:hover {
    background-color: #e9ecef !important;
}

.today-cell {
    background-color: #fff3cd !important;
}

.crew-legend-item {
    padding: 3px 8px;
    border-radius: 15px;
    transition: all 0.2s;
    border: 1px solid transparent;
}

.crew-legend-item:hover {
    background-color: #f0f0f0;
    border-color: #ddd;
}

.crew-legend-item.active-legend {
    background-color: #e3f2fd;
    border-color: #2196F3;
    font-weight: 500;
}

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

.bg-success { background-color: #28a745 !important; }
.bg-danger { background-color: #dc3545 !important; }
.bg-warning { background-color: #ffc107 !important; }
.bg-info { background-color: #17a2b8 !important; }
.bg-secondary { background-color: #6c757d !important; }
.bg-primary { background-color: #007bff !important; }
</style>

<script>
let currentMonth = moment();
let allCrews = @json($crews);
let crewColors = {};
let dateCache = {};
let dataTable = null;
let calendarAttendances = [];

// Generate consistent colors for crews
@foreach($crews as $crew)
    crewColors[{{ $crew->id }}] = '#{{ substr(md5($crew->id),0,6) }}';
@endforeach

// Status colors
const statusColors = {
    'present': '#28a745',
    'absent': '#dc3545',
    'half_day': '#ffc107',
    'holiday': '#17a2b8',
    'leave': '#6c757d'
};

let currentAttendanceId = null;

$(document).ready(function() {
    // Initialize DataTable only if it doesn't exist
    if ($('#attendanceDataTable').length && !$.fn.DataTable.isDataTable('#attendanceDataTable')) {
        dataTable = $('#attendanceDataTable').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "pageLength": 25
        });
    }
    
    // View toggle buttons
    $('#listViewBtn').on('click', function() {
        showTableView();
    });
    
    $('#calendarViewBtn').on('click', function() {
        showCalendarView();
    });
    
    // Month navigation buttons
    $('#prevMonthBtn').on('click', function() {
        changeMonth(-1);
    });
    
    $('#nextMonthBtn').on('click', function() {
        changeMonth(1);
    });
    
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('view') === 'calendar') {
        showCalendarView();
    } else {
        showTableView();
    }
    
    setFilterValuesFromUrl();
    loadNepaliDatesForTable();
    
    // Mark attendance form submit
    $('#markAttendanceForm').on('submit', function(e) {
        e.preventDefault();
        saveAttendance();
    });
    
    // Edit button click handler
    $('#editAttendanceBtn').on('click', function() {
        if (currentAttendanceId) {
            window.location.href = `/dashboard/attendance/${currentAttendanceId}/edit`;
        }
    });
});

function showTableView() {
    $('#tableView').show();
    $('#calendarView').hide();
    let url = new URL(window.location.href);
    url.searchParams.set('view', 'list');
    window.history.replaceState({}, '', url);
}

function showCalendarView() {
    $('#tableView').hide();
    $('#calendarView').show();
    let url = new URL(window.location.href);
    url.searchParams.set('view', 'calendar');
    window.history.replaceState({}, '', url);
    loadCalendarGrid();
}

function setFilterValuesFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('crew_id')) $('#crewFilter').val(urlParams.get('crew_id'));
    if (urlParams.has('status')) $('#statusFilter').val(urlParams.get('status'));
    if (urlParams.has('start_date')) $('#startDateFilter').val(urlParams.get('start_date'));
    if (urlParams.has('end_date')) $('#endDateFilter').val(urlParams.get('end_date'));
    if (urlParams.has('remarks')) $('#remarksFilter').val(urlParams.get('remarks'));
}

function filterByCrew(crewId) {
    $('#crewFilter').val(crewId);
    applyFilter();
}

function clearCrewFilter() {
    $('#crewFilter').val('');
    let url = new URL(window.location.href);
    url.searchParams.delete('crew_id');
    
    if ($('#calendarView').is(':visible')) {
        loadCalendarGrid();
        window.history.replaceState({}, '', url);
        $('.crew-legend-item').removeClass('active-legend');
        $('.crew-legend-item .fa-check-circle').remove();
        toastr.success('Crew filter cleared');
    } else {
        window.location.href = url.toString();
    }
}

async function loadNepaliDatesForTable() {
    $('#attendanceTableBody tr').each(async function() {
        let $row = $(this);
        let attendanceDate = $row.data('attendance-date');
        
        if (attendanceDate) {
            try {
                let bsDate = await convertToNepaliDate(attendanceDate);
                $row.find('.date-cell .bs-date').text(bsDate.display);
            } catch (e) {
                console.error('Error converting date:', e);
            }
        }
    });
}

function convertToNepaliDate(adDate) {
    return new Promise((resolve, reject) => {
        if (dateCache[adDate]) {
            resolve(dateCache[adDate]);
            return;
        }
        
        $.ajax({
            url: "{{ route('admin.attendance.convert_ad_to_bs') }}",
            type: 'POST',
            data: {
                date: adDate,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.success) {
                    let nepaliOnly = response.display || response.nepali || '';
                    dateCache[adDate] = {
                        display: nepaliOnly,
                        day: response.day || '',
                        month: response.month || '',
                        year: response.year || ''
                    };
                    resolve(dateCache[adDate]);
                } else {
                    reject('Conversion failed');
                }
            },
            error: function() {
                reject('Error converting date');
            }
        });
    });
}

function applyFilter() {
    $('#filterModal').modal('hide');
    
    let params = {
        crew_id: $('#crewFilter').val(),
        status: $('#statusFilter').val(),
        start_date: $('#startDateFilter').val(),
        end_date: $('#endDateFilter').val(),
        remarks: $('#remarksFilter').val()
    };
    
    Object.keys(params).forEach(key => {
        if (!params[key]) delete params[key];
    });
    
    if ($('#calendarView').is(':visible')) {
        loadCalendarGrid();
        let url = new URL(window.location.href);
        Object.keys(params).forEach(key => url.searchParams.set(key, params[key]));
        url.searchParams.set('view', 'calendar');
        window.history.replaceState({}, '', url);
    } else {
        let url = new URL(window.location.href);
        Object.keys(params).forEach(key => url.searchParams.set(key, params[key]));
        url.searchParams.set('view', 'list');
        window.location.href = url.toString();
    }
}

function clearFilter() {
    $('#crewFilter, #statusFilter, #remarksFilter').val('');
    $('#startDateFilter, #endDateFilter').val('');
    
    if ($('#calendarView').is(':visible')) {
        loadCalendarGrid();
        let url = new URL(window.location.href);
        url.search = '?view=calendar';
        window.history.replaceState({}, '', url);
    } else {
        window.location.href = "{{ route('admin.attendance.index') }}?view=list";
    }
}

function changeMonth(direction) {
    if (direction === -1) {
        currentMonth.subtract(1, 'month');
    } else {
        currentMonth.add(1, 'month');
    }
    $('#currentMonthDisplay').text(currentMonth.format('MMMM YYYY'));
    updateNepaliMonthDisplay();
    loadCalendarGrid();
}

async function updateNepaliMonthDisplay() {
    let firstDate = currentMonth.clone().startOf('month').format('YYYY-MM-DD');
    let lastDate = currentMonth.clone().endOf('month').format('YYYY-MM-DD');
    
    try {
        let bsFirstDate = await convertToNepaliDate(firstDate);
        let bsLastDate = await convertToNepaliDate(lastDate);
        
        if (bsFirstDate && bsLastDate) {
            let firstParts = bsFirstDate.display.split(' ');
            let lastParts = bsLastDate.display.split(' ');
            let firstMonthName = firstParts.length >= 2 ? firstParts[1] : '';
            let lastMonthName = lastParts.length >= 2 ? lastParts[1] : '';
            let year = firstParts.length >= 1 ? firstParts[0] : '';
            
            if (firstMonthName && lastMonthName) {
                if (firstMonthName === lastMonthName) {
                    $('#currentNepaliMonthDisplay').text(`${firstMonthName} ${year}`);
                } else {
                    $('#currentNepaliMonthDisplay').text(`${firstMonthName}/${lastMonthName} ${year}`);
                }
            }
        }
    } catch (e) {
        console.error('Error loading Nepali month:', e);
        $('#currentNepaliMonthDisplay').text('');
    }
}

function loadCalendarGrid() {
    let filterData = {
        crew_id: $('#crewFilter').val(),
        status: $('#statusFilter').val(),
        start_date: $('#startDateFilter').val(),
        end_date: $('#endDateFilter').val(),
        month: currentMonth.format('YYYY-MM')
    };
    
    $.ajax({
        url: "{{ route('admin.attendance.events') }}",
        type: "GET",
        data: filterData,
        success: function(attendances) {
            calendarAttendances = attendances;
            renderCalendarGrid();
        },
        error: function() {
            toastr.error('Error loading calendar data');
        }
    });
}

async function renderCalendarGrid() {
    let startDate = currentMonth.clone().startOf('month');
    let endDate = currentMonth.clone().endOf('month');
    let today = moment().format('YYYY-MM-DD');
    
    // Get Nepali dates for header
    let dates = [];
    let current = startDate.clone();
    while (current <= endDate) {
        dates.push(current.format('YYYY-MM-DD'));
        current.add(1, 'day');
    }
    
    let nepaliMap = {};
    try {
        let response = await $.ajax({
            url: "{{ route('admin.attendance.convert_multiple_ad_to_bs') }}",
            type: "POST",
            data: { dates: dates, _token: "{{ csrf_token() }}" }
        });
        nepaliMap = response.data || {};
    } catch(e) {
        console.error('Error fetching Nepali dates:', e);
    }
    
    // Build header HTML
    let headerHtml = '<table class="table table-bordered calendar-grid" style="min-width: 1200px;">';
    headerHtml += '<thead><tr><th class="crew-column">Crew Member / Date</th>';
    
    current = startDate.clone();
    while (current <= endDate) {
        let dateStr = current.format('YYYY-MM-DD');
        let isToday = dateStr === today;
        let bsData = nepaliMap[dateStr] || {};
        let nepaliDay = bsData.day || '';
        
        headerHtml += `<th style="min-width: 80px; ${isToday ? 'background:#cfe2ff;' : ''}">
            ${current.format('D')}<br>
            <small class="nepali-date">${nepaliDay}</small>
        </th>`;
        current.add(1, 'day');
    }
    headerHtml += '</tr></thead><tbody>';
    
    // Build body rows for each crew
    let crewsToShow = allCrews;
    let selectedCrewId = $('#crewFilter').val();
    if (selectedCrewId) {
        crewsToShow = allCrews.filter(c => c.id == parseInt(selectedCrewId));
    }
    
    for (let crew of crewsToShow) {
        headerHtml += `<tr data-crew-id="${crew.id}">`;
        headerHtml += `<td class="crew-column">
            <div style="display: flex; align-items: center; padding: 5px;">
                <div style="width:15px;height:15px; background:${crewColors[crew.id]}; border-radius:3px; margin-right:8px;"></div>
                <span style="font-weight: 600;">${crew.user.name}</span>
            </div>
        </td>`;
        
        current = startDate.clone();
        while (current <= endDate) {
            let dateStr = current.format('YYYY-MM-DD');
            let isToday = dateStr === today;
            let dayAttendance = calendarAttendances.filter(a => a.crew_id == crew.id && a.start == dateStr);
            
            if (dayAttendance.length > 0) {
                headerHtml += `<td class="${isToday ? 'today-cell' : ''}" style="padding: 2px;">`;
                for (let attendance of dayAttendance) {
                    let statusColor = statusColors[attendance.extendedProps.status] || '#6c757d';
                    let statusText = attendance.extendedProps.status.replace('_', ' ').toUpperCase();
                    
                    headerHtml += `
                        <div class="attendance-block" 
                            style="background: ${crewColors[crew.id]}; border-left: 4px solid ${statusColor};"
                            onclick="event.stopPropagation(); viewAttendance(${attendance.id})"
                            title="${statusText}">
                            <i class="fa ${getStatusIcon(attendance.extendedProps.status)}"></i> ${statusText}
                        </div>`;
                }
                headerHtml += `</td>`;
            } else {
                headerHtml += `<td class="calendar-empty-cell ${isToday ? 'today-cell' : ''}" 
                    onclick="quickMarkAttendance(${crew.id}, '${dateStr}')"
                    style="cursor: pointer; background: #f9f9f9;"
                    title="Click to mark attendance for ${crew.user.name} on ${dateStr}">
                    <div style="min-height: 60px;"></div>
                </td>`;
            }
            current.add(1, 'day');
        }
        headerHtml += `</tr>`;
    }
    
    headerHtml += '</tbody></table>';
    $('#calendarGridContainer').html(headerHtml);
}

function getStatusIcon(status) {
    const icons = {
        'present': 'fa-check-circle',
        'absent': 'fa-times-circle',
        'half_day': 'fa-adjust',
        'holiday': 'fa-sun-o',
        'leave': 'fa-bed'
    };
    return icons[status] || 'fa-question-circle';
}

function openMarkAttendanceModal() {
    $('#markCrewSelect').val('');
    $('#markDateInput').val(new Date().toISOString().split('T')[0]);
    $('#markStatus').val('present');
    $('#markSalary').val(0);
    $('#markBonus').val(0);
    $('#markDeduction').val(0);
    $('#markRemarks').val('');
    $('#markAttendanceModal').modal('show');
}

function quickMarkAttendance(crewId, date) {
    $('#markCrewSelect').val(crewId);
    $('#markDateInput').val(date);
    $('#markStatus').val('present');
    $('#markSalary').val(0);
    $('#markBonus').val(0);
    $('#markDeduction').val(0);
    $('#markRemarks').val('');
    $('#markAttendanceModal').modal('show');
}

function saveAttendance() {
    let data = {
        _token: "{{ csrf_token() }}",
        crew_id: $('#markCrewSelect').val(),
        attendance_date: $('#markDateInput').val(),
        status: $('#markStatus').val(),
        salary_amount: $('#markSalary').val(),
        bonus: $('#markBonus').val(),
        deduction: $('#markDeduction').val(),
        remarks: $('#markRemarks').val()
    };
    
    if (!data.crew_id) {
        toastr.error('Please select a crew member');
        return;
    }
    
    $.ajax({
        url: "{{ route('admin.attendance.store') }}",
        type: "POST",
        data: data,
        success: function(response) {
            toastr.success('Attendance saved successfully');
            $('#markAttendanceModal').modal('hide');
            if ($('#calendarView').is(':visible')) {
                loadCalendarGrid();
            } else {
                location.reload();
            }
        },
        error: function(xhr) {
            let errorMsg = xhr.responseJSON?.message || 'Error saving attendance';
            toastr.error(errorMsg);
        }
    });
}

function viewAttendance(id) {
    currentAttendanceId = id;
    $('#attendanceDetailsModal').modal('show');
    
    $.ajax({
        url: `/dashboard/attendance/${id}`,
        type: "GET",
        success: function(attendance) {
            let statusColor = statusColors[attendance.status] || '#6c757d';
            let crewColor = crewColors[attendance.crew_id] || '#3498db';
            let date = moment(attendance.attendance_date).format('MMMM D, YYYY');
            
            let html = `
                <div class="container-fluid">
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center p-2" style="background: ${statusColor}20; border-left: 4px solid ${statusColor};">
                                <span><strong>Status:</strong> ${attendance.status ? attendance.status.toUpperCase().replace('_', ' ') : 'N/A'}</span>
                                <span class="badge" style="background: ${statusColor}; color: white;">
                                    ${attendance.status ? attendance.status.replace('_', ' ') : 'N/A'}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fa fa-user"></i> Crew Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                         <tr><td style="width: 100px;"><strong>Name:</strong> <td><span style="display: inline-block; width: 12px; height: 12px; background: ${crewColor}; border-radius: 3px; margin-right: 5px;"></span>${attendance.crew?.user?.name || 'N/A'}</td></tr>
                                        <tr><td><strong>Email:</strong> <td>${attendance.crew?.user?.email || 'N/A'}</td></tr>
                                        <tr><td><strong>Phone:</strong> <td>${attendance.crew?.user?.phone || 'N/A'}</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fa fa-calendar"></i> Attendance Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr><td style="width: 100px;"><strong>Date:</strong> <td>${date}</td></tr>
                                        <tr><td><strong>Salary Amount:</strong> <td>Rs. ${formatNumber(attendance.salary_amount)}</td></tr>
                                        <tr><td><strong>Bonus:</strong> <td>Rs. ${formatNumber(attendance.bonus)}</td></tr>
                                        <tr><td><strong>Deduction:</strong> <td>Rs. ${formatNumber(attendance.deduction)}</td></tr>
                                        <tr><td><strong>Net Amount:</strong> <td><strong>Rs. ${formatNumber(attendance.net_amount)}</strong></td></tr>
                                        ${attendance.remarks ? `<tr><td><strong>Remarks:</strong> <td>${attendance.remarks}</td></tr>` : ''}
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#attendanceModalBody').html(html);
        },
        error: function() {
            $('#attendanceModalBody').html('<div class="alert alert-danger">Error loading attendance details</div>');
        }
    });
}

function editAttendance(id) {
    window.location.href = `/dashboard/attendance/${id}/edit`;
}

function deleteAttendance(id) {
    if (confirm('Are you sure you want to delete this attendance record?')) {
        $.ajax({
            url: `/dashboard/attendance/${id}`,
            type: "DELETE",
            data: { _token: "{{ csrf_token() }}" },
            success: function() {
                toastr.success('Attendance deleted successfully');
                if ($('#calendarView').is(':visible')) {
                    loadCalendarGrid();
                } else {
                    location.reload();
                }
            },
            error: function() {
                toastr.error('Error deleting attendance');
            }
        });
    }
}

function formatNumber(num) {
    return parseFloat(num).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>
@endsection