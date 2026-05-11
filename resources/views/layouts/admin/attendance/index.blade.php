{{-- resources/views/admin/attendance/index.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')

@if(auth()->user()->can('index_attendance') || auth()->user()->can('create_attendance') || auth()->user()->can('export_attendance'))
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1>Attendance Management</h1>
        <div>
            @if(auth()->user()->can('index_attendance'))
            <button class="btn btn-outline-secondary btn-sm" onclick="showTable()">
                <i class="fa fa-list"></i> List View
            </button>
            @endif

            @if(auth()->user()->can('index_attendance'))
            <button class="btn btn-outline-success btn-sm" onclick="showCalendar()">
                <i class="fa fa-calendar"></i> Calendar View
            </button>
            @endif

            @if(auth()->user()->can('create_attendance'))
            <button class="btn btn-primary btn-sm" onclick="openCreateAttendance()">
                <i class="fa fa-plus"></i> Mark Attendance
            </button>
            @endif

            @if(auth()->user()->can('export_attendance'))
            <a id="exportBtn"
                href="{{ route('admin.attendance.export') }}"
                class="btn btn-success btn-sm">
                <i class="fa fa-file-excel"></i> Export Excel
            </a>
            @endif
        </div>
    </div>
</div>
@endif

<section class="content">
<div class="container-fluid">

@include('layouts.admin_theme.alert')

<div class="card card-primary card-outline">
<div class="card-body">

<!-- Filter Modal -->
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
                                <input type="date" id="startDateFilter" class="form-control" placeholder="From" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-6">
                                <input type="date" id="endDateFilter" class="form-control" placeholder="To" value="{{ request('end_date') }}">
                            </div>
                        </div>
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

<!-- Crew Legend -->
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
            <span>Clear Crew Filter</span>
        </div>
    </div>
</div>

{{-- <!-- Status Legend -->
<div class="mb-4">
    <strong>Status Legend:</strong>
    <div class="d-flex flex-wrap mt-2">
        <div style="display:flex;align-items:center;margin-right:20px;">
            <div style="width:15px;height:15px; background:#28a745; margin-right:6px;border-radius:3px;"></div>
            <span>Present</span>
        </div>
        <div style="display:flex;align-items:center;margin-right:20px;">
            <div style="width:15px;height:15px; background:#dc3545; margin-right:6px;border-radius:3px;"></div>
            <span>Absent</span>
        </div>
        <div style="display:flex;align-items:center;margin-right:20px;">
            <div style="width:15px;height:15px; background:#ffc107; margin-right:6px;border-radius:3px;"></div>
            <span>Half Day</span>
        </div>

        <div style="display:flex;align-items:center;margin-right:20px;">
            <div style="width:15px;height:15px; background:#6c757d; margin-right:6px;border-radius:3px;"></div>
            <span>Leave</span>
        </div>
    </div>
</div> --}}



<!-- LIST VIEW -->
<div id="tableView">
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered table-striped show-search-bar">
            <thead>
                <td>#</th>
                    <th>Crew Member</th>
                    <th>Date (AD/BS)</th>
                    <th>Allowances</th>
                    <th>Attendance</th>
                    <th>Pay By</th>
                    <th>Remarks</th>
                    {{-- <th>Salary (Rs)</th>
                    <th>Net Amount</th> --}}
                    <th>Actions</th>
                </thead>
            <tbody id="attendanceTableBody">
                @forelse($attendances as $i => $attendance)
                    @php
                        $statusColor = $attendance->status == 'present' ? '#28a745' : 
                                      ($attendance->status == 'half_day' ? '#ffc107' : 
                                      ($attendance->status == 'holiday' ? '#17a2b8' : 
                                      ($attendance->status == 'leave' ? '#6c757d' : '#dc3545')));
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
                            <small class="bs-date text-muted" data-date="{{ $attendance->attendance_date }}">Loading...</small>
                        </td>
                        <td>Rs. {{ ($attendance->allowances) }}</td>
                        <td> {{ ($attendance->status) }}</td>

                        
                       <td>
                            @if(($attendance->payment_status) === 'paid')
                                <span class="badge badge-success">
                                    Paid
                                </span>
                            @else
                                <button id="khaltiBtn-{{ $attendance->id }}" class="btn btn-sm btn-primary"
                                    onclick="payByKhalti({{ $attendance->id }})">
                                    <i class="fas fa-credit-card mr-2"></i>Khalti
                                </button>
                                {{-- <button class="btn btn-sm btn-success"
                                    onclick="payByEsewa({{ $attendance->id }})">

                                    <i class="fas fa-wallet mr-2"></i>
                                    ESewa
                                </button> --}}
                                <button id="manualBtn-{{ $attendance->id }}" class="btn btn-sm btn-success"
                                    onclick="selectProof({{ $attendance->id }})">
                                    <i class="fas fa-money-bill mr-2"></i> Cash Payment
                                </button>
                            @endif
                        </td>
                        <td> {{ ($attendance->payment_remarks) }}</td>

                        {{-- <td>Rs. {{ number_format($attendance->salary_amount ?? 0, 2) }}</td>
                        <td><strong>Rs. {{ number_format($attendance->net_amount ?? 0, 2) }}</strong></td> --}}
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                    Actions
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#" onclick="viewAttendance({{ $attendance->id }})">
                                        <i class="fas fa-eye text-info mr-2"></i> View
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.attendance.edit', $attendance->id) }}">
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
                    <td colspan="7" class="text-center">No attendance records found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- CALENDAR VIEW -->
<div id="calendarView" style="display:none;">
    <!-- Month Navigation -->
    <div id="monthNav" class="mb-3 d-flex justify-content-between align-items-center" style="display: none;">
        <button class="btn btn-sm btn-outline-primary" onclick="changeMonth(-1)">
            <i class="fa fa-chevron-left"></i> Previous Month
        </button>
        <div class="text-center">
            <h4 id="currentMonth" class="mb-0">{{ date('F Y') }}</h4>
            <h5 id="currentNepaliMonth" class="mb-0 nepali-date" style="color:#198754; font-size:16px;">Loading...</h5>
        </div>
        <button class="btn btn-sm btn-outline-primary" onclick="changeMonth(1)">
            Next Month <i class="fa fa-chevron-right"></i>
        </button>
    </div>
    <div class="mb-3">
        <small class="text-muted">Click on any empty cell to mark attendance. Click on attendance block to view details.</small>
    </div>
    <div style="overflow-x:auto; max-height: 600px; overflow-y: auto;">
        <table class="table table-bordered attendance-grid" style="min-width: 1200px;">
            <thead id="attendanceGridHead" style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;"></thead>
            <tbody id="attendanceGridBody"></tbody>
        </table>
    </div>
</div>

</div>
</div>
</div>
</section>

<!-- Attendance Details Modal -->
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
                <a href="#" id="editAttendanceBtn" class="btn btn-primary btn-sm">
                    <i class="fa fa-edit"></i> Edit Attendance
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<style>
@import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap');

.nepali-date {
    font-family: 'Hind Siliguri', 'Preeti', 'Arial Unicode MS', sans-serif;
    font-size: 11px;
    color: #198754;
    font-weight: 500;
    line-height: 1.3;
}

.attendance-grid th, .attendance-grid td {
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

.badge {
    padding: 5px 10px;
    font-weight: 500;
}

#monthNav {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #dee2e6;
}

.small-box {
    border-radius: 0.25rem;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    margin-bottom: 20px;
}

.small-box .inner {
    padding: 10px;
}

.small-box h3 {
    font-size: 2rem;
    font-weight: bold;
    margin: 0 0 10px 0;
    color: white;
}

.small-box p {
    font-size: 1rem;
    margin: 0;
    color: white;
}

.bg-success { background-color: #28a745 !important; }
.bg-danger { background-color: #dc3545 !important; }
.bg-warning { background-color: #ffc107 !important; }
.bg-info { background-color: #17a2b8 !important; }
.bg-secondary { background-color: #6c757d !important; }
.bg-primary { background-color: #007bff !important; }

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
</style>

<script>
let currentMonth = moment();
let allCrews = @json($crews);
let crewColors = {};
let dateCache = {};

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

$(document).ready(function() {
    // Check if we should show calendar view based on URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('view') === 'calendar') {
        showCalendar();
    } else {
        showTable();
    }
    
    // Set filter values from URL
    setFilterValuesFromUrl();
    
    // Load Nepali dates for table view
    loadNepaliDatesForTable();
    
    // Initialize DataTable
    if ($.fn.DataTable.isDataTable('#dataTable')) {
        $('#dataTable').DataTable().destroy();
    }
    $('#dataTable').DataTable({
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true
    });
});

function setFilterValuesFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('crew_id')) {
        $('#crewFilter').val(urlParams.get('crew_id'));
    }
    if (urlParams.has('status')) {
        $('#statusFilter').val(urlParams.get('status'));
    }
    if (urlParams.has('start_date')) {
        $('#startDateFilter').val(urlParams.get('start_date'));
    }
    if (urlParams.has('end_date')) {
        $('#endDateFilter').val(urlParams.get('end_date'));
    }
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
    
    updateExportLink();
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
                    let nepaliOnly = '';
                    if (response.display && response.display.includes('|')) {
                        nepaliOnly = response.display.split('|')[1].trim();
                    } else if (response.nepali) {
                        nepaliOnly = response.nepali;
                    } else {
                        nepaliOnly = response.display || '';
                    }
                    
                    let dayOnly = '';
                    if (nepaliOnly) {
                        let parts = nepaliOnly.split(' ');
                        dayOnly = parts.length >= 3 ? parts[2] : (parts.length >= 2 ? parts[1] : '');
                    }
                    
                    dateCache[adDate] = {
                        nepali: response.nepali || '',
                        day: response.day || dayOnly,
                        month: response.month || '',
                        year: response.year || '',
                        display: nepaliOnly,
                        day_only: dayOnly
                    };
                    resolve(dateCache[adDate]);
                } else {
                    reject('Conversion failed');
                }
            },
            error: function(xhr, status, error) {
                console.error('Date conversion error:', error);
                let date = new Date(adDate);
                let nepYear = date.getFullYear() + 57;
                let nepMonth = date.getMonth() + 9;
                let nepDay = date.getDate();
                
                if (nepMonth > 12) {
                    nepMonth -= 12;
                    nepYear += 1;
                }
                
                const monthNames = {
                    1: 'बैशाख', 2: 'जेठ', 3: 'असार', 4: 'साउन',
                    5: 'भदौ', 6: 'असोज', 7: 'कात्तिक', 8: 'मंसिर',
                    9: 'पुस', 10: 'माघ', 11: 'फागुन', 12: 'चैत'
                };
                let nepaliMonthName = monthNames[nepMonth] || 'बैशाख';
                
                const nepaliNumbers = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];
                let nepaliYearStr = nepYear.toString().split('').map(d => nepaliNumbers[parseInt(d)]).join('');
                let nepaliDayStr = nepDay.toString().split('').map(d => nepaliNumbers[parseInt(d)]).join('');
                
                let fallbackDate = {
                    nepali: `${nepaliYearStr} ${nepaliMonthName} ${nepaliDayStr}`,
                    day: nepaliDayStr,
                    month: nepMonth.toString().padStart(2, '0'),
                    year: nepYear.toString(),
                    display: `${nepaliYearStr} ${nepaliMonthName} ${nepaliDayStr}`,
                    day_only: nepaliDayStr
                };
                dateCache[adDate] = fallbackDate;
                resolve(fallbackDate);
            }
        });
    });
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

function showTable() {
    $('#tableView').show();
    $('#calendarView').hide();
    $('#monthNav').hide();
    
    let url = new URL(window.location.href);
    url.searchParams.set('view', 'list');
    window.history.replaceState({}, '', url);
}

function showCalendar() {
    $('#tableView').hide();
    $('#calendarView').show();
    $('#monthNav').show();
    
    let url = new URL(window.location.href);
    url.searchParams.set('view', 'calendar');
    window.history.replaceState({}, '', url);
    updateNepaliMonthDisplay();
    loadCalendarGrid();
}

function applyFilter() {
    $('#filterModal').modal('hide');
    
    let params = {
        crew_id: $('#crewFilter').val(),
        status: $('#statusFilter').val(),
        start_date: $('#startDateFilter').val(),
        end_date: $('#endDateFilter').val()
    };
    
    Object.keys(params).forEach(key => {
        if (!params[key]) {
            delete params[key];
        }
    });
    
    if ($('#calendarView').is(':visible')) {
        loadCalendarGrid();
        let url = new URL(window.location.href);
        Object.keys(params).forEach(key => {
            url.searchParams.set(key, params[key]);
        });
        url.searchParams.set('view', 'calendar');
        window.history.replaceState({}, '', url);
    } else {
        let url = new URL(window.location.href);
        Object.keys(params).forEach(key => {
            url.searchParams.set(key, params[key]);
        });
        url.searchParams.set('view', 'list');
        window.location.href = url.toString();
    }
    
    updateExportLink();
}

function clearFilter() {
    $('#crewFilter, #statusFilter').val('');
    $('#startDateFilter, #endDateFilter').val('');
    
    if ($('#calendarView').is(':visible')) {
        loadCalendarGrid();
        let url = new URL(window.location.href);
        url.search = '?view=calendar';
        window.history.replaceState({}, '', url);
    } else {
        window.location.href = "{{ route('admin.attendance.index') }}?view=list";
    }
    
    updateExportLink();
}

async function updateNepaliMonthDisplay() {
    let firstDate = currentMonth.clone().startOf('month').format('YYYY-MM-DD');
    let lastDate = currentMonth.clone().endOf('month').format('YYYY-MM-DD');
    
    try {
        let bsFirstDate = await convertToNepaliDate(firstDate);
        let bsLastDate = await convertToNepaliDate(lastDate);

        if (bsFirstDate && bsLastDate) {
            let firstMonth = bsFirstDate.month || '';
            let lastMonth = bsLastDate.month || '';
            let nepaliYear = bsFirstDate.year || '';
            
            if (firstMonth && lastMonth) {
                if (firstMonth === lastMonth) {
                    $('#currentNepaliMonth').text(`${firstMonth} ${nepaliYear}`);
                } else {
                    $('#currentNepaliMonth').text(`${firstMonth}/${lastMonth} ${nepaliYear}`);
                }
            }
        }
    } catch (e) {
        console.error('Error loading Nepali month:', e);
        $('#currentNepaliMonth').text('');
    }
}

function changeMonth(direction) {
    if (direction === -1) {
        currentMonth.subtract(1, 'month');
    } else if (direction === 1) {
        currentMonth.add(1, 'month');
    }

    $('#currentMonth').text(currentMonth.format('MMMM YYYY'));
    updateNepaliMonthDisplay();
    loadCalendarGrid();
}

function loadCalendarGrid() {
    $('#currentMonth').text(currentMonth.format('MMMM YYYY'));
    
    let filterData = {
        crew_id: $('#crewFilter').val(),
        status: $('#statusFilter').val(),
        start_date: $('#startDateFilter').val(),
        end_date: $('#endDateFilter').val(),
        month: currentMonth.format('YYYY-MM')
    };
    
    // Remove empty values
    Object.keys(filterData).forEach(key => {
        if (!filterData[key]) {
            delete filterData[key];
        }
    });
    
    $.ajax({
        url: "{{ route('admin.attendance.events') }}",
        type: "GET",
        data: filterData,
        success: function(attendances) {
            let startDate = currentMonth.clone().startOf('month');
            let endDate = currentMonth.clone().endOf('month');
            
            buildCalendarHeader(startDate, endDate);
            buildCalendarBody(attendances, startDate, endDate);
        },
        error: function(xhr) {
            console.error('Error loading calendar:', xhr);
            toastr.error('Error loading calendar data');
        }
    });
}

async function buildCalendarHeader(startDate, endDate) {
    let adRow = '<tr><th class="crew-column">Crew / Date</th>';
    let bsRow = '<tr><th class="crew-column">कर्मचारी / मिति</th>';

    let current = startDate.clone();
    let today = moment().format('YYYY-MM-DD');
    let dates = [];

    while (current <= endDate) {
        dates.push(current.format('YYYY-MM-DD'));
        current.add(1, 'day');
    }

    let response = await $.ajax({
        url: "{{ route('admin.attendance.convert_multiple_ad_to_bs') }}",
        type: "POST",
        data: {
            dates: dates,
            _token: "{{ csrf_token() }}"
        }
    });

    let nepaliMap = response.data || {};

    current = startDate.clone();

    while (current <= endDate) {
        let dateStr = current.format('YYYY-MM-DD');
        let isToday = dateStr === today;
        let bsData = nepaliMap[dateStr] || {};
        let nepaliDay = bsData.day || '';

        adRow += `<th style="${isToday ? 'background:#fff3cd;' : ''}">${current.format('D')}</th>`;
        bsRow += `<th style="${isToday ? 'background:#fff3cd;' : ''}">${nepaliDay}</th>`;

        current.add(1, 'day');
    }

    adRow += '<th>Total</th></tr>';
    bsRow += '<th>जम्मा</th></tr>';

    $('#attendanceGridHead').html(adRow + bsRow);
}

function buildCalendarBody(attendances, startDate, endDate) {
    let html = '';
    let today = moment().format('YYYY-MM-DD');
    
    let crewsToShow = allCrews;
    let selectedCrewId = $('#crewFilter').val();
    if (selectedCrewId) {
        crewsToShow = allCrews.filter(c => c.id == parseInt(selectedCrewId));
    }
    
    crewsToShow.forEach(crew => {
        let totalDays = 0;
        html += `<tr data-crew-id="${crew.id}">`;
        html += `<td class="crew-column">
                    <div style="display: flex; align-items: center; padding: 5px;">
                        <div style="width:15px;height:15px; background:${crewColors[crew.id]}; border-radius:3px; margin-right:8px;"></div>
                        <span style="font-weight: 600;">${crew.user.name}</span>
                    </div>
                  </td>`;

        let current = startDate.clone();
        
        while (current <= endDate) {
            let currentDateStr = current.format('YYYY-MM-DD');
            let isToday = currentDateStr === today;
            
            // Find attendance for this crew on this date
            let attendance = null;
            if (attendances && attendances.length > 0) {
                attendance = attendances.find(a => {
                    // Check if a has extendedProps or direct crew_id
                    let crewId = a.extendedProps ? a.extendedProps.crew_id : a.crew_id;
                    let startDate = a.start || a.attendance_date;
                    return crewId == crew.id && startDate == currentDateStr;
                });
            }

            if (attendance) {
                totalDays++;
                // Get status from either extendedProps or direct
                let status = attendance.extendedProps ? attendance.extendedProps.status : attendance.status;
                let statusColor = statusColors[status] || '#6c757d';
                let statusText = status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
                let attendanceId = attendance.id;
                
                html += `<td class="${isToday ? 'today-cell' : ''}" style="padding: 2px;">
                            <div class="attendance-block" 
                                style="background: ${crewColors[crew.id]}; border-left: 4px solid ${statusColor};"
                                onclick="event.stopPropagation(); viewAttendance(${attendanceId})"
                                title="${statusText}">
                                <i class="fa ${getStatusIcon(status)}"></i> ${statusText.substring(0, 3)}
                            </div>
                          </td>`;
            } else {
                html += `<td class="calendar-empty-cell ${isToday ? 'today-cell' : ''}" 
                            onclick="openCreateAttendance(${crew.id}, '${currentDateStr}')"
                            style="cursor: pointer;"
                            title="Click to mark attendance for ${crew.user.name} on ${currentDateStr}">
                            &nbsp;
                          </td>`;
            }
            
            current.add(1, 'day');
        }
        
        html += `<td><span class="badge badge-info">${totalDays}</span></td>`;
        html += `</tr>`;
    });
    
    $('#attendanceGridBody').html(html);
}

function getStatusIcon(status) {
    const icons = {
        'present': 'fa-check-circle',
        'absent': 'fa-times-circle',
        'half_day': 'fa-adjust',
        'holiday': 'fa-sun-o',
        'leave': 'fa-bed'
    };
    return icons[status] || 'fa-calendar';
}

function openCreateAttendance(crewId, date) {
    let url = "{{ route('admin.attendance.create') }}?crew_id=" + crewId + "&date=" + date;
    window.location.href = url;
}
// Add this helper function at the top of your script section
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Replace your existing viewAttendance function with this updated version
async function viewAttendance(attendanceId) {
    $('#attendanceModalBody').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i><p class="mt-2">Loading attendance details...</p></div>');
    $('#attendanceDetailsModal').modal('show');
    
    $.ajax({
        url: `/dashboard/attendance/${attendanceId}`,
        type: "GET",
        success: async function(attendance) {
            let statusColor = statusColors[attendance.status] || '#6c757d';
            let crewColor = crewColors[attendance.crew_id] || '#3498db';
            let adDate = moment(attendance.attendance_date).format('MMMM D, YYYY');
            let bsDate = attendance.nepali_date || adDate;
            
            let html = `
                <div class="container-fluid">
                    <!-- Status Bar -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center p-2" style="background: ${statusColor}20; border-left: 4px solid ${statusColor}; border-radius: 4px;">
                                <span><strong>Status:</strong> ${attendance.status ? attendance.status.toUpperCase().replace('_', ' ') : 'N/A'}</span>
                                <span class="badge" style="background: ${statusColor}; color: white; padding: 5px 10px;">${getStatusText(attendance.status)}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Crew Info -->
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fa fa-user"></i> Crew Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td style="width: 100px;"><strong>Name:</strong></td>
                                            <td>
                                                <span style="display: inline-block; width: 12px; height: 12px; background: ${crewColor}; border-radius: 3px; margin-right: 5px;"></span>
                                                ${escapeHtml(attendance.crew?.user?.name || attendance.crew_name || 'N/A')}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>${escapeHtml(attendance.crew?.user?.email || 'N/A')}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Phone:</strong></td>
                                            <td>${escapeHtml(attendance.crew?.user?.phone || 'N/A')}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Attendance Details -->
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fa fa-calendar"></i> Attendance Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td style="width: 100px;"><strong>AD Date:</strong></td>
                                            <td>${adDate}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>BS Date:</strong></td>
                                            <td class="nepali-date">${escapeHtml(bsDate)}</td>
                                        </tr>`;
            
            // Only show financial details if they have values (not null and not 0)
            let hasSalary = attendance.salary_amount && parseFloat(attendance.salary_amount) > 0;
            let hasBonus = attendance.bonus && parseFloat(attendance.bonus) > 0;
            let hasDeduction = attendance.deduction && parseFloat(attendance.deduction) > 0;
            let hasAllowances = attendance.allowances && parseFloat(attendance.allowances) > 0;
            
            if (hasSalary) {
                html += `
                        <tr>
                            <td><strong>Salary:</strong></td>
                            <td>Rs. ${parseFloat(attendance.salary_amount).toFixed(2)}</td>
                        </tr>`;
            }
            
            if (hasBonus) {
                html += `
                        <tr>
                            <td><strong>Bonus:</strong></td>
                            <td>Rs. ${parseFloat(attendance.bonus).toFixed(2)}</td>
                        </tr>`;
            }
            
            if (hasAllowances) {
                html += `
                        <tr style="background-color: #e8f5e9;">
                            <td><strong><i class="fa fa-money"></i> Allowances/Bhatta:</strong></td>
                            <td><strong class="text-success">Rs. ${parseFloat(attendance.allowances).toFixed(2)}</strong></td>
                        </tr>`;
            }
            
            if (hasDeduction) {
                html += `
                        <tr>
                            <td><strong>Deduction:</strong></td>
                            <td class="text-danger">Rs. ${parseFloat(attendance.deduction).toFixed(2)}</td>
                        </tr>`;
            }
            
            // Only show net amount if any financial values exist
            let hasNetAmount = attendance.net_amount && parseFloat(attendance.net_amount) > 0;
            if (hasNetAmount || hasSalary || hasBonus || hasAllowances) {
                let netAmount = attendance.net_amount || 
                    (parseFloat(attendance.salary_amount || 0) + 
                     parseFloat(attendance.bonus || 0) + 
                     parseFloat(attendance.allowances || 0) - 
                     parseFloat(attendance.deduction || 0));
                
                html += `
                        <tr style="border-top: 2px solid #dee2e6;">
                            <td><strong>Net Amount:</strong></td>
                            <td><strong class="text-primary">Rs. ${parseFloat(netAmount).toFixed(2)}</strong></td>
                        </tr>`;
            }
            
            if (attendance.remarks) {
                html += `
                        <tr>
                            <td><strong>Remarks:</strong></td>
                            <td>${escapeHtml(attendance.remarks)}</td>
                        </tr>`;
            }
            
            html += `</table>
                                </div>
                            </div>
                        </div>
                    </div>`;
            
            // Add Booking Info if exists
            if (attendance.booking && attendance.booking.id) {
                html += `
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fa fa-truck"></i> Related Booking</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td style="width: 120px;"><strong>Booking ID:</strong></td>
                                            <td>#${attendance.booking.id}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Vehicle:</strong></td>
                                            <td>${escapeHtml(attendance.booking.vehicle_name || 'N/A')}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Customer:</strong></td>
                                            <td>${escapeHtml(attendance.booking.customer_name || 'N/A')}</td>
                                        </tr>`;
                
                if (attendance.booking.start_date) {
                    html += `
                                        <tr>
                                            <td><strong>Date Range:</strong></td>
                                            <td>${attendance.booking.start_date} to ${attendance.booking.end_date}</td>
                                        </tr>`;
                }
                
                html += `</table>
                                </div>
                            </div>
                        </div>
                    </div>`;
            }
            
            // Add Vehicle Moment Info if exists
            if (attendance.vehicle_moment && attendance.vehicle_moment.id) {
                html += `
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fa fa-road"></i> Related Vehicle Moment</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td style="width: 120px;"><strong>Moment ID:</strong></td>
                                            <td>#${attendance.vehicle_moment.id}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Vehicle:</strong></td>
                                            <td>${escapeHtml(attendance.vehicle_moment.vehicle_name || 'N/A')}</td>
                                        </tr>`;
                
                if (attendance.vehicle_moment.moment_type) {
                    html += `
                                        <tr>
                                            <td><strong>Moment Type:</strong></td>
                                            <td>${escapeHtml(attendance.vehicle_moment.moment_type)}</td>
                                        </tr>`;
                }
                
                if (attendance.vehicle_moment.moment_date) {
                    html += `
                                        <tr>
                                            <td><strong>Date:</strong></td>
                                            <td>${attendance.vehicle_moment.moment_date}</td>
                                        </tr>`;
                }
                
                if (attendance.vehicle_moment.description) {
                    html += `
                                        <tr>
                                            <td><strong>Description:</strong></td>
                                            <td>${escapeHtml(attendance.vehicle_moment.description)}</td>
                                        </tr>`;
                }
                
                html += `</table>
                                </div>
                            </div>
                        </div>
                    </div>`;
            }
            
            html += `</div>`;
            
            $('#attendanceModalBody').html(html);
            $('#editAttendanceBtn').attr('href', `/dashboard/attendance/${attendance.id}/edit`);
        },
        error: function(xhr) {
            console.error('Error loading attendance:', xhr);
            let errorMsg = xhr.responseJSON?.message || 'Error loading attendance details';
            $('#attendanceModalBody').html(`
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i> 
                    ${escapeHtml(errorMsg)}
                </div>
            `);
        }
    });
}

function getStatusText(status) {
    const texts = {
        'present': 'Present',
        'absent': 'Absent',
        'half_day': 'Half Day',
        'holiday': 'Holiday',
        'leave': 'Leave'
    };
    return texts[status] || status;
}

function updateExportLink() {
    let params = {
        crew_id: $('#crewFilter').val(),
        status: $('#statusFilter').val(),
        start_date: $('#startDateFilter').val(),
        end_date: $('#endDateFilter').val()
    };
    
    Object.keys(params).forEach(key => {
        if (!params[key]) {
            delete params[key];
        }
    });
    
    $('#exportBtn').attr('href', "{{ route('admin.attendance.export') }}?" + $.param(params));
}

function deleteAttendance(id) {
    if (confirm('Are you sure you want to delete this attendance record? This action cannot be undone.')) {
        $.ajax({
            url: '/dashboard/attendance/' + id,
            type: "DELETE",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                toastr.success('Attendance deleted successfully');
                $(`tr[data-attendance-id="${id}"]`).fadeOut(500, function() {
                    $(this).remove();
                });
                if ($('#calendarView').is(':visible')) {
                    loadCalendarGrid();
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Error deleting attendance');
            }
        });
    }
}
function payByKhalti(attendanceId) {

    if (!confirm("Are you sure you want to pay this attendance allowance via Khalti?")) {
        return;
    }

    let btn = $("#khaltiBtn-" + attendanceId);

    btn.prop("disabled", true);
    btn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Processing...');

    $.ajax({
        url: "{{ route('admin.attendance.khalti.initiate') }}",
        type: "POST",
        data: {
            attendance_id: attendanceId,
            _token: "{{ csrf_token() }}"
        },

        success: function(response) {

            if (response.success) {
                window.location.href = response.payment_url;
            } else {
                toastr.error(response.message);
                resetButton();
            }
        },

        error: function(xhr) {
            toastr.error(xhr.responseJSON?.message || 'Payment initiation failed');
            resetButton();
        }
    });

    function resetButton() {
        btn.prop("disabled", false);
        btn.html('<i class="fas fa-credit-card mr-2"></i> Pay by Khalti');
    }
}


// Replace the existing selectProof, payManual functions with these updated versions:

function selectProof(attendanceId) {
    // Open modal for manual payment
    showManualPaymentModal(attendanceId);
}

function showManualPaymentModal(attendanceId) {
    // Remove existing modal if any
    if ($('#manualPaymentModal').length) {
        $('#manualPaymentModal').remove();
    }
    
    let modalHtml = `
        <div class="modal fade" id="manualPaymentModal" tabindex="-1" role="dialog" aria-labelledby="manualPaymentModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="manualPaymentModalLabel">
                            <i class="fas fa-money-bill-wave mr-2"></i> Manual Payment (Cash)
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="manualPaymentForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <input type="hidden" name="attendance_id" value="${attendanceId}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            
                            <!-- Optional File Upload -->
                            <div class="form-group">
                                <label for="paymentProof">
                                    <i class="fas fa-paperclip mr-1"></i> Payment Proof (Optional)
                                </label>
                                <div class="custom-file">
                                    <input type="file" 
                                           class="custom-file-input" 
                                           id="paymentProof" 
                                           name="proof" 
                                           accept=".jpg,.jpeg,.png,.pdf">
                                    <label class="custom-file-label" for="paymentProof">Choose file...</label>
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Upload receipt, screenshot, or any payment proof (JPG, PNG, PDF)
                                </small>
                            </div>
                            
                            
                            
                            <!-- Confirmation Notice -->
                            <div class="alert alert-info mt-3" role="alert">
                                <i class="fas fa-check-circle mr-2"></i>
                                By clicking "Confirm Payment", you confirm that the payment has been made and received.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times mr-1"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-success" id="confirmPaymentBtn">
                                <i class="fas fa-check-circle mr-1"></i> Confirm Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(modalHtml);
    
    // Initialize custom file input
    $('#paymentProof').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        if (fileName) {
            $(this).next('.custom-file-label').html(fileName);
        } else {
            $(this).next('.custom-file-label').html('Choose file...');
        }
    });
    
    // Handle form submission
    $('#manualPaymentForm').on('submit', function(e) {
        e.preventDefault();
        processManualPayment(attendanceId);
    });
    
    // Show modal
    $('#manualPaymentModal').modal('show');
}

function processManualPayment(attendanceId) {
    let btn = $("#confirmPaymentBtn");
    let formData = new FormData($('#manualPaymentForm')[0]);
    
    btn.prop("disabled", true);
    btn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Processing...');
    
    $.ajax({
        url: "{{ route('admin.attendance.manual.pay') }}",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        
        success: function(response) {
            if (response.success) {
                $('#manualPaymentModal').modal('hide');
                toastr.success('Payment marked as successful!');
                
                // Reload the page or update the UI
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            } else {
                toastr.error(response.message || 'Payment processing failed');
                resetButton();
            }
        },
        
        error: function(xhr) {
            let errorMsg = xhr.responseJSON?.message || 'Payment processing failed';
            toastr.error(errorMsg);
            resetButton();
        }
    });
    
    function resetButton() {
        btn.prop("disabled", false);
        btn.html('<i class="fas fa-check-circle mr-1"></i> Confirm Payment');
    }
}




function payByEsewa(attendanceId) {

    if (!confirm("Are you sure you want to pay via eSewa?")) {
        return;
    }

    $.ajax({
        url: "{{ route('admin.attendance.esewa.initiate') }}",
        type: "POST",
        data: {
            attendance_id: attendanceId,
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {

            if (!response.success) {
                toastr.error(response.message);
                return;
            }

            let form = `
                <form id="esewaForm" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">

                    <input type="hidden" name="amount" value="${response.amount}">
                    <input type="hidden" name="tax_amount" value="0">
                    <input type="hidden" name="total_amount" value="${response.amount}">
                    <input type="hidden" name="transaction_uuid" value="${response.transaction_uuid}">
                    <input type="hidden" name="product_code" value="EPAYTEST">
                    <input type="hidden" name="product_service_charge" value="0">
                    <input type="hidden" name="product_delivery_charge" value="0">
                    <input type="hidden" name="success_url" value="${response.success_url}">
                    <input type="hidden" name="failure_url" value="${response.failure_url}">
                    <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code">
                    <input type="hidden" name="signature" value="${response.signature}">

                </form>
            `;

            $('body').append(form);

            $('#esewaForm').submit();
        },
        error: function(xhr) {
            toastr.error(xhr.responseJSON?.message || 'Payment failed');
        }
    });
}


function submitEsewaForm(data) {

    let form = `
        <form id="esewaForm"
            action="https://rc-epay.esewa.com.np/api/epay/main/v2/form"
            method="POST">

            <input type="hidden" name="amount" value="${data.amount}">
            <input type="hidden" name="tax_amount" value="0">
            <input type="hidden" name="total_amount" value="${data.amount}">
            <input type="hidden" name="transaction_uuid" value="${data.transaction_uuid}">
            <input type="hidden" name="product_code" value="EPAYTEST">
            <input type="hidden" name="product_service_charge" value="0">
            <input type="hidden" name="product_delivery_charge" value="0">
            <input type="hidden" name="success_url" value="${data.success_url}">
            <input type="hidden" name="failure_url" value="${data.failure_url}">
            <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code">
            <input type="hidden" name="signature" value="${data.signature}">
        </form>
    `;

    $('body').append(form);

    $('#esewaForm').submit();
}


</script>
@endsection