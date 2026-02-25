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
            <a href="{{ route('admin.vehicle_bookings.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Add Booking
            </a>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @include('layouts.admin_theme.alert')

        <div class="card card-primary card-outline">
            <div class="card-body">
                <!-- Filter Section -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select id="vehicleFilter" class="form-control">
                            <option value="">All Vehicles</option>
                            @foreach($vehicles as $vehicle)
                                @php
                                    // Assign consistent colors based on vehicle ID
                                    $colors = [
                                        1 => '#3498db', // Blue
                                        2 => '#e74c3c', // Red
                                        3 => '#2ecc71', // Green
                                        4 => '#f39c12', // Orange
                                        5 => '#9b59b6', // Purple
                                        6 => '#1abc9c', // Turquoise
                                        7 => '#e67e22', // Carrot
                                        8 => '#34495e', // Dark Blue
                                        9 => '#16a085', // Green Sea
                                        10 => '#27ae60', // Nephritis
                                        11 => '#2980b9', // Belize Hole
                                        12 => '#8e44ad', // Wisteria
                                        13 => '#2c3e50', // Midnight Blue
                                        14 => '#d35400', // Pumpkin
                                        15 => '#c0392b', // Pomegranate
                                    ];
                                    $colorCode = $colors[$vehicle->id] ?? '#3498db';
                                @endphp
                                <option value="{{ $vehicle->id }}" style="background-color: {{ $colorCode }}20; border-left: 5px solid {{ $colorCode }};">
                                    {{ $vehicle->vehicle_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm" onclick="applyFilter()">
                            <i class="fa fa-filter"></i> Apply Filter
                        </button>
                    </div>
                </div>

                <!-- Vehicle Legend with Consistent Colors -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h6 class="text-muted mb-2">VEHICLES LEGEND</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($vehicles as $vehicle)
                                @php
                                    $colors = [
                                        1 => '#3498db', 2 => '#e74c3c', 3 => '#2ecc71', 4 => '#f39c12', 
                                        5 => '#9b59b6', 6 => '#1abc9c', 7 => '#e67e22', 8 => '#34495e',
                                        9 => '#16a085', 10 => '#27ae60', 11 => '#2980b9', 12 => '#8e44ad',
                                        13 => '#2c3e50', 14 => '#d35400', 15 => '#c0392b',
                                    ];
                                    $colorCode = $colors[$vehicle->id] ?? '#3498db';
                                @endphp
                                <div class="vehicle-legend-item" style="display: inline-flex; align-items: center; margin-right: 20px; margin-bottom: 10px;">
                                    <div style="width: 20px; height: 20px; background-color: {{ $colorCode }}; border-radius: 4px; margin-right: 8px;"></div>
                                    <span style="font-weight: 500;">{{ $vehicle->vehicle_name }}</span>
                                    {{-- <span class="text-muted ml-2" style="font-size: 11px;">(ID: {{ $vehicle->id }})</span> --}}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Table View -->
                <div id="tableView">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Vehicle</th>
                                <th>Customer</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="bookingTableBody">
                            @foreach($bookings as $index => $booking)
                                @php
                                    $colors = [
                                        1 => '#3498db', 2 => '#e74c3c', 3 => '#2ecc71', 4 => '#f39c12',
                                        5 => '#9b59b6', 6 => '#1abc9c', 7 => '#e67e22', 8 => '#34495e',
                                        9 => '#16a085', 10 => '#27ae60', 11 => '#2980b9', 12 => '#8e44ad',
                                        13 => '#2c3e50', 14 => '#d35400', 15 => '#c0392b',
                                    ];
                                    $colorCode = $colors[$booking->vehicle_id] ?? '#3498db';
                                    
                                    // Status colors
                                    $statusColor = $booking->status == 'confirmed' ? '#28a745' : 
                                                  ($booking->status == 'pending' ? '#ffc107' : '#dc3545');
                                @endphp
                                <tr data-vehicle-id="{{ $booking->vehicle_id }}" data-booking-id="{{ $booking->id }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div style="display: flex; align-items: center;">
                                            <div style="width: 15px; height: 15px; background-color: {{ $colorCode }}; border-radius: 3px; margin-right: 8px;"></div>
                                            <span>{{ $booking->vehicle->vehicle_name ?? '' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $booking->customer_name }}</td>
                                    <td>{{ $booking->from_destination ?? '-' }}</td>
                                    <td>{{ $booking->to_destination ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($booking->start_date)->format('M d, Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($booking->end_date)->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $statusColor }}; color: white; padding: 5px 10px;">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.vehicle_bookings.edit', $booking->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="deleteBooking({{ $booking->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Calendar View -->
                <div id="calendarView" style="display: none;">
                    <div id="calendar" style="background: #fff; padding: 15px; border-radius: 5px; min-height: 600px;"></div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<!-- Required Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<style>
    .fc-event { 
        cursor: pointer; 
        border-radius: 4px; 
        padding: 2px 4px !important;
        border-left-width: 4px !important;
    }
    .fc-event-title { 
        font-weight: bold; 
        font-size: 12px; 
    }
    .fc-day-today { 
        background-color: #f8f9fa !important; 
    }
    .fc-event:hover { 
        opacity: 0.9; 
        transform: scale(1.01); 
        transition: all 0.2s; 
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    #calendar.loading { 
        opacity: 0.5; 
        pointer-events: none; 
    }
    .vehicle-legend-item {
        padding: 5px 10px;
        background-color: #f8f9fa;
        border-radius: 20px;
        border: 1px solid #dee2e6;
    }
    .vehicle-legend-item:hover {
        transform: translateY(-2px);
        transition: all 0.2s;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .badge {
        padding: 5px 10px;
        font-weight: 500;
    }
    .fc-daygrid-event {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<script>
$(document).ready(function() {
    let calendar;
    let calendarInitialized = false;
    
    // Fixed vehicle colors matching PHP
    const vehicleColors = {
        @foreach($vehicles as $vehicle)
            {{ $vehicle->id }}: '{{ $colors[$vehicle->id] ?? '#3498db' }}',
        @endforeach
    };

    // Status colors
    const statusColors = {
        'confirmed': '#28a745',
        'pending': '#ffc107',
        'cancelled': '#dc3545'
    };

    // View Toggle Functions
    window.showTable = function() {
        $('#tableView').show();
        $('#calendarView').hide();
    };

    window.showCalendar = function() {
        $('#tableView').hide();
        $('#calendarView').show();

        if (!calendarInitialized) {
            initCalendar();
            calendarInitialized = true;
        } else {
            calendar.refetchEvents();
        }
    };

    window.applyFilter = function() {
        let vehicleId = $('#vehicleFilter').val();
        
        // Filter table
        if(vehicleId == '') {
            $('#bookingTableBody tr').show();
        } else {
            $('#bookingTableBody tr').hide();
            $('#bookingTableBody tr[data-vehicle-id="'+vehicleId+'"]').show();
        }
        
        // Filter calendar
        if (calendarInitialized) {
            calendar.refetchEvents();
        }
    };

    function initCalendar() {
        let calendarEl = document.getElementById('calendar');

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 'auto',
            selectable: true,
            editable: true,
            
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            
            events: function(fetchInfo, successCallback, failureCallback) {
                $.ajax({
                    url: "{{ route('admin.vehicle_bookings.events') }}",
                    type: "GET",
                    data: {
                        vehicle_id: $('#vehicleFilter').val()
                    },
                    success: function(response) {
                        const events = response.map(event => {
                            const vehicleId = event.extendedProps?.vehicle_id;
                            return {
                                ...event,
                                // Use vehicle color from our fixed mapping
                                backgroundColor: vehicleColors[vehicleId] || '#3498db',
                                borderColor: statusColors[event.extendedProps?.status] || '#6c757d',
                                textColor: '#ffffff',
                                title: event.title
                            };
                        });
                        successCallback(events);
                    },
                    error: function() {
                        toastr.error('Error loading calendar events');
                        failureCallback();
                    }
                });
            },
            
            select: function(info) {
                let start = moment(info.startStr).format('YYYY-MM-DD');
                let end = moment(info.endStr).subtract(1, 'day').format('YYYY-MM-DD');
                window.location.href = "{{ route('admin.vehicle_bookings.create') }}?start=" + start + "&end=" + end;
            },
            
            eventDrop: function(info) {
                if (confirm('Move this booking?')) {
                    updateEvent(info.event);
                } else {
                    info.revert();
                }
            },
            
            eventResize: function(info) {
                if (confirm('Change booking duration?')) {
                    updateEvent(info.event);
                } else {
                    info.revert();
                }
            },
            
            eventClick: function(info) {
                showBookingDetails(info.event);
            },
            
            eventDidMount: function(info) {
                // Add a small status indicator
                let status = info.event.extendedProps?.status;
                if (status === 'pending') {
                    info.el.style.borderLeftWidth = '6px';
                } else if (status === 'cancelled') {
                    info.el.style.opacity = '0.7';
                    info.el.style.textDecoration = 'line-through';
                }
            }
        });

        calendar.render();
    }

    function updateEvent(event) {
        let startDate = moment(event.start).format('YYYY-MM-DD');
        let endDate = event.end ? moment(event.end).subtract(1, 'day').format('YYYY-MM-DD') : startDate;
        
        let url = '/dashboard/vehicle_bookings/' + event.id;
        
        $.ajax({
            url: url,
            type: "PUT",
            data: {
                _token: "{{ csrf_token() }}",
                start_date: startDate,
                end_date: endDate
            },
            success: function() {
                toastr.success("Booking updated");
            },
            error: function() {
                toastr.error("Update failed");
                calendar.refetchEvents();
            }
        });
    }

    window.deleteBooking = function(id) {
        if (confirm('Are you sure you want to delete this booking?')) {
            let url = '/dashboard/vehicle_bookings/' + id;
            
            $.ajax({
                url: url,
                type: "DELETE",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    toastr.success('Booking deleted');
                    
                    $(`tr[data-booking-id="${id}"]`).fadeOut(500, function() {
                        $(this).remove();
                    });
                    
                    if (calendar) {
                        calendar.refetchEvents();
                    }
                },
                error: function(xhr) {
                    let message = 'Delete failed';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    toastr.error(message);
                }
            });
        }
    };

 function showBookingDetails(event) {
    let props = event.extendedProps || {};
    let startDate = moment(event.start).format('YYYY-MM-DD');
    let endDate = event.end ? moment(event.end).subtract(1, 'day').format('YYYY-MM-DD') : startDate;
    
    let statusColor = statusColors[props.status] || '#6c757d';
    let vehicleColor = vehicleColors[props.vehicle_id] || '#3498db';
    
    // Remove any existing modal
    $('#bookingDetailsModal').remove();
    
    // Create modal HTML with simple close button that calls a function
    let modalHtml = `
        <div class="modal fade" id="bookingDetailsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
                <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                    <!-- Header -->
                    <div class="modal-header" style="border-bottom: 1px solid #edf2f7; padding: 15px 20px; background: #f8fafc;">
                        <h5 class="modal-title" style="font-weight: 600; color: #1e293b; font-size: 16px;">
                            <i class="fa fa-calendar-check-o mr-2" style="color: ${vehicleColor};"></i> 
                            Booking Details
                        </h5>
                        <button type="button" class="btn-close" onclick="closeBookingModal()" aria-label="Close" style="font-size: 12px; cursor: pointer;"></button>
                    </div>
                    
                    <!-- Body -->
                    <div class="modal-body" style="padding: 20px;">
                        <!-- Vehicle & Status Row -->
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 12px; height: 12px; background-color: ${vehicleColor}; border-radius: 3px;"></div>
                                <span style="font-weight: 600; color: #1e293b;">${props.vehicle_name || 'N/A'}</span>
                            </div>
                            <span style="background-color: ${statusColor}; color: white; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                                ${props.status ? props.status.toUpperCase() : 'N/A'}
                            </span>
                        </div>
                        
                        <!-- Customer Info -->
                        <div style="background: #f8fafc; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                            <div style="display: grid; grid-template-columns: 80px 1fr; gap: 8px; font-size: 13px;">
                                <span style="color: #64748b;">Customer:</span>
                                <span style="color: #1e293b; font-weight: 500;">${props.customer_name || 'N/A'}</span>
                                
                                <span style="color: #64748b;">Email:</span>
                                <span style="color: #2563eb;">${props.customer_email || 'N/A'}</span>
                                
                                <span style="color: #64748b;">Phone:</span>
                                <span style="color: #1e293b;">${props.customer_phone || 'N/A'}</span>
                            </div>
                        </div>
                        
                        <!-- Trip Info -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                            <div style="background: #f8fafc; border-radius: 8px; padding: 10px;">
                                <div style="color: #64748b; font-size: 11px; margin-bottom: 4px;">FROM</div>
                                <div style="color: #1e293b; font-weight: 500; font-size: 13px;">${props.from_destination || 'N/A'}</div>
                            </div>
                            <div style="background: #f8fafc; border-radius: 8px; padding: 10px;">
                                <div style="color: #64748b; font-size: 11px; margin-bottom: 4px;">TO</div>
                                <div style="color: #1e293b; font-weight: 500; font-size: 13px;">${props.to_destination || 'N/A'}</div>
                            </div>
                        </div>
                        
                        <!-- Dates -->
                        <div style="background: #f8fafc; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="color: #64748b; font-size: 11px;">START</div>
                                    <div style="color: #1e293b; font-weight: 500; font-size: 13px;">${startDate}</div>
                                </div>
                                <div style="color: #94a3b8;">→</div>
                                <div>
                                    <div style="color: #64748b; font-size: 11px;">END</div>
                                    <div style="color: #1e293b; font-weight: 500; font-size: 13px;">${endDate}</div>
                                </div>
                                <div style="background: #e2e8f0; padding: 4px 8px; border-radius: 20px;">
                                    <span style="color: #475569; font-size: 11px; font-weight: 600;">${moment(endDate).diff(moment(startDate), 'days') + 1}d</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notes if any -->
                        ${props.notes ? `
                        <div style="background: #fff7ed; border-radius: 8px; padding: 10px;">
                            <div style="color: #9a3412; font-size: 11px; margin-bottom: 4px;">NOTES</div>
                            <div style="color: #7b341e; font-size: 12px;">${props.notes}</div>
                        </div>
                        ` : ''}
                    </div>
                    
                    <!-- Footer -->
                    <div class="modal-footer" style="border-top: 1px solid #edf2f7; padding: 12px 20px; background: #f8fafc;">
                        <div style="display: flex; gap: 8px; justify-content: flex-end; width: 100%;">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="closeBookingModal()" style="padding: 6px 16px; font-size: 13px; cursor: pointer;">
                                Close
                            </button>
                            <a href="/dashboard/vehicle_bookings/${event.id}/edit" class="btn btn-sm btn-primary" style="padding: 6px 16px; font-size: 13px; background: #3b82f6; color: white; text-decoration: none; border-radius: 6px;">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                           
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    $('#bookingDetailsModal').addClass('show').css('display', 'block');
    $('body').addClass('modal-open');
    $('<div class="modal-backdrop fade show"></div>').appendTo('body');
}

// Simple close modal function
window.closeBookingModal = function() {
    $('#bookingDetailsModal').removeClass('show').css('display', 'none');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    $('#bookingDetailsModal').remove();
}

// Delete function for modal
window.deleteBookingFromModal = function(id) {
    closeBookingModal(); // Close modal first
    setTimeout(() => {
        deleteBooking(id); // Then delete
    }, 300);
}

// Close modal when clicking on backdrop
$(document).on('click', '.modal-backdrop', function() {
    closeBookingModal();
});

// Close modal with Escape key
$(document).on('keyup', function(e) {
    if (e.key === 'Escape' && $('#bookingDetailsModal').is(':visible')) {
        closeBookingModal();
    }
});


    // Initialize
    showTable();
});
</script>
@endsection