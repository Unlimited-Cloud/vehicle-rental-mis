@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1>Vehicle Bookings</h1>
        <div>
            <button class="btn btn-outline-secondary btn-sm" onclick="showTable()">
                <i class="fa fa-list"></i> List View
            </button>

            <button class="btn btn-outline-success btn-sm" onclick="showCalendar()">
                <i class="fa fa-calendar"></i> Calendar View
            </button>

            <a href="{{ route('admin.vehicle_bookings.create') }}"
               class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Add Booking
            </a>

            <a id="exportBtn"
                href="{{ route('admin.vehicle_bookings.export') }}"
                class="btn btn-success btn-sm">
                <i class="fa fa-file-excel"></i> Export Excel
            </a>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">

@include('layouts.admin_theme.alert')

<div class="card card-primary card-outline">
<div class="card-body">

<!-- ================= FILTERS ================= -->
<div class="row mb-3">
    <div class="col-md-3">
        <select id="vehicleFilter" class="form-control">
            <option value="">All Vehicles</option>
            @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->vehicle_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <select id="customerFilter" class="form-control">
            <option value="">All Customers</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <select id="driverFilter" class="form-control">
            <option value="">All Drivers</option>
            @foreach($drivers as $driver)
                <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>{{ $driver->user->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <button class="btn btn-primary btn-sm" onclick="applyFilter()">
            <i class="fa fa-filter"></i> Apply
        </button>
        <button class="btn btn-secondary btn-sm" onclick="clearFilter()">
            <i class="fa fa-refresh"></i> Reset
        </button>
    </div>
</div>

<!-- ================= VEHICLE LEGEND ================= -->
<div id="vehicleLegend" class="mb-4">
    <strong>Vehicle Legend:</strong>
    <div class="d-flex flex-wrap mt-2">
        @foreach($vehicles as $vehicle)
            @php
                $color = '#'.substr(md5($vehicle->id),0,6);
            @endphp
            <div style="display:flex;align-items:center;margin-right:20px;margin-bottom:5px;">
                <div style="width:15px;height:15px; background:{{ $color }}; margin-right:6px;border-radius:3px;"></div>
                <span>{{ $vehicle->vehicle_name }}</span>
            </div>
        @endforeach
    </div>
</div>

<!-- ================= LIST VIEW ================= -->
<div id="tableView">
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered table-striped show-search-bar">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Vehicle</th>
                    <th>Customer</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Start Date (AD/BS)</th>
                    <th>End Date (AD/BS)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="bookingTableBody">
                @forelse($bookings as $i => $booking)
                    @php
                        $statusColor = $booking->status == 'confirmed' ? '#28a745' : 
                                      ($booking->status == 'pending' ? '#ffc107' : '#dc3545');
                    @endphp
                    <tr data-booking-id="{{ $booking->id }}" data-start-date="{{ $booking->start_date }}" data-end-date="{{ $booking->end_date }}">
                        <td>{{ $i+1 }}</td>
                        <td>
                            <div style="display: flex; align-items: center;">
                                <div style="width:12px;height:12px; background:#{{ substr(md5($booking->vehicle_id),0,6) }}; border-radius:3px; margin-right:6px;"></div>
                                {{ $booking->vehicle->vehicle_name ?? '' }}
                            </div>
                        </td>
                        <td>{{ $booking->customer->name ?? '' }}</td>
                        <td>{{ $booking->from_destination ?? '-' }}</td>
                        <td>{{ $booking->to_destination ?? '-' }}</td>
                        <td class="start-date-cell">
                            <span class="ad-date">{{ \Carbon\Carbon::parse($booking->start_date)->format('M d, Y') }}</span>
                            <br>
                            <small class="bs-date text-muted">Loading...</small>
                        </td>
                        <td class="end-date-cell">
                            <span class="ad-date">{{ \Carbon\Carbon::parse($booking->end_date)->format('M d, Y') }}</span>
                            <br>
                            <small class="bs-date text-muted">Loading...</small>
                        </td>
                        <td>
                            <span class="badge" style="background-color: {{ $statusColor }}; color: white; padding: 5px 10px;">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.vehicle_bookings.show', $booking->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.vehicle_bookings.edit', $booking->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-sm btn-danger" onclick="deleteBooking({{ $booking->id }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No bookings found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ================= CALENDAR GRID ================= -->
<div id="calendarView" style="display:none;">
    <!-- ================= MONTH NAVIGATION (Calendar View Only) ================= -->
    <div id="monthNav" class="mb-3 d-flex justify-content-between align-items-center" style="display: none;">
        <button class="btn btn-sm btn-outline-primary" onclick="changeMonth(-1)">
            <i class="fa fa-chevron-left"></i> Previous Month
        </button>
        <h4 id="currentMonth" class="mb-0">{{ date('F Y') }}</h4>
        <h5 id="currentNepaliMonth" class="mb-0 nepali-date" style="color:#198754; font-size:16px;">Loading...</h5>
        <button class="btn btn-sm btn-outline-primary" onclick="changeMonth(1)">
            Next Month <i class="fa fa-chevron-right"></i>
        </button>
    </div>
    <div class="mb-3">
        <small class="text-muted">Click on any empty cell to create a new booking. Click on booking block to view details.</small>
    </div>
    <div style="overflow-x:auto; max-height: 600px; overflow-y: auto;">
        <table class="table table-bordered booking-grid" style="min-width: 1200px;">
            <thead id="bookingGridHead" style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;"></thead>
            <tbody id="bookingGridBody"></tbody>
        </table>
    </div>
</div>

</div>
</div>
</div>
</section>

<!-- ================= BOOKING DETAILS MODAL ================= -->
<div class="modal fade" id="bookingDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa fa-calendar-check-o"></i> Booking Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="bookingModalBody">
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin fa-2x"></i> Loading...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fa fa-times"></i> Close
                </button>
                <a href="#" id="viewBookingBtn" class="btn btn-info btn-sm" target="_blank">
                    <i class="fa fa-eye"></i> View Full Details
                </a>
                <a href="#" id="editBookingBtn" class="btn btn-primary btn-sm" target="_blank">
                    <i class="fa fa-edit"></i> Edit Booking
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
    /* Add to your styles */
@import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap');

.nepali-date {
    font-family: 'Hind Siliguri', 'Preeti', 'Arial Unicode MS', sans-serif;
    font-size: 11px;
    color: #198754;
    font-weight: 500;
    line-height: 1.3;
}

/* For the calendar header */
.booking-grid th .nepali-date {
    font-size: 10px;
    white-space: nowrap;
}

/* For table view */
.bs-date {
    font-family: 'Hind Siliguri', 'Preeti', sans-serif;
    font-size: 11px;
    color: #198754;
}
    .booking-grid th, .booking-grid td {
        min-width: 80px;
        text-align: center;
        font-size: 12px;
        padding: 8px 4px;
        vertical-align: middle;
        border: 1px solid #dee2e6;
    }
    
    .vehicle-column {
        min-width: 180px;
        font-weight: 600;
        background: #f8f9fa;
        position: sticky;
        left: 0;
        z-index: 5;
        border-right: 2px solid #dee2e6;
    }
    
    .booking-block {
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
    
    .booking-block:hover {
        opacity: 0.9;
        transform: scale(1.02);
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
    
    .booking-block i {
        margin-right: 3px;
    }
    
    .days-badge {
        background: #28a745;
        color: #fff;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
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
    
    .modal-lg {
        max-width: 700px;
    }
    
    .detail-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .detail-table td {
        padding: 10px;
        border: 1px solid #dee2e6;
    }
    
    .detail-table td:first-child {
        font-weight: 600;
        background: #f8f9fa;
        width: 140px;
    }
    
    .nepali-date {
        font-family: 'Hind Siliguri', 'Preeti', 'Arial Unicode MS', sans-serif;
        font-size: 12px;
        color: #198754;
        font-weight: 600;
    }
    
    .bs-date {
        font-size: 11px;
        color: #198754;
        font-family: 'Hind Siliguri', 'Preeti', sans-serif;
    }
    
    .ad-date {
        font-size: 12px;
        font-weight: 500;
    }

</style>

<script>
let currentMonth = moment();
let allVehicles = @json($vehicles);
let vehicleColors = {};
let dateCache = {}; // Cache for converted dates

// Generate consistent colors for vehicles
@foreach($vehicles as $vehicle)
    vehicleColors[{{ $vehicle->id }}] = '#{{ substr(md5($vehicle->id),0,6) }}';
@endforeach

// Status colors
const statusColors = {
    'confirmed': '#28a745',
    'pending': '#ffc107',
    'cancelled': '#dc3545'
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
    if (urlParams.has('vehicle_id')) {
        $('#vehicleFilter').val(urlParams.get('vehicle_id'));
    }
    if (urlParams.has('customer_id')) {
        $('#customerFilter').val(urlParams.get('customer_id'));
    }
    if (urlParams.has('driver_id')) {
        $('#driverFilter').val(urlParams.get('driver_id'));
    }
    
    // Load Nepali dates for table view
    loadNepaliDatesForTable();
});


// Update the convertToNepaliDate function to handle the response correctly
// Function to convert date to Nepali using your route
function convertToNepaliDate(adDate) {
    return new Promise((resolve, reject) => {
        // Check cache first
        if (dateCache[adDate]) {
            resolve(dateCache[adDate]);
            return;
        }
        
        $.ajax({
            url: "{{ route('admin.vehicle_bookings.convert_ad_to_bs') }}",
            type: 'POST',
            data: {
                date: adDate,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.success) {
                    // Extract just the Nepali part from "February, 27 2026 | २०८२ फागुन १५"
                    let nepaliOnly = '';
                    if (response.display && response.display.includes('|')) {
                        // Split by | and take the second part, then trim
                        nepaliOnly = response.display.split('|')[1].trim();
                    } else if (response.nepali) {
                        nepaliOnly = response.nepali;
                    } else {
                        nepaliOnly = response.display || '';
                    }
                    
                    // Also extract just the day number for calendar header
                    let dayOnly = '';
                    if (nepaliOnly) {
                        let parts = nepaliOnly.split(' ');
                        dayOnly = parts.length >= 3 ? parts[2] : (parts.length >= 2 ? parts[1] : '');
                    }
                    
                    // Cache the result
                    dateCache[adDate] = {
                        nepali: response.nepali || '',
                        day: response.day || dayOnly,
                        month: response.month || '',
                        year: response.year || '',
                        formatted: response.formatted || '',
                        display: nepaliOnly, // Now just "२०८२ फागुन १५"
                        day_only: dayOnly,    // Just "१५"
                        full_response: response
                    };
                    resolve(dateCache[adDate]);
                } else {
                    reject('Conversion failed');
                }
            },
            error: function(xhr, status, error) {
                console.error('Date conversion error:', error);
                // Fallback to approximate conversion
                let date = new Date(adDate);
                let nepYear = date.getFullYear() + 57;
                let nepMonth = date.getMonth() + 9;
                let nepDay = date.getDate();
                
                if (nepMonth > 12) {
                    nepMonth -= 12;
                    nepYear += 1;
                }
                
                // Get Nepali month name
                const monthNames = {
                    1: 'बैशाख', 2: 'जेठ', 3: 'असार', 4: 'साउन',
                    5: 'भदौ', 6: 'असोज', 7: 'कात्तिक', 8: 'मंसिर',
                    9: 'पुस', 10: 'माघ', 11: 'फागुन', 12: 'चैत'
                };
                let nepaliMonthName = monthNames[nepMonth] || 'बैशाख';
                
                // Convert to Nepali numbers
                const nepaliNumbers = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];
                let nepaliYearStr = nepYear.toString().split('').map(d => nepaliNumbers[parseInt(d)]).join('');
                let nepaliDayStr = nepDay.toString().split('').map(d => nepaliNumbers[parseInt(d)]).join('');
                
                let fallbackDate = {
                    nepali: `${nepaliYearStr} ${nepaliMonthName} ${nepaliDayStr}`,
                    day: nepaliDayStr,
                    month: nepMonth.toString().padStart(2, '0'),
                    year: nepYear.toString(),
                    formatted: `${nepYear}/${nepMonth.toString().padStart(2, '0')}/${nepDay.toString().padStart(2, '0')}`,
                    display: `${nepaliYearStr} ${nepaliMonthName} ${nepaliDayStr}`,
                    day_only: nepaliDayStr
                };
                dateCache[adDate] = fallbackDate;
                resolve(fallbackDate);
            }
        });
    });
}
// Update the table view to show Nepali dates correctly
async function loadNepaliDatesForTable() {
    $('#bookingTableBody tr').each(async function() {
        let $row = $(this);
        let startDate = $row.data('start-date');
        let endDate = $row.data('end-date');
        
        if (startDate) {
            try {
                let bsDate = await convertToNepaliDate(startDate);
                // Show format: "२०८२ फागुन १५"
                $row.find('.start-date-cell .bs-date').text(bsDate.display);
            } catch (e) {
                console.error('Error converting start date:', e);
            }
        }
        
        if (endDate) {
            try {
                let bsDate = await convertToNepaliDate(endDate);
                // Show format: "२०८२ फागुन १५"
                $row.find('.end-date-cell .bs-date').text(bsDate.display);
            } catch (e) {
                console.error('Error converting end date:', e);
            }
        }
    });
}

// View Toggle Functions
function showTable() {
    $('#tableView').show();
    $('#calendarView').hide();
    $('#monthNav').hide();
    
    // Update URL to reflect list view
    let url = new URL(window.location.href);
    url.searchParams.set('view', 'list');
    window.history.replaceState({}, '', url);
}

function showCalendar() {
    $('#tableView').hide();
    $('#calendarView').show();
    $('#monthNav').show();
    
    // Update URL to reflect calendar view
    let url = new URL(window.location.href);
    url.searchParams.set('view', 'calendar');
    window.history.replaceState({}, '', url);
      updateNepaliMonthDisplay();
    loadCalendarGrid();
}

// Filter Functions
function applyFilter() {
    if ($('#calendarView').is(':visible')) {
        loadCalendarGrid();
    } else {
        // Reload page with filters for list view
        let url = new URL(window.location.href);
        url.searchParams.set('vehicle_id', $('#vehicleFilter').val());
        url.searchParams.set('customer_id', $('#customerFilter').val());
        url.searchParams.set('driver_id', $('#driverFilter').val());
        url.searchParams.set('view', 'list');
        window.location.href = url.toString();
    }
}

function clearFilter() {
    $('#vehicleFilter, #customerFilter, #driverFilter').val('');
    if ($('#calendarView').is(':visible')) {
        loadCalendarGrid();
    } else {
        window.location.href = "{{ route('admin.vehicle_bookings.index') }}?view=list";
    }
}

// Update month navigation with Nepali date
async function updateNepaliMonthDisplay() {
    // Get first and last day of the current month
    let firstDate = currentMonth.clone().startOf('month').format('YYYY-MM-DD');
    let lastDate = currentMonth.clone().endOf('month').format('YYYY-MM-DD');
    
    try {
        // Get Nepali dates
        let bsFirstDate = await convertToNepaliDate(firstDate);
        let bsLastDate = await convertToNepaliDate(lastDate);

        if (bsFirstDate && bsLastDate) {
            // Extract month and year from the full response
            let firstMonth = bsFirstDate.month || '';
            let lastMonth = bsLastDate.month || '';
            let nepaliYear = bsFirstDate.year || '';
            
            if (firstMonth && lastMonth) {
                if (firstMonth === lastMonth) {
                    $('#currentNepaliMonth').text(`${firstMonth} ${nepaliYear}`);
                } else {
                    $('#currentNepaliMonth').text(`${firstMonth}/${lastMonth} ${nepaliYear}`);
                }
            } else {
                // Fallback to display if month names not available
                let firstParts = bsFirstDate.display.split(' ');
                let lastParts = bsLastDate.display.split(' ');
                let firstMonthName = firstParts.length >= 2 ? firstParts[1] : '';
                let lastMonthName = lastParts.length >= 2 ? lastParts[1] : '';
                let year = firstParts.length >= 1 ? firstParts[0] : '';
                
                if (firstMonthName && lastMonthName) {
                    if (firstMonthName === lastMonthName) {
                        $('#currentNepaliMonth').text(`${firstMonthName} ${year}`);
                    } else {
                        $('#currentNepaliMonth').text(`${firstMonthName}/${lastMonthName} ${year}`);
                    }
                }
            }
        }

    } catch (e) {
        console.error('Error loading Nepali month:', e);
        $('#currentNepaliMonth').text('');
    }
}

// Month Navigation
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

// Load Calendar Grid
function loadCalendarGrid() {
    $('#currentMonth').text(currentMonth.format('MMMM YYYY'));
    
    $.ajax({
        url: "{{ route('admin.vehicle_bookings.events') }}",
        type: "GET",
        data: {
            vehicle_id: $('#vehicleFilter').val(),
            customer_id: $('#customerFilter').val(),
            driver_id: $('#driverFilter').val(),
            month: currentMonth.format('YYYY-MM')
        },
        success: function(bookings) {
            let startDate = currentMonth.clone().startOf('month');
            let endDate = currentMonth.clone().endOf('month');
            
            buildCalendarHeader(startDate, endDate);
            buildCalendarBody(bookings, startDate, endDate);
        },
        error: function() {
            toastr.error('Error loading calendar data');
        }
    });
}

async function buildCalendarHeader(startDate, endDate) {

    let adRow = '<tr><th class="vehicle-column">Vehicle / Date</th>';
    let bsRow = '<tr><th class="vehicle-column">वाहन / मिति</th>';

    let current = startDate.clone();
    let today = moment().format('YYYY-MM-DD');
    let dates = [];

    while (current <= endDate) {
        dates.push(current.format('YYYY-MM-DD'));
        current.add(1, 'day');
    }

    // 🔥 ONE SINGLE REQUEST
    let response = await $.ajax({
        url: "{{ route('admin.vehicle_bookings.convert_multiple_ad_to_bs') }}",
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

        adRow += `
            <th style="${isToday ? 'background:#cfe2ff;' : ''}">
                ${current.format('D')}
            </th>
        `;

        bsRow += `
            <th style="${isToday ? 'background:#cfe2ff;' : ''}">
                ${nepaliDay}
            </th>
        `;

        current.add(1, 'day');
    }

    adRow += '<th>Total Days</th></tr>';
    bsRow += '<th>जम्मा दिन</th></tr>';

    $('#bookingGridHead').html(adRow + bsRow);
}

// Build Calendar Body
function buildCalendarBody(bookings, startDate, endDate) {
    let html = '';
    let today = moment().format('YYYY-MM-DD');
    
    allVehicles.forEach(vehicle => {
        let totalDays = 0;
        html += `<tr data-vehicle-id="${vehicle.id}">`;
        html += `<td class="vehicle-column">
                    <div style="display: flex; align-items: center; padding: 5px;">
                        <div style="width:15px;height:15px; background:${vehicleColors[vehicle.id]}; border-radius:3px; margin-right:8px;"></div>
                        <span style="font-weight: 600;">${vehicle.vehicle_name}</span>
                    </div>
                </td>`;

        let current = startDate.clone();
        
        while (current <= endDate) {
            let currentDateStr = current.format('YYYY-MM-DD');
            let isToday = currentDateStr === today;
            
            // Find bookings for this vehicle on this date
            let dayBookings = bookings.filter(b => 
                b.extendedProps.vehicle_id == vehicle.id &&
                moment(currentDateStr).isBetween(
                    moment(b.start), 
                    moment(b.end).subtract(1, 'day'), 
                    null, 
                    '[]'
                )
            );

            if (dayBookings.length > 0) {
                // Show booking(s)
                totalDays++;
                html += `<td class="${isToday ? 'today-cell' : ''}" style="padding: 2px;">`;
                
                dayBookings.forEach((booking, index) => {
                    let statusColor = statusColors[booking.extendedProps.status] || '#6c757d';
                    let customerName = booking.extendedProps.customer_name || 'N/A';
                    let displayName = customerName.length > 8 ? customerName.substring(0, 6) + '..' : customerName;
                    
                    html += `
                        <div class="booking-block" 
                            style="background: ${vehicleColors[vehicle.id]}; border-left: 4px solid ${statusColor};"
                            onclick="event.stopPropagation(); openBookingModal(${booking.id})"
                            title="${booking.title} - ${booking.extendedProps.status}">
                            <i class="fa fa-user"></i> ${displayName}
                        </div>`;
                });
                
                html += `</td>`;
            } else {
                // Empty cell - click to create booking
                html += `<td class="calendar-empty-cell ${isToday ? 'today-cell' : ''}" 
                            onclick="openCreateBooking(${vehicle.id}, '${currentDateStr}')"
                            style="cursor: pointer;"
                            title="Click to create booking for ${vehicle.vehicle_name} on ${currentDateStr}">
                            &nbsp;
                        </td>`;
            }
            
            current.add(1, 'day');
        }
        
        html += `<td><span class="days-badge">${totalDays} day${totalDays !== 1 ? 's' : ''}</span></td>`;
        html += `</tr>`;
    });
    
    $('#bookingGridBody').html(html);
}

// Open create booking page
function openCreateBooking(vehicleId, date) {
    let url = "{{ route('admin.vehicle_bookings.create') }}?vehicle_id=" + vehicleId + "&start=" + date + "&end=" + date;
    window.location.href = url;
}

// Open booking details modal
async function openBookingModal(bookingId) {
    // Show loading
    $('#bookingModalBody').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i><p class="mt-2">Loading booking details...</p></div>');
    $('#bookingDetailsModal').modal('show');
    
    // Fetch booking details
    $.ajax({
        url: `/dashboard/vehicle_bookings/${bookingId}`,
        type: "GET",
        success: async function(booking) {
            let statusColor = statusColors[booking.status] || '#6c757d';
            let vehicleColor = vehicleColors[booking.vehicle_id] || '#3498db';
            
            let startDate = moment(booking.start_date).format('MMMM D, YYYY');
            let endDate = moment(booking.end_date).format('MMMM D, YYYY');
            let duration = moment(booking.end_date).diff(moment(booking.start_date), 'days') + 1;
            
            // Get Nepali dates
            let bsStartDate = await convertToNepaliDate(booking.start_date);
            let bsEndDate = await convertToNepaliDate(booking.end_date);
            
            let html = `
                <div class="container-fluid">
                    <!-- Status Bar -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center p-2" style="background: ${statusColor}20; border-left: 4px solid ${statusColor};">
                                <span><strong>Status:</strong> ${booking.status ? booking.status.toUpperCase() : 'N/A'}</span>
                                <span class="badge" style="background: ${statusColor}; color: white;">${duration} Day${duration > 1 ? 's' : ''}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Vehicle Info -->
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fa fa-truck"></i> Vehicle Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td style="width: 100px;"><strong>Vehicle:</strong></td>
                                            <td>
                                                <span style="display: inline-block; width: 12px; height: 12px; background: ${vehicleColor}; border-radius: 3px; margin-right: 5px;"></span>
                                                ${booking.vehicle.vehicle_name}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Driver:</strong></td>
                                            <td>${booking.driver ? booking.driver.user.name : '<span class="text-muted">Not Assigned</span>'}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Helper:</strong></td>
                                            <td>${booking.helper ? booking.helper.user.name : '<span class="text-muted">Not Assigned</span>'}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Customer Info -->
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fa fa-user"></i> Customer Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td style="width: 100px;"><strong>Name:</strong></td>
                                            <td>${booking.customer ? booking.customer.name : 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>${booking.customer ? booking.customer.email : 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Phone:</strong></td>
                                            <td>${booking.customer ? booking.customer.phone : 'N/A'}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Trip Details -->
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fa fa-map-marker"></i> Trip Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td style="width: 100px;"><strong>From:</strong></td>
                                                    <td>${booking.from_destination || 'N/A'}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>To:</strong></td>
                                                    <td>${booking.to_destination || 'N/A'}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td style="width: 100px;"><strong>Start Date:</strong></td>
                                                    <td>
                                                        <div>${startDate}</div>
                                                        <small class="nepali-date">${bsStartDate.display}</small>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>End Date:</strong></td>
                                                    <td>
                                                        <div>${endDate}</div>
                                                        <small class="nepali-date">${bsEndDate.display}</small>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Info -->
                    ${booking.notes ? `
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fa fa-sticky-note"></i> Notes</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">${booking.notes}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    
                    <!-- KM and Fuel Info (if available) -->
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h4>${booking.start_km || 'N/A'}</h4>
                                    <p>Start KM</p>
                                </div>
                                <div class="icon">
                                    <i class="fa fa-tachometer"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h4>${booking.end_km || 'N/A'}</h4>
                                    <p>End KM</p>
                                </div>
                                <div class="icon">
                                    <i class="fa fa-tachometer"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h4>${booking.approx_fuel_litre || 'N/A'} L</h4>
                                    <p>Fuel Used</p>
                                </div>
                                <div class="icon">
                                    <i class="fa fa-fire"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#bookingModalBody').html(html);
            $('#viewBookingBtn').attr('href', `/dashboard/vehicle_bookings/${booking.id}`);
            $('#editBookingBtn').attr('href', `/dashboard/vehicle_bookings/${booking.id}/edit`);
        },
        error: function(xhr) {
            $('#bookingModalBody').html(`
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i> 
                    Error loading booking details: ${xhr.responseJSON?.message || 'Unknown error'}
                </div>
            `);
        }
    });
}

function updateExportLink() {
    let params = $.param({
        vehicle_id: $('#vehicleFilter').val(),
        customer_id: $('#customerFilter').val(),
        driver_id: $('#driverFilter').val()
    });
    $('#exportBtn').attr('href', "{{ route('admin.vehicle_bookings.export') }}?" + params);
}

// Delete booking function
function deleteBooking(id) {
    if (confirm('Are you sure you want to delete this booking? This action cannot be undone.')) {
        $.ajax({
            url: '/dashboard/vehicle_bookings/' + id,
            type: "DELETE",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                toastr.success('Booking deleted successfully');
                $(`tr[data-booking-id="${id}"]`).fadeOut(500, function() {
                    $(this).remove();
                });
                if ($('#calendarView').is(':visible')) {
                    loadCalendarGrid();
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Error deleting booking');
            }
        });
    }
}
</script>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true
    });
});
</script>
@endpush