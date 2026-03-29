@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <i class="fas fa-calendar-alt fa-2x text-primary mr-3"></i>
            <h1 class="m-0">Create Multiple Bookings</h1>
        </div>
        <a href="{{ route('admin.vehicle_bookings.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to Bookings
        </a>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @include('layouts.admin_theme.alert')

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-car mr-1"></i>
                    Multiple Bookings Creation
                </h3>
               
            </div>

            <form method="POST" action="{{ route('admin.vehicle_bookings.multiple.store') }}" id="multipleBookingForm">
                @csrf
                <div class="card-body">
                    <!-- Common Information Section -->
                    <div class="card card-secondary mb-4">
                        <div class="card-header">
                            <h4 class="card-title">Common Information (Applies to all bookings)</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if($currentUserIsCustomer == 'N')
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="customer_id">Customer <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <select name="customer_id" id="customer_id" class="form-control" required>
                                                <option value="">Select Customer</option>
                                                @foreach($customers as $customer)
                                                    <option value="{{ $customer->id }}">
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
                                                <option value="{{ $vehicle->id }}" data-type="{{ $vehicle->vehicle_type }}">
                                                    {{ $vehicle->vehicle_name }} ({{ ucfirst($vehicle->vehicle_type) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Driver</label>
                                        <div class="input-group">
                                            <select name="driver_id" id="driver_id" class="form-control">
                                                <option value="">Select Driver</option>
                                                @foreach($drivers as $driver)
                                                    <option value="{{ $driver->id }}">
                                                        {{ $driver->user->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-success" id="addDriverBtn">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Helper</label>
                                        <div class="input-group">
                                            <select name="helper_id" id="helper_id" class="form-control">
                                                <option value="">Select Helper</option>
                                                @foreach($helpers as $helper)
                                                    <option value="{{ $helper->id }}">
                                                        {{ $helper->user->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-success" id="addHelperBtn">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Passenger</label>
                                        <input type="text" name="passenger" id="passenger" class="form-control" placeholder="Enter passenger">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>File No</label>
                                        <input type="text" name="file_no" id="file_no" class="form-control" placeholder="Enter file no">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Signage Information</label>
                                        <textarea name="signage_information" id="signageInformation" class="form-control" rows="2" placeholder="Describe signage details..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                   <div class="card-tools d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-success btn-sm" id="addBookingRowBtn">
                            <i class="fas fa-plus mr-1"></i> Add Another Booking Row
                        </button>
                    </div>

                    <!-- Multiple Booking Rows Section -->
                    <div class="card card-success">
                        <div class="card-header">
                            <h4 class="card-title">Booking Details</h4>
                        </div>
                         
                        <div class="card-body">
                            <div id="bookingsRowsContainer">
                                <!-- Booking Row 1 -->
                                <div class="booking-row card card-info mb-3" data-row-index="1">
                                    <div class="card-header">
                                        <h5 class="card-title">Booking #1</h5>
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-danger btn-sm remove-row-btn" data-row-id="1">
                                                <i class="fas fa-trash"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            {{-- <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>File No</label>
                                                    <input type="text" name="bookings[1][file_no]" id="file_no" class="form-control" placeholder="Enter file no">
                                                </div>
                                            </div> --}}
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Trip Category</label>
                                                    <div class="input-group">
                                                        <select name="bookings[1][trip_category_id]" id="trip_category_id_1" class="form-control trip-category-select" data-row="1">
                                                            <option value="">Select Category</option>
                                                            @foreach($tripCategories as $category)
                                                                <option value="{{ $category->id }}">
                                                                    {{ $category->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-success add-category-btn">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Trip Route</label>
                                                    <div class="input-group">
                                                        <select name="bookings[1][trip_route_id]" id="trip_route_id_1" class="form-control trip-route-select" data-row="1">
                                                            <option value="">Select Route</option>
                                                        </select>
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-success add-route-btn">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Amount</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">रू</span>
                                                        </div>
                                                        <input type="number" step="0.01" name="bookings[1][rate_per_day]" class="form-control rate-per-day" data-row="1" value="0">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Pickup Destination</label>
                                                    <input type="text" name="bookings[1][from_destination]" class="form-control" placeholder="Enter pickup location">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Dropout Destination</label>
                                                    <input type="text" name="bookings[1][to_destination]" class="form-control" placeholder="Enter dropoff location">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>No. of People</label>
                                                    <input type="number" name="bookings[1][no_of_people]" class="form-control no-of-people" min="1" data-row="1">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Start Date <span class="text-danger">*</span></label>
                                                    <input type="date" name="bookings[1][start_date]"  id="start_date_1" class="form-control start-date" data-row="1" required>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Start Time <span class="text-danger">*</span></label>
                                                    <input type="time" name="bookings[1][start_time]" class="form-control start-time" data-row="1" required>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>End Date <span class="text-danger">*</span></label>
                                                    <input type="date" name="bookings[1][end_date]"  id="end_date_1" class="form-control end-date" data-row="1" required>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <hr>
                                                <h6>Financial Details - Booking #1</h6>
                                            </div>

                                            

                                            {{-- <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Sub Total</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">रू</span>
                                                        </div>
                                                        <input type="number" step="0.01" name="bookings[1][sub_total]" class="form-control sub-total" data-row="1" readonly>
                                                    </div>
                                                </div>
                                            </div> --}}

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Discount Type</label>
                                                    <select name="bookings[1][discount_amount_type]" class="form-control discount-type" data-row="1">
                                                        <option value="amount">Fixed Amount</option>
                                                        <option value="percentage">Percentage</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Discount</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text discount-symbol" data-row="1">रू</span>
                                                        </div>
                                                        <input type="number" step="0.01" name="bookings[1][discount]" class="form-control discount" data-row="1" value="0">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Apply VAT (13%)</label>
                                                    <select name="bookings[1][vat]" class="form-control vat" data-row="1">
                                                        <option value="0">No</option>
                                                        <option value="1">Yes</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>VAT Amount</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">रू</span>
                                                        </div>
                                                        <input type="number" step="0.01" name="bookings[1][tax]" class="form-control vat-amount" data-row="1" readonly>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Total Amount</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">रू</span>
                                                        </div>
                                                        <input type="number" step="0.01" name="bookings[1][total_amount]" class="form-control total-amount" data-row="1" readonly>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Paid Amount</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">रू</span>
                                                        </div>
                                                        <input type="number" step="0.01" name="bookings[1][paid_amount]" class="form-control paid-amount" data-row="1" value="0">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Additional Notes</label>
                                                    <textarea name="bookings[1][notes]" rows="2" class="form-control" placeholder="Any additional notes..."></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Save All Bookings
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Modals -->
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

<div class="modal fade" id="categoryModal">
    <div class="modal-dialog">
        <div class="modal-content p-3">
            <h5>Add Category</h5>
            <input id="cat_name" class="form-control mb-2" placeholder="Name">
            <textarea id="cat_desc" class="form-control mb-2" placeholder="Description"></textarea>
            <button class="btn btn-primary" id="saveCategory">Save</button>
        </div>
    </div>
</div>

<div class="modal fade" id="routeModal">
    <div class="modal-dialog">
        <div class="modal-content p-3">
            <h5>Add Route</h5>
            <select id="r_category" class="form-control mb-2">
                @foreach($tripCategories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <input id="r_title" class="form-control mb-2" placeholder="Title">
            <input id="r_km" class="form-control mb-2" placeholder="KM">
            <input id="r_car" class="form-control mb-2" placeholder="Car Price">
            <input id="r_hiace" class="form-control mb-2" placeholder="Hiace Price">
            <input id="r_coaster" class="form-control mb-2" placeholder="Coaster Price">
            <input id="r_bus" class="form-control mb-2" placeholder="Bus Price">
            <button class="btn btn-primary" id="saveRoute">Save</button>
        </div>
    </div>
</div>

<div class="modal fade" id="driverModal">
    <div class="modal-dialog">
        <div class="modal-content p-3">
            <h5>Add Driver</h5>
            <input id="d_name" class="form-control mb-2" placeholder="Driver Name">
            <input id="d_phone" class="form-control mb-2" placeholder="Phone">
            <input id="d_email" class="form-control mb-2" placeholder="Email">
            <input id="d_license" class="form-control mb-2" placeholder="License Number">
            <button class="btn btn-primary" id="saveDriver">Save</button>
        </div>
    </div>
</div>

<div class="modal fade" id="helperModal">
    <div class="modal-dialog">
        <div class="modal-content p-3">
            <h5>Add Helper</h5>
            <input id="h_name" class="form-control mb-2" placeholder="Helper Name">
            <input id="h_phone" class="form-control mb-2" placeholder="Phone">
            <input id="h_email" class="form-control mb-2" placeholder="Email">
            <button class="btn btn-primary" id="saveHelper">Save</button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    const VAT_RATE = 0.13;
    let rowCounter = 1;
    document.getElementById('start_date_1').value = new Date().toISOString().split('T')[0];
    document.getElementById('end_date_1').value = new Date().toISOString().split('T')[0];

    // Calculate days between dates
    function calculateDays(startDate, endDate) {
        if (startDate && endDate) {
            var start = new Date(startDate);
            var end = new Date(endDate);
            var diffTime = end - start;
            var days = diffTime / (1000 * 60 * 60 * 24);
            if (days < 0) days = 0;
            if (days === 0 && startDate === endDate) {
                days = 1;
            }
            return days;
        }
        return 0;
    }

    // Calculate total for a specific row
    function calculateRowTotal(rowId) {
        var startDate = $(`#start_date_${rowId}`).val();
        var endDate = $(`#end_date_${rowId}`).val();
        
        console.log(`Calculating row ${rowId}: startDate=${startDate}, endDate=${endDate}`);
        
        var days = calculateDays(startDate, endDate);
        
        var ratePerDay = parseFloat($(`input[name="bookings[${rowId}][rate_per_day]"]`).val()) || 0;
        var subTotal = days * ratePerDay;
        
        console.log(`Row ${rowId}: days=${days}, ratePerDay=${ratePerDay}, subTotal=${subTotal}`);
        
        $(`input[name="bookings[${rowId}][sub_total]"]`).val(subTotal.toFixed(2));

        var discount = parseFloat($(`input[name="bookings[${rowId}][discount]"]`).val()) || 0;
        var discountType = $(`select[name="bookings[${rowId}][discount_amount_type]"]`).val();
        var discountAmount = 0;

        if (discount > 0) {
            if (discountType === 'percentage') {
                discountAmount = subTotal * (discount / 100);
            } else {
                discountAmount = discount;
            }
        }

        var afterDiscount = Math.max(0, subTotal - discountAmount);
        var applyVat = $(`select[name="bookings[${rowId}][vat]"]`).val() == '1';
        var vatAmount = 0;
        
        if (applyVat && afterDiscount > 0) {
            vatAmount = afterDiscount * VAT_RATE;
        }
        $(`input[name="bookings[${rowId}][tax]"]`).val(vatAmount.toFixed(2));

        var total = afterDiscount + vatAmount;
        $(`input[name="bookings[${rowId}][total_amount]"]`).val(total.toFixed(2));
        
        updateRemainingBalance(rowId);
    }

    function updateRemainingBalance(rowId) {
        var total = parseFloat($(`input[name="bookings[${rowId}][total_amount]"]`).val()) || 0;
        var paid = parseFloat($(`input[name="bookings[${rowId}][paid_amount]"]`).val()) || 0;
        var remaining = total - paid;
        // Store remaining balance in a hidden field or just calculate when needed
    }

    function updateDiscountSymbol(rowId) {
        var type = $(`select[name="bookings[${rowId}][discount_amount_type]"]`).val();
        $(`.discount-symbol[data-row="${rowId}"]`).text(type === 'percentage' ? '%' : 'रू');
    }

    // Attach event handlers for a specific row
    function attachRowEventHandlers(rowId) {
        console.log(`Attaching event handlers for row ${rowId}`);
        
        // Date change handlers
        $(`#start_date_${rowId}`).off('change').on('change', function() {
            console.log(`Start date changed for row ${rowId}`);
            calculateRowTotal(rowId);
        });
        
        $(`#end_date_${rowId}`).off('change').on('change', function() {
            console.log(`End date changed for row ${rowId}`);
            calculateRowTotal(rowId);
        });
        
        // Rate per day change
        $(`input[name="bookings[${rowId}][rate_per_day]"]`).off('change keyup').on('change keyup', function() {
            console.log(`Rate per day changed for row ${rowId}`);
            calculateRowTotal(rowId);
        });
        
        // Discount change
        $(`.discount[data-row="${rowId}"]`).off('change keyup').on('change keyup', function() {
            calculateRowTotal(rowId);
        });
        
        // Discount type change
        $(`.discount-type[data-row="${rowId}"]`).off('change').on('change', function() {
            updateDiscountSymbol(rowId);
            calculateRowTotal(rowId);
        });
        
        // VAT change
        $(`.vat[data-row="${rowId}"]`).off('change').on('change', function() {
            calculateRowTotal(rowId);
        });
        
        // Paid amount change
        $(`.paid-amount[data-row="${rowId}"]`).off('change keyup').on('change keyup', function() {
            updateRemainingBalance(rowId);
        });
        
        // Trip category change - Load routes
        $(`#trip_category_id_${rowId}`).off('change').on('change', function() {
            var categoryId = $(this).val();
            var routeSelect = $(`#trip_route_id_${rowId}`);
            routeSelect.html('<option value="">Loading...</option>');
            
            if (categoryId) {
                $.ajax({
                    url: '/dashboard/get-trip-routes/' + categoryId,
                    type: 'GET',
                    success: function(routes) {
                        var options = '<option value="">Select Route</option>';
                        $.each(routes, function(index, route) {
                            options += '<option value="' + route.id + '" ' +
                                'data-car="' + (route.car_price || 0) + '" ' +
                                'data-hiace="' + (route.hiace_price || 0) + '" ' +
                                'data-coaster="' + (route.coaster_price || 0) + '" ' +
                                'data-bus="' + (route.bus_price || 0) + '">' +
                                route.title + '</option>';
                        });
                        routeSelect.html(options);
                    }
                });
            } else {
                routeSelect.html('<option value="">Select Route</option>');
            }
        });
        
        // Route selection - Set rate based on vehicle type
        $(`#trip_route_id_${rowId}`).off('change').on('change', function() {
            var vehicleType = $('#vehicle_id option:selected').data('type');
            var selected = $(this).find(':selected');
            var rate = 0;
            
            if (vehicleType && selected.val()) {
                vehicleType = vehicleType.toLowerCase();
                rate = selected.data(vehicleType) || 0;
                
                if (rate > 0) {
                    $(`input[name="bookings[${rowId}][rate_per_day]"]`).val(rate);
                    calculateRowTotal(rowId);
                }
            }
        });
        
        updateDiscountSymbol(rowId);
        
        // Initial calculation for this row
        setTimeout(function() {
            calculateRowTotal(rowId);
        }, 100);
    }

    // Add new booking row
    $('#addBookingRowBtn').click(function() {
        rowCounter++;
        var newRowHtml = `
            <div class="booking-row card card-info mb-3" data-row-index="${rowCounter}">
                <div class="card-header">
                    <h5 class="card-title">Booking #${rowCounter}</h5>
                    <div class="card-tools">
                        <button type="button" class="btn btn-danger btn-sm remove-row-btn" data-row-id="${rowCounter}">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                       
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Trip Category</label>
                                <div class="input-group">
                                    <select name="bookings[${rowCounter}][trip_category_id]" id="trip_category_id_${rowCounter}" class="form-control trip-category-select" data-row="${rowCounter}">
                                        <option value="">Select Category</option>
                                        @foreach($tripCategories as $category)
                                            <option value="{{ $category->id }}">
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-success add-category-btn">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Trip Route</label>
                                <div class="input-group">
                                    <select name="bookings[${rowCounter}][trip_route_id]" id="trip_route_id_${rowCounter}" class="form-control trip-route-select" data-row="${rowCounter}">
                                        <option value="">Select Route</option>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-success add-route-btn">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Pickup Destination</label>
                                <input type="text" name="bookings[${rowCounter}][from_destination]" class="form-control" placeholder="Enter pickup location">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Dropout Destination</label>
                                <input type="text" name="bookings[${rowCounter}][to_destination]" class="form-control" placeholder="Enter dropoff location">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>No. of People</label>
                                <input type="number" name="bookings[${rowCounter}][no_of_people]" class="form-control no-of-people" min="1" data-row="${rowCounter}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="bookings[${rowCounter}][start_date]" id="start_date_${rowCounter}" class="form-control start-date" data-row="${rowCounter}" required>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Start Time <span class="text-danger">*</span></label>
                                <input type="time" name="bookings[${rowCounter}][start_time]" class="form-control start-time" data-row="${rowCounter}" required>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>End Date <span class="text-danger">*</span></label>
                                <input type="date" name="bookings[${rowCounter}][end_date]" id="end_date_${rowCounter}" class="form-control end-date" data-row="${rowCounter}" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <hr>
                            <h6>Financial Details - Booking #${rowCounter}</h6>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Rate Per Day</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">रू</span>
                                    </div>
                                    <input type="number" step="0.01" name="bookings[${rowCounter}][rate_per_day]" class="form-control rate-per-day" data-row="${rowCounter}" value="0">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Sub Total</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">रू</span>
                                    </div>
                                    <input type="number" step="0.01" name="bookings[${rowCounter}][sub_total]" class="form-control sub-total" data-row="${rowCounter}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Discount Type</label>
                                <select name="bookings[${rowCounter}][discount_amount_type]" class="form-control discount-type" data-row="${rowCounter}">
                                    <option value="amount">Fixed Amount</option>
                                    <option value="percentage">Percentage</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Discount</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text discount-symbol" data-row="${rowCounter}">रू</span>
                                    </div>
                                    <input type="number" step="0.01" name="bookings[${rowCounter}][discount]" class="form-control discount" data-row="${rowCounter}" value="0">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Apply VAT (13%)</label>
                                <select name="bookings[${rowCounter}][vat]" class="form-control vat" data-row="${rowCounter}">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>VAT Amount</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">रू</span>
                                    </div>
                                    <input type="number" step="0.01" name="bookings[${rowCounter}][tax]" class="form-control vat-amount" data-row="${rowCounter}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Total Amount</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">रू</span>
                                    </div>
                                    <input type="number" step="0.01" name="bookings[${rowCounter}][total_amount]" class="form-control total-amount" data-row="${rowCounter}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Paid Amount</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">रू</span>
                                    </div>
                                    <input type="number" step="0.01" name="bookings[${rowCounter}][paid_amount]" class="form-control paid-amount" data-row="${rowCounter}" value="0">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Additional Notes</label>
                                <textarea name="bookings[${rowCounter}][notes]" rows="2" class="form-control" placeholder="Any additional notes..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#bookingsRowsContainer').append(newRowHtml);
        
        // Attach event handlers for the new row
        attachRowEventHandlers(rowCounter);
    });

    // Remove booking row
    $(document).on('click', '.remove-row-btn', function() {
        var rowId = $(this).data('row-id');
        $(`.booking-row[data-row-index="${rowId}"]`).remove();
    });

    // Vehicle change - Update rates for all rows
    $('#vehicle_id').on('change', function() {
        $('.booking-row').each(function() {
            var rowId = $(this).data('row-index');
            $(`#trip_route_id_${rowId}`).trigger('change');
        });
    });

    // Attach handlers for existing row (row 1) and trigger initial calculation
    attachRowEventHandlers(1);

    // Also trigger initial calculation for row 1 with a small delay to ensure DOM is ready
    setTimeout(function() {
        calculateRowTotal(1);
    }, 200);

    // AJAX functions for modals (keep your existing modal functions)
    function reloadCustomers(selectedId = null) {
        $.ajax({
            url: "{{ route('admin.ajax.customers.list') }}",
            type: "GET",
            success: function(data) {
                let options = '<option value="">Select Customer</option>';
                data.forEach(function(c) {
                    options += `<option value="${c.id}">${c.name}</option>`;
                });
                $('#customer_id').html(options);
                if (selectedId) {
                    $('#customer_id').val(selectedId);
                }
            }
        });
    }

    function reloadCategoryDropdown(selectedId = null) {
        $.ajax({
            url: "{{ route('admin.ajax.trip-categories.list') }}",
            type: "GET",
            success: function(data) {
                let options = '<option value="">Select Category</option>';
                data.forEach(function(category) {
                    options += `<option value="${category.id}">${category.name}</option>`;
                });
                $('.trip-category-select').html(options);
                $('#r_category').html(options);
                if (selectedId) {
                    $('.trip-category-select').val(selectedId);
                    $('#r_category').val(selectedId);
                }
            }
        });
    }

    function reloadRoutes(selectedId = null) {
        $.ajax({
            url: "{{ route('admin.ajax.trip-routes.list') }}",
            type: "GET",
            success: function(data) {
                let options = '<option value="">Select Route</option>';
                data.forEach(function(route) {
                    options += `<option value="${route.id}">${route.title}</option>`;
                });
                $('.trip-route-select').html(options);
                if (selectedId) {
                    $('.trip-route-select').val(selectedId);
                }
            }
        });
    }

    // Modal handlers
    $('#addCustomerBtn').click(() => $('#customerModal').modal('show'));
  $(document).on('click', '.add-category-btn', function() {
    $('#categoryModal').modal('show');
});

// Route modal open
$(document).on('click', '.add-route-btn', function() {
    $('#routeModal').modal('show');
});
    $('#addDriverBtn').click(() => $('#driverModal').modal('show'));
    $('#addHelperBtn').click(() => $('#helperModal').modal('show'));

    $('#saveCustomer').click(function() {
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
            success: function(res) {
                if (res.success) {
                    reloadCustomers(res.id);
                    $('#customerModal').modal('hide');
                    $('#c_name, #c_phone, #c_email, #c_address').val('');
                }
            }
        });
    });

    $('#saveCategory').click(function() {
        $.ajax({
            url: "{{ route('admin.ajax.trip-categories.store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                name: $('#cat_name').val(),
                description: $('#cat_desc').val()
            },
            success: function(res) {
                if (res.success) {
                    reloadCategoryDropdown(res.id);
                    $('#categoryModal').modal('hide');
                    $('#cat_name, #cat_desc').val('');
                }
            }
        });
    });

    $('#saveRoute').click(function() {
        $.ajax({
            url: "{{ route('admin.ajax.trip-routes.store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                trip_category_id: $('#r_category').val(),
                title: $('#r_title').val(),
                km: $('#r_km').val(),
                car_price: $('#r_car').val(),
                hiace_price: $('#r_hiace').val(),
                coaster_price: $('#r_coaster').val(),
                bus_price: $('#r_bus').val()
            },
            success: function(res) {
                if (res.success) {
                    reloadRoutes(res.id);
                    $('#routeModal').modal('hide');
                    $('#r_title, #r_km, #r_car, #r_hiace, #r_coaster, #r_bus').val('');
                    $('.trip-category-select').trigger('change');
                }
            }
        });
    });

    $('#saveDriver').click(function() {
        $.ajax({
            url: "{{ route('admin.ajax.drivers.store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                name: $('#d_name').val(),
                phone: $('#d_phone').val(),
                email: $('#d_email').val(),
                license_number: $('#d_license').val()
            },
            success: function(res) {
                if (res.success) {
                    let options = '<option value="">Select Driver</option>';
                    res.drivers.forEach(function(driver) {
                        options += `<option value="${driver.id}">${driver.user.name}</option>`;
                    });
                    $('#driver_id').html(options);
                    $('#driverModal').modal('hide');
                    $('#d_name, #d_phone, #d_email, #d_license').val('');
                }
            }
        });
    });

    $('#saveHelper').click(function() {
        $.ajax({
            url: "{{ route('admin.ajax.helpers.store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                name: $('#h_name').val(),
                phone: $('#h_phone').val(),
                email: $('#h_email').val()
            },
            success: function(res) {
                if (res.success) {
                    let options = '<option value="">Select Helper</option>';
                    res.helpers.forEach(function(helper) {
                        options += `<option value="${helper.id}">${helper.user.name}</option>`;
                    });
                    $('#helper_id').html(options);
                    $('#helperModal').modal('hide');
                    $('#h_name, #h_phone, #h_email').val('');
                }
            }
        });
    });
});
</script>
@endsection