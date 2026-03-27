@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <i class="fas fa-calendar-alt fa-2x text-primary mr-3"></i>
            <h1 class="m-0">Create Multiple Bookings</h1>
        </div>
        <div>
            <a href="{{ route('admin.vehicle_bookings.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Bookings
            </a>
            <a href="{{ route('admin.vehicle_bookings.create') }}" class="btn btn-info ml-2">
                <i class="fas fa-plus mr-1"></i> Single Booking
            </a>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @include('layouts.admin_theme.alert')
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            <strong>Multiple Bookings Mode:</strong> Customer, vehicle, dates, driver, and helper are the same for all bookings.<br>
            Each booking can have its own trip category, route (which determines the rate), destinations, and payment details.
        </div>

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-car mr-1"></i>
                    Common Booking Information
                </h3>
            </div>
            
            <form method="POST" action="{{ route('admin.vehicle_bookings.multiple.store') }}" id="multipleBookingForm">
                @csrf
                <div class="card-body">
                    <!-- Common Fields Section -->
                    <div class="row">
                        @if($currentUserIsCustomer == 'N')
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="customer_id">Customer <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="customer_id" id="customer_id" class="form-control" required>
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-success" id="addCustomerBtn">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Vehicle <span class="text-danger">*</span></label>
                                <select name="vehicle_id" id="vehicle_id" class="form-control" required>
                                    <option value="">Select Vehicle</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" data-type="{{ $vehicle->vehicle_type }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                            {{ $vehicle->vehicle_name }} ({{ ucfirst($vehicle->vehicle_type) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Driver</label>
                                <select name="driver_id" class="form-control">
                                    <option value="">Select Driver</option>
                                    @foreach($drivers as $driver)
                                        <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                                            {{ $driver->user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Helper</label>
                                <select name="helper_id" class="form-control">
                                    <option value="">Select Helper</option>
                                    @foreach($helpers as $helper)
                                        <option value="{{ $helper->id }}" {{ old('helper_id') == $helper->id ? 'selected' : '' }}>
                                            {{ $helper->user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Start Date <span class="text-danger">*</span></label>
                                <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Start Time <span class="text-danger">*</span></label>
                                <input type="time" id="start_time" name="start_time" value="{{ old('start_time') }}" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>End Date <span class="text-danger">*</span></label>
                                <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>End Time</label>
                                <input type="time" id="end_time" name="end_time" value="{{ old('end_time') }}" class="form-control">
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Total Days</label>
                                <input type="number" id="total_days" class="form-control" readonly>
                                <small class="text-muted">Auto-calculated</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Discount Type (Common)</label>
                                <select id="discount_amount_type" name="discount_amount_type" class="form-control">
                                    <option value="amount" {{ old('discount_amount_type') == 'amount' ? 'selected' : '' }}>Fixed Amount</option>
                                    <option value="percentage" {{ old('discount_amount_type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Discount (Common)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="discount_symbol">रू</span>
                                    </div>
                                    <input id="discount" name="discount" type="number" step="0.01" value="{{ old('discount', '0') }}" class="form-control">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Apply VAT (13%)</label>
                                <select name="vat" id="vat" class="form-control">
                                    <option value="0" {{ old('vat', '0') == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('vat') == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Signage Information</label>
                                <textarea name="signage_information" id="signageInformation" class="form-control" rows="2" placeholder="Describe signage details...">{{ old('signage_information') }}</textarea>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Dynamic Booking Entries Section -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="mb-0">Individual Trip Details (Each with its own rate)</h4>
                                <button type="button" class="btn btn-success" id="addBookingRow">
                                    <i class="fas fa-plus"></i> Add Another Trip
                                </button>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="bookingsTable">
                                    <thead>
                                        <tr>
                                            <th width="30">#</th>
                                            <th>Passenger</th>
                                            <th>File No</th>
                                            <th>Trip Category *</th>
                                            <th>Trip Route *</th>
                                            <th>Rate/Day</th>
                                            <th>Sub Total</th>
                                            <th>Total (after discount & VAT)</th>
                                            <th>From Destination</th>
                                            <th>To Destination</th>
                                            <th>No. of People</th>
                                            <th>Status</th>
                                            <th>Paid Amount</th>
                                            <th>Payment Method</th>
                                            <th>Payment Date</th>
                                            <th width="50">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bookingsTbody">
                                        @php
                                            $oldBookings = old('bookings', []);
                                            if(empty($oldBookings)) {
                                                $oldBookings = [['passenger' => '', 'file_no' => '', 'trip_category_id' => '', 'trip_route_id' => '', 'from_destination' => '', 'to_destination' => '', 'no_of_people' => '', 'status' => 'pending', 'paid_amount' => '', 'payment_method' => '', 'payment_date' => '', 'payment_note' => '', 'notes' => '']];
                                            }
                                        @endphp
                                        
                                        @foreach($oldBookings as $index => $booking)
                                        <tr class="booking-row" data-index="{{ $index }}">
                                            <td class="row-number">{{ $index + 1 }}</td>
                                            <td>
                                                <input type="text" name="bookings[{{ $index }}][passenger]" value="{{ $booking['passenger'] ?? '' }}" class="form-control" placeholder="Passenger">
                                            </td>
                                            <td>
                                                <input type="text" name="bookings[{{ $index }}][file_no]" value="{{ $booking['file_no'] ?? '' }}" class="form-control" placeholder="File No">
                                            </td>
                                            <td>
                                                <select name="bookings[{{ $index }}][trip_category_id]" class="form-control trip-category" data-index="{{ $index }}" required>
                                                    <option value="">Select Category</option>
                                                    @foreach($tripCategories as $category)
                                                        <option value="{{ $category->id }}" {{ ($booking['trip_category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                                                            {{ $category->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="bookings[{{ $index }}][trip_route_id]" class="form-control trip-route" data-index="{{ $index }}" required>
                                                    <option value="">Select Route</option>
                                                    @if(!empty($booking['trip_route_id']))
                                                        @php
                                                            $selectedRoute = \App\Models\TripRoute::find($booking['trip_route_id']);
                                                        @endphp
                                                        @if($selectedRoute)
                                                            <option value="{{ $selectedRoute->id }}" 
                                                                data-car="{{ $selectedRoute->car_price ?? 0 }}"
                                                                data-hiace="{{ $selectedRoute->hiace_price ?? 0 }}"
                                                                data-coaster="{{ $selectedRoute->coaster_price ?? 0 }}"
                                                                data-bus="{{ $selectedRoute->bus_price ?? 0 }}"
                                                                selected>{{ $selectedRoute->title }}</option>
                                                        @endif
                                                    @endif
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control rate-display" readonly placeholder="Auto">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control subtotal-display" readonly placeholder="Auto">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control total-display" readonly placeholder="Auto">
                                            </td>
                                            <td>
                                                <input type="text" name="bookings[{{ $index }}][from_destination]" value="{{ $booking['from_destination'] ?? '' }}" class="form-control" placeholder="Pickup">
                                            </td>
                                            <td>
                                                <input type="text" name="bookings[{{ $index }}][to_destination]" value="{{ $booking['to_destination'] ?? '' }}" class="form-control" placeholder="Dropoff">
                                            </td>
                                            <td>
                                                <input type="number" name="bookings[{{ $index }}][no_of_people]" value="{{ $booking['no_of_people'] ?? '' }}" class="form-control" placeholder="People">
                                            </td>
                                            <td>
                                                <select name="bookings[{{ $index }}][status]" class="form-control" required>
                                                    <option value="pending" {{ ($booking['status'] ?? 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="confirmed" {{ ($booking['status'] ?? '') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                    <option value="cancelled" {{ ($booking['status'] ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="bookings[{{ $index }}][paid_amount]" value="{{ $booking['paid_amount'] ?? '' }}" class="form-control paid-amount" placeholder="Paid">
                                            </td>
                                            <td>
                                                <select name="bookings[{{ $index }}][payment_method]" class="form-control">
                                                    <option value="">Select Method</option>
                                                    <option value="cash" {{ ($booking['payment_method'] ?? '') == 'cash' ? 'selected' : '' }}>Cash</option>
                                                    <option value="bank_transfer" {{ ($booking['payment_method'] ?? '') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                                    <option value="card" {{ ($booking['payment_method'] ?? '') == 'card' ? 'selected' : '' }}>Card</option>
                                                    <option value="online" {{ ($booking['payment_method'] ?? '') == 'online' ? 'selected' : '' }}>Online</option>
                                                    <option value="cheque" {{ ($booking['payment_method'] ?? '') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="date" name="bookings[{{ $index }}][payment_date]" value="{{ $booking['payment_date'] ?? date('Y-m-d') }}" class="form-control">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm remove-row" {{ $index == 0 ? 'disabled' : '' }}>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Create All Bookings
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Add Customer Modal -->
<div class="modal fade" id="customerModal">
    <div class="modal-dialog">
        <div class="modal-content p-3">
            <h5>Add Customer</h5>
            <input id="c_name" class="form-control mb-2" placeholder="Name">
            <input id="c_phone" class="form-control mb-2" placeholder="Phone">
            <input id="c_email" class="form-control mb-2" placeholder="Email">
            <input id="c_address" class="form-control mb-2" placeholder="Address">
            <button class="btn btn-primary" id="saveCustomer">Save</button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    const VAT_RATE = 0.13;
    let rowCounter = {{ count($oldBookings) }};
    
    // Calculate total days from start and end dates
    function calculateTotalDays() {
        var start_date = $("#start_date").val();
        var start_time = $("#start_time").val();
        var end_date = $("#end_date").val();
        var end_time = $("#end_time").val();
        
        if (start_date && start_time && end_date && end_time) {
            var start = new Date(start_date + 'T' + start_time);
            var end = new Date(end_date + 'T' + end_time);
            
            if (end > start) {
                var diffMs = end - start;
                var diffHours = diffMs / (1000 * 60 * 60);
                var days = diffHours / 24;
                if (days < 1) days = 1;
                $("#total_days").val(days.toFixed(1));
                return days;
            }
        }
        $("#total_days").val(0);
        return 0;
    }
    
    // Calculate rate, subtotal, and total for a specific row
    function calculateRowTotals(row) {
        var days = parseFloat($("#total_days").val()) || 0;
        var routeSelect = row.find('.trip-route');
        var selectedOption = routeSelect.find(':selected');
        var vehicleType = $('#vehicle_id option:selected').data('type');
        
        if (days > 0 && vehicleType && selectedOption.val()) {
            vehicleType = vehicleType.toLowerCase();
            var rate = selectedOption.data(vehicleType) || 0;
            var subtotal = days * rate;
            
            // Get common discount and VAT settings
            var discount = parseFloat($("#discount").val()) || 0;
            var discountType = $("#discount_amount_type").val();
            var discountAmount = 0;
            
            if (discount > 0) {
                if (discountType === 'percentage') {
                    discountAmount = subtotal * (discount / 100);
                } else {
                    discountAmount = discount;
                }
            }
            
            var afterDiscount = subtotal - discountAmount;
            var applyVat = $("#vat").val() == '1';
            var vatAmount = 0;
            
            if (applyVat && afterDiscount > 0) {
                vatAmount = afterDiscount * VAT_RATE;
            }
            
            var total = afterDiscount + vatAmount;
            
            // Update display fields
            row.find('.rate-display').val(rate.toFixed(2));
            row.find('.subtotal-display').val(subtotal.toFixed(2));
            row.find('.total-display').val(total.toFixed(2));
        } else {
            row.find('.rate-display').val('');
            row.find('.subtotal-display').val('');
            row.find('.total-display').val('');
        }
    }
    
    // Calculate all rows
    function calculateAllRows() {
        $('.booking-row').each(function() {
            calculateRowTotals($(this));
        });
    }
    
    // Add new booking row
    $('#addBookingRow').click(function() {
        const newIndex = rowCounter;
        const newRow = `
            <tr class="booking-row" data-index="${newIndex}">
                <td class="row-number">${newIndex + 1}</td>
                <td>
                    <input type="text" name="bookings[${newIndex}][passenger]" class="form-control" placeholder="Passenger">
                </td>
                <td>
                    <input type="text" name="bookings[${newIndex}][file_no]" class="form-control" placeholder="File No">
                </td>
                <td>
                    <select name="bookings[${newIndex}][trip_category_id]" class="form-control trip-category" data-index="${newIndex}" required>
                        <option value="">Select Category</option>
                        @foreach($tripCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="bookings[${newIndex}][trip_route_id]" class="form-control trip-route" data-index="${newIndex}" required>
                        <option value="">Select Route</option>
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control rate-display" readonly placeholder="Auto">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control subtotal-display" readonly placeholder="Auto">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control total-display" readonly placeholder="Auto">
                </td>
                <td>
                    <input type="text" name="bookings[${newIndex}][from_destination]" class="form-control" placeholder="Pickup">
                </td>
                <td>
                    <input type="text" name="bookings[${newIndex}][to_destination]" class="form-control" placeholder="Dropoff">
                </td>
                <td>
                    <input type="number" name="bookings[${newIndex}][no_of_people]" class="form-control" placeholder="People">
                </td>
                <td>
                    <select name="bookings[${newIndex}][status]" class="form-control" required>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" name="bookings[${newIndex}][paid_amount]" class="form-control paid-amount" placeholder="Paid">
                </td>
                <td>
                    <select name="bookings[${newIndex}][payment_method]" class="form-control">
                        <option value="">Select Method</option>
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="card">Card</option>
                        <option value="online">Online</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </td>
                <td>
                    <input type="date" name="bookings[${newIndex}][payment_date]" value="{{ date('Y-m-d') }}" class="form-control">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm remove-row">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#bookingsTbody').append(newRow);
        rowCounter++;
        updateRowNumbers();
    });
    
    // Remove row
    $(document).on('click', '.remove-row', function() {
        if ($('.booking-row').length > 1) {
            $(this).closest('tr').remove();
            updateRowNumbers();
            calculateAllRows();
        }
    });
    
    // Update row numbers
    function updateRowNumbers() {
        $('.booking-row').each(function(index) {
            $(this).find('.row-number').text(index + 1);
            $(this).find('select[name^="bookings["], input[name^="bookings["]').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    const newName = name.replace(/bookings\[\d+\]/, `bookings[${index}]`);
                    $(this).attr('name', newName);
                }
            });
            $(this).find('.trip-category').attr('data-index', index);
            $(this).find('.trip-route').attr('data-index', index);
        });
        rowCounter = $('.booking-row').length;
    }
    
    // Trip Category Change - Load Routes for specific row
    $(document).on('change', '.trip-category', function() {
        var category_id = $(this).val();
        var row = $(this).closest('tr');
        var routeSelect = row.find('.trip-route');
        
        routeSelect.html('<option value="">Loading...</option>');
        
        if (category_id) {
            $.ajax({
                url: '/dashboard/get-trip-routes/' + category_id,
                type: 'GET',
                success: function(routes) {
                    var options = '<option value="">Select Route</option>';
                    
                    $.each(routes, function(index, route) {
                        options += '<option value="' + route.id + '" ' +
                            'data-car="' + (route.car_price || 0) + '" ' +
                            'data-hiace="' + (route.hiace_price || 0) + '" ' +
                            'data-coaster="' + (route.coaster_price || 0) + '" ' +
                            'data-bus="' + (route.bus_price || 0) + '">' +
                            route.title +
                            '</option>';
                    });
                    
                    routeSelect.html(options);
                },
                error: function() {
                    routeSelect.html('<option value="">Error loading routes</option>');
                }
            });
        } else {
            routeSelect.html('<option value="">Select Route</option>');
        }
    });
    
    // Route selection - Calculate totals for this row
    $(document).on('change', '.trip-route', function() {
        var row = $(this).closest('tr');
        calculateRowTotals(row);
    });
    
    // Common field changes - Recalculate all rows
    $("#start_date, #start_time, #end_date, #end_time").on("change", function() {
        calculateTotalDays();
        calculateAllRows();
    });
    
    $("#discount, #discount_amount_type, #vat").on("change keyup", function() {
        calculateAllRows();
        updateDiscountSymbol();
    });
    
    // Vehicle change - Recalculate all rows
    $('#vehicle_id').change(function() {
        calculateAllRows();
    });
    
    // Update discount symbol
    function updateDiscountSymbol() {
        var type = $("#discount_amount_type").val();
        $("#discount_symbol").text(type === 'percentage' ? '%' : 'रू');
    }
    
    // Initial calculations
    calculateTotalDays();
    calculateAllRows();
    updateDiscountSymbol();
});

// Customer modal functions
function reloadCustomers(selectedId = null) {
    $.ajax({
        url: "{{ route('admin.ajax.customers.list') }}",
        type: "GET",
        success: function (data) {
            let options = '<option value="">Select Customer</option>';
            data.forEach(function (c) {
                options += `<option value="${c.id}">${c.name}</option>`;
            });
            $('#customer_id').html(options);
            if (selectedId) {
                $('#customer_id').val(selectedId);
            }
        }
    });
}

// Open modal
$('#addCustomerBtn').click(() => $('#customerModal').modal('show'));

// Save customer
$('#saveCustomer').click(function () {
    $.ajax({
        url: "{{ route('admin.ajax.customers.store') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            name: $('#c_name').val(),
            phone: $('#c_phone').val(),
            email: $('#c_email').val(),
            address: $('#c_address').val(),
        },
        success: function (res) {
            if (res.success) {
                reloadCustomers(res.id);
                $('#customerModal').modal('hide');
                $('#c_name, #c_phone, #c_email, #c_address').val('');
            }
        },
        error: function (err) {
            console.log(err.responseText);
        }
    });
});
</script>

<style>
.text-danger {
    color: #dc3545;
}
.input-group-text {
    background-color: #e9ecef;
}
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0,0,0,.125);
}
.form-group label {
    font-weight: 500;
    margin-bottom: 0.3rem;
}
hr {
    border-top: 2px solid rgba(0,0,0,.1);
}
.table-responsive {
    overflow-x: auto;
}
.table th, .table td {
    white-space: nowrap;
}
.booking-row td {
    vertical-align: middle;
}
.remove-row {
    margin: 0;
}
</style>
@endsection