@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <i class="fas fa-calendar-alt fa-2x text-primary mr-3"></i>
            <h1 class="m-0">{{ isset($booking) ? 'Edit Booking' : 'Create New Booking' }}</h1>
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
                    {{ isset($booking) ? 'Edit Booking #' . $booking->id : 'New Booking' }}
                </h3>
            </div>

            <form method="POST" action="{{ isset($booking) ? route('admin.vehicle_bookings.update', $booking->id) : route('admin.vehicle_bookings.store') }}" id="bookingForm">
                @csrf
                @if(isset($booking))
                    @method('PUT')
                @endif

                <div class="card-body">
                    <div class="row">
                        @if($currentUserIsCustomer == 'N')
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="customer_id">Customer <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="customer_id" id="customer_id" class="form-control">
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" 
                                                {{ old('customer_id', $booking->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
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
                                <label>Passenger</label>
                                <input type="text" name="passenger" value="{{ old('passenger', $booking->passenger ?? '') }}" class="form-control" placeholder="Enter passenger">
                            </div>
                        </div>

                         <div class="col-md-4">
                            <div class="form-group">
                                <label>File No</label>
                                <input type="text" name="file_no" value="{{ old('file_no', $booking->file_no ?? '') }}" class="form-control" placeholder="Enter file no">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Vehicle <span class="text-danger">*</span></label>
                                <select name="vehicle_id" id="vehicle_id" class="form-control" required>
                                    <option value="">Select Vehicle</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" data-type="{{ $vehicle->vehicle_type }}" {{ old('vehicle_id', $booking->vehicle_id ?? '') == $vehicle->id ? 'selected' : '' }}>
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
                                        <option value="{{ $driver->id }}" {{ old('driver_id', $booking->driver_id ?? '') == $driver->id ? 'selected' : '' }}>
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
                                        <option value="{{ $helper->id }}" {{ old('helper_id', $booking->helper_id ?? '') == $helper->id ? 'selected' : '' }}>
                                            {{ $helper->user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
<div class="col-md-4">
    <div class="form-group">
        <label for="trip_category_id">Trip Category</label>
        <div class="input-group">
            <select name="trip_category_id" id="trip_category_id" class="form-control">
                <option value="">Select Category</option>
                @foreach($tripCategories as $category)
                    <option value="{{ $category->id }}" 
                        {{ old('trip_category_id', $booking->trip_category_id ?? '') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <div class="input-group-append">
                <button type="button" class="btn btn-success" id="addCategoryBtn">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <label for="trip_route_id">Trip Route</label>
        <div class="input-group">
            <select name="trip_route_id" id="trip_route_id" class="form-control">
                <option value="">Select Route</option>
                @if(isset($booking) && $booking->trip_route_id)
                    @php
                        $selectedRoute = \App\Models\TripRoute::find($booking->trip_route_id);
                    @endphp
                    @if($selectedRoute)
                        <option value="{{ $selectedRoute->id }}" selected>{{ $selectedRoute->title }}</option>
                    @endif
                @endif
            </select>
            <div class="input-group-append">
                <button type="button" class="btn btn-success" id="addRouteBtn">
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
                                    <input id="rate_per_day" name="rate_per_day" type="number" step="0.01" value="{{ old('rate_per_day', $booking->rate_per_day ?? '0') }}" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Pickup Destination</label>
                                <input type="text" name="from_destination" value="{{ old('from_destination', $booking->from_destination ?? '') }}" class="form-control" placeholder="Enter pickup location">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Dropout Destination</label>
                                <input type="text" name="to_destination" value="{{ old('to_destination', $booking->to_destination ?? '') }}" class="form-control" placeholder="Enter dropoff location">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>No. of People</label>
                                <input type="number" id="no_of_people" name="no_of_people" value="{{ old('no_of_people', $booking->no_of_people ?? '') }}" class="form-control" min="1">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Start Date <span class="text-danger">*</span></label>
                                <input type="date" id="start_date" name="start_date"  value="{{ old('start_date', $booking->start_date ?? $start ?? now()->format('Y-m-d')) }}"  class="form-control" required>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Start Time <span class="text-danger">*</span></label>
                                <input type="time" id="start_time" name="start_time" value="{{ old('start_time', $booking->start_time ?? $start ?? '') }}" class="form-control" required>
                            </div>
                        </div>

                      <!-- Alternative: Simple icon button version -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>End Date <span class="text-danger">*</span></label>
                            <div class="input-group">
                                @php
                                    // Set default value to today if no session data or old input is present
                                    $endDateValue = session('warning_message') && session('end_date') ? session('end_date') : old('end_date', $booking->end_date ?? $end ?? date('Y-m-d'));
                                @endphp
                                <input type="date" id="end_date" name="end_date" value="{{ $endDateValue }}" class="form-control" required>
                            
                            </div>
                        </div>
                    </div>

                        {{-- <div class="col-md-2">
                            <div class="form-group">
                                <label>End Time <span class="text-danger"></span></label>
                                <input type="time" id="end_time" name="end_time" value="{{ old('end_time', $booking->end_time ?? $start ?? '') }}" class="form-control" required>
                            </div>
                        </div> --}}

                        {{-- <div class="col-md-2">
                            <div class="form-group">
                                <label>No. of Hours</label>
                                <input type="number" id="no_of_hours" name="no_of_hours" value="{{ old('no_of_hours', $booking->no_of_hours ?? '') }}" class="form-control">
                                <small class="text-muted">Auto-calculated</small>
                            </div>
                        </div> --}}

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="confirmed" {{ old('status', $booking->status ?? '') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="pending" {{ old('status', $booking->status ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="cancelled" {{ old('status', $booking->status ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h4 class="mb-3">Financial Details</h4>
                    
                    <div class="row">
                        

                        {{-- <div class="col-md-3">
                            <div class="form-group">
                                <label>Sub Total</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">रू</span>
                                    </div>
                                    <input id="sub_total" name="sub_total" type="number" step="0.01" value="{{ old('sub_total', $booking->sub_total ?? '0') }}" class="form-control" readonly>
                                </div>
                            </div>
                        </div> --}}

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Discount Type</label>
                                <select id="discount_amount_type" name="discount_amount_type" class="form-control">
                                    <option value="amount" {{ old('discount_amount_type', $booking->discount_amount_type ?? '') == 'amount' ? 'selected' : '' }}>Fixed Amount</option>
                                    <option value="percentage" {{ old('discount_amount_type', $booking->discount_amount_type ?? '') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Discount</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="discount_symbol">रू</span>
                                    </div>
                                    <input id="discount" name="discount" type="number" step="0.01" value="{{ old('discount', $booking->discount ?? '0') }}" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Apply VAT (13%)</label>
                                <select name="vat" id="vat" class="form-control">
                                    <option value="0" {{ old('vat', $booking->vat ?? '0') == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('vat', $booking->vat ?? '') == '1' ? 'selected' : '' }}>Yes</option>
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
                                    <input id="vat_amount" name="tax" type="number" step="0.01" value="{{ old('tax', $booking->tax ?? '0') }}" class="form-control" readonly>
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
                                    <input id="total_amount" name="total_amount" type="number" step="0.01" value="{{ old('total_amount', $booking->total_amount ?? '0') }}" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h4 class="mb-3">Payment Information</h4>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Paid Amount</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">रू</span>
                                    </div>
                                    <input name="paid_amount" id="paid_amount" type="number" step="0.01" value="{{ old('paid_amount', $booking->paid_amount ?? '0') }}" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Remaining Balance</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">रू</span>
                                    </div>
                                    <input id="remaining_balance" name="remaining_balance" type="number" step="0.01" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Payment Method</label>
                                <select name="payment_method" class="form-control">
                                    <option value="">Select Payment Method</option>
                                    <option value="cash" {{ old('payment_method', $booking->payment_method ?? '') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ old('payment_method', $booking->payment_method ?? '') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="card" {{ old('payment_method', $booking->payment_method ?? '') == 'card' ? 'selected' : '' }}>Card</option>
                                    <option value="online" {{ old('payment_method', $booking->payment_method ?? '') == 'online' ? 'selected' : '' }}>Online</option>
                                    <option value="cheque" {{ old('payment_method', $booking->payment_method ?? '') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Payment Status</label>
                                <select name="payment_status" class="form-control">
                                    <option value="0" {{ old('payment_status', $booking->payment_status ?? '') == '0' ? 'selected' : '' }}>Pending</option>
                                    <option value="1" {{ old('payment_status', $booking->payment_status ?? '') == '1' ? 'selected' : '' }}>Paid</option>
                                    <option value="2" {{ old('payment_status', $booking->payment_status ?? '2') == '2' ? 'selected' : '' }}>Partial</option>
                                </select>
                            </div>
                        </div>

                        @php
                            $payment_date_date = date('Y-m-d');
                            if(isset($booking) && $booking->payment_date){
                                $payment_date = $booking->payment_date;
                                $payment_date_implode = explode(' ', $payment_date);
                                if(!empty($payment_date_implode)){
                                    $payment_date_date = $payment_date_implode[0];
                                }
                            }
                        @endphp

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Payment Date</label>
                                <input type="date" name="payment_date" value="{{ $payment_date_date }}" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-9">
                            <div class="form-group">
                                <label>Payment Notes</label>
                                <textarea name="payment_note" rows="2" class="form-control">{{ old('payment_note', $booking->payment_note ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <hr>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Signage Information</label>
                                <textarea name="signage_information" id="signageInformation" class="form-control" rows="3" placeholder="Describe signage details...">{{ $booking->signage_information ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Additional Notes</label>
                                <textarea name="notes" rows="2" class="form-control" placeholder="Any additional notes...">{{ old('notes', $booking->notes ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> {{ isset($booking) ? 'Update Booking' : 'Save Booking' }}
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </button>
                </div>
            </form>
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
        </div>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    const VAT_RATE = 0.13; // 13%

    // Function to calculate hours between start and end datetime
    function calculateHours() {
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
                $("#no_of_hours").val(diffHours.toFixed(1));
                return diffHours;
            } else {
                $("#no_of_hours").val(0);
                return 0;
            }
        }
        return 0;
    }

    // Copy start date to end date
    $('#start_date').change(function() {
    var startDate = $('#start_date').val();
    if (startDate) {
        $('#end_date').val(startDate);
        // Trigger change event to recalculate totals
        $('#end_date').trigger('change');
        
        // Optional: Show success message
        var btn = $(this);
        var originalHtml = btn.html();
        btn.html('<i class="fas fa-check"></i>');
        setTimeout(function() {
            btn.html(originalHtml);
        }, 1000);
    } else {
        // Show warning if start date is empty
        alert('Please select a start date first');
    }
    });

    // Function to calculate days from hours
   function calculateDays() {
            var start_date = $("#start_date").val();
            var end_date = $("#end_date").val();
            var no_of_hours = $("#no_of_hours").val();
            var days = 0;

            if (no_of_hours !== '' && no_of_hours > 0) {
                days = parseFloat(no_of_hours) / 24;
            } else if (start_date && end_date) {
                var start = new Date(start_date);
                var end = new Date(end_date);
                
                var diffTime = end - start;
                days = diffTime / (1000 * 60 * 60 * 24);
                if (days < 0) days = 0;
                // If same day, at least 1 day
                if (days === 0 && start_date === end_date) {
                    days = 1;
                }
            }
            
            return days;
        }

    // Main calculation function
    function calculateTotal() {
        var days = calculateDays();
        var rate_per_day = parseFloat($("#rate_per_day").val()) || 0;
        
        // Calculate Sub Total
        var sub_total = days * rate_per_day;
        $("#sub_total").val(sub_total.toFixed(2));

        // Calculate Discount
        var discount = parseFloat($("#discount").val()) || 0;
        var discount_type = $("#discount_amount_type").val();
        var discount_amount = 0;

        if (discount > 0) {
            if (discount_type === 'percentage') {
                discount_amount = sub_total * (discount / 100);
            } else {
                discount_amount = discount;
            }
        }

        // Amount after discount (taxable amount)
        var after_discount = Math.max(0, sub_total - discount_amount);

        // Calculate VAT (13% on amount after discount)
        var apply_vat = $("#vat").val() == '1';
        var vat_amount = 0;
        
        if (apply_vat && after_discount > 0) {
            vat_amount = after_discount * VAT_RATE;
        }
        $("#vat_amount").val(vat_amount.toFixed(2));

        // Calculate Total
        var total = after_discount + vat_amount;
        $("#total_amount").val(total.toFixed(2));

        // Update remaining balance
        updateRemainingBalance();

        console.log('Calculation:', {
            days: days,
            rate: rate_per_day,
            sub_total: sub_total,
            discount: discount,
            discount_type: discount_type,
            discount_amount: discount_amount,
            after_discount: after_discount,
            vat: vat_amount,
            total: total
        });
    }

    // Update remaining balance
    function updateRemainingBalance() {
        var total = parseFloat($("#total_amount").val()) || 0;
        var paid = parseFloat($("#paid_amount").val()) || 0;
        var remaining = total - paid;
        $("#remaining_balance").val(remaining.toFixed(2));
    }

    // Update discount symbol
    function updateDiscountSymbol() {
        var type = $("#discount_amount_type").val();
        $("#discount_symbol").text(type === 'percentage' ? '%' : 'रू');
    }

    // Event Listeners
    $("#start_date, #start_time, #end_date, #end_time").on("change", function() {
        calculateHours();
        calculateTotal();
    });

    $("#rate_per_day, #discount, #discount_amount_type, #vat").on("change keyup", function() {
        calculateTotal();
        updateDiscountSymbol();
    });

    $("#paid_amount").on("keyup change", function() {
        updateRemainingBalance();
    });

    // Trip Category Change - Load Routes
    $('#trip_category_id').change(function() {
        var category_id = $(this).val();
        $('#trip_route_id').html('<option value="">Loading...</option>');

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

                    $('#trip_route_id').html(options);
                    
                    // If editing, set the previously selected route
                    @if(isset($booking) && $booking->trip_route_id)
                        $('#trip_route_id').val('{{ $booking->trip_route_id }}');
                    @endif
                },
                error: function() {
                    $('#trip_route_id').html('<option value="">Error loading routes</option>');
                }
            });
        } else {
            $('#trip_route_id').html('<option value="">Select Route</option>');
        }
    });

    // Route selection - Set rate based on vehicle type
    $('#trip_route_id').change(function() {
        var vehicleType = $('#vehicle_id option:selected').data('type');
        var selected = $(this).find(':selected');
        var rate = 0;

        if (vehicleType && selected.val()) {
            vehicleType = vehicleType.toLowerCase();
            rate = selected.data(vehicleType) || 0;
            
            if (rate > 0) {
                $('#rate_per_day').val(rate);
                calculateTotal();
            }
        }
    });

    // Vehicle change - Update rate if route is selected
    $('#vehicle_id').change(function() {
        if ($('#trip_route_id').val()) {
            $('#trip_route_id').trigger('change');
        }
    });

    // Auto-trigger category change on page load for edit mode
    @if(isset($booking) && $booking->trip_category_id)
        $('#trip_category_id').trigger('change');
    @endif

    // Initial calculations for edit mode
    @if(isset($booking))
        setTimeout(function() {
            calculateHours();
            calculateTotal();
            updateDiscountSymbol();
        }, 500);
    @endif

    // Initial discount symbol
    updateDiscountSymbol();
});

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
                $('#customer_id').val(selectedId).trigger('change');
            }
        }
    });
}

function reloadCategory(selectedId = null) {
    $.ajax({
        url: "{{ route('admin.ajax.trip-categories.list') }}",
        type: "GET",
        success: function (data) {
            let options = '<option value="">Select Category</option>';
            
            data.forEach(function (category) {
                options += `<option value="${category.id}">${category.name}</option>`;
            });
            
            $('#trip_category_id').html(options);
            
            if (selectedId) {
                $('#trip_category_id').val(selectedId).trigger('change');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading categories:', error);
            $('#trip_category_id').html('<option value="">Error loading categories</option>');
        }
    });
}

function reloadCategoryDropdown(selectedId = null) {
    $.ajax({
        url: "{{ route('admin.ajax.trip-categories.list') }}",
        type: "GET",
        success: function (data) {
            let options = '<option value="">Select Category</option>';
            
            data.forEach(function (category) {
                options += `<option value="${category.id}">${category.name}</option>`;
            });
            
            // Update both the booking form select AND the route modal select
            $('#trip_category_id, #r_category').html(options);

            if (selectedId) {
                $('#trip_category_id, #r_category').val(selectedId).trigger('change');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading categories:', error);
            $('#trip_category_id, #r_category').html('<option value="">Error loading categories</option>');
        }
    });
}

function reloadRoutes(selectedId = null) {
    $.ajax({
        url: "{{ route('admin.ajax.trip-routes.list') }}",
        type: "GET",
        success: function (data) {
            let options = '<option value="">Select Route</option>';
            
            data.forEach(function (route) {
                // Use 'title' instead of 'name' since that's what your database uses
                options += `<option value="${route.id}">${route.title}</option>`;
            });
            
            $('#trip_route_id').html(options);
            
            if (selectedId) {
                $('#trip_route_id').val(selectedId).trigger('change');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading routes:', error);
            $('#trip_route_id').html('<option value="">Error loading routes</option>');
        }
    });
}




// OPEN MODALS
$('#addCustomerBtn').click(() => $('#customerModal').modal('show'));
$('#addCategoryBtn').click(() => $('#categoryModal').modal('show'));
$('#addRouteBtn').click(() => $('#routeModal').modal('show'));


// SAVE CUSTOMER
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

            console.log(res);

            if (res.success) {

                // 🔥 reload dropdown from DB
                reloadCustomers(res.id);

                $('#customerModal').modal('hide');
            }
        },
        error: function (err) {
            console.log(err.responseText);
        }
    });

});


$('#saveCategory').click(function () {

    $.ajax({
        url: "{{ route('admin.ajax.trip-categories.store') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            name: $('#cat_name').val(),
            description: $('#cat_desc').val()
        },
        success: function (res) {

            console.log(res);

            if (res.success) {

                // 🔥 reload dropdown from DB
                reloadCategoryDropdown(res.id);

                $('#categoryModal').modal('hide');
                $('#cat_name, #cat_desc').val('');
            }
        },
        error: function (err) {
            console.log(err.responseText);
        }
    });

});


$('#saveRoute').click(function () {
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
        success: function (res) {
            console.log(res);
            
            if (res.success) {
                // reload routes from DB
                reloadRoutes(res.id);
                
                // Optionally, also update the category filter to show new route
                $('#trip_category_id').trigger('change');
                
                $('#routeModal').modal('hide');
                
                // Clear form
                $('#r_title, #r_km, #r_car, #r_hiace, #r_coaster, #r_bus').val('');
            }
        },
        error: function (err) {
            console.error('Error saving route:', err.responseText);
            alert('Error saving route. Please check console for details.');
        }
    });
});


// SAVE CATEGORY
// $('#saveCategory').click(function () {
//     $.post("{{ route('admin.ajax.trip-categories.store') }}", {
//         _token: "{{ csrf_token() }}",
//         name: $('#cat_name').val(),
//         description: $('#cat_desc').val()
//     }, function (res) {
//         $('#trip_category_id').append(`<option value="${res.id}" selected>${res.name}</option>`);
//         $('#categoryModal').modal('hide');
//     });
// });


// SAVE ROUTE
// $('#saveRoute').click(function () {
//     $.post("{{ route('admin.ajax.trip-routes.store') }}", {
//         _token: "{{ csrf_token() }}",
//         trip_category_id: $('#r_category').val(),
//         title: $('#r_title').val(),
//         km: $('#r_km').val(),
//         car_price: $('#r_car').val(),
//         hiace_price: $('#r_hiace').val(),
//         coaster_price: $('#r_coaster').val(),
//         bus_price: $('#r_bus').val()
//     }, function (res) {
//         $('#trip_route_id').append(`<option value="${res.id}" selected>${res.title}</option>`);
//         $('#routeModal').modal('hide');
//     });
// });
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

.btn-same-date {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border-left: none;
}
</style>
@endsection