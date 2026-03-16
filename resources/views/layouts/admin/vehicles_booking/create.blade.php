@extends('layouts.admin_theme.container')

@section('dynamicdata')

            <div class="content-header">
                <div class="container-fluid d-flex justify-content-between align-items-center">
                    <h1>
                        {{ isset($booking) ? 'Edit Booking' : 'Add Booking' }}
                    </h1>

                    <a href="{{ route('admin.vehicle_bookings.index') }}"
                       class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <section class="content">
            <div class="container-fluid">
            @include('layouts.admin_theme.alert')

            <div class="card card-primary card-outline">
            <form method="POST"
                  action="{{ isset($booking)
        ? route('admin.vehicle_bookings.update', $booking->id)
        : route('admin.vehicle_bookings.store') }}">

            @csrf
            @if(isset($booking))
                @method('PUT')
            @endif

            <div class="card-body">
            <div class="row">

            {{-- VEHICLE --}}
            <div class="col-md-4">
    <div class="form-group">
        <label>Vehicle *</label>
        <select name="vehicle_id" id="vehicle_id" class="form-control" required>
            <option value="">Select Vehicle</option>
            @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}"
                    data-type="{{ $vehicle->vehicle_type }}"
                    {{ old('vehicle_id', $booking->vehicle_id ?? '') == $vehicle->id ? 'selected' : '' }}>
                    {{ $vehicle->vehicle_name }}
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
                            <option value="{{ $driver->id }}"
                                {{ old('driver_id', $booking->driver_id ?? '') == $driver->id ? 'selected' : '' }}>
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
                    <option value="{{ $helper->id }}"
                        {{ old('helper_id', $booking->helper_id ?? '') == $helper->id ? 'selected' : '' }}>
                        {{ $helper->user->name }}
                    </option>
                @endforeach
            </select>
                </div>
            </div>
            <div class="col-md-4">
            <div class="form-group">
                  <label>Customer</label>
            <select name="customer_id" class="form-control">
                <option value="">Select Customer</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}"
                        {{ old('customer_id', $booking->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>
            </div>
            </div>







            {{-- FROM --}}
            <div class="col-md-4">
                <div class="form-group">
                    <label>From Destination</label>
                    <input type="text" name="from_destination"
                           value="{{ old('from_destination', $booking->from_destination ?? '') }}"
                           class="form-control">
                </div>
            </div>

            {{-- TO --}}
            <div class="col-md-4">
                <div class="form-group">
                    <label>To Destination</label>
                    <input type="text" name="to_destination"
                           value="{{ old('to_destination', $booking->to_destination ?? '') }}"
                           class="form-control">
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>Start K/M</label>
                    <input type="text" name="start_km"
                           value="{{ old('start_km', $booking->start_km ?? '') }}"
                           class="form-control">
                </div>
            </div>

            {{-- TO --}}
            <div class="col-md-4">
                <div class="form-group">
                    <label>End K/M</label>
                    <input type="text" name="end_km"
                           value="{{ old('end_km', $booking->end_km ?? '') }}"
                           class="form-control">
                </div>
            </div>

            {{-- PEOPLE --}}
            <div class="col-md-4">
                <div class="form-group">
                    <label>No. of People</label>
                    <input type="number" id="no_of_people" name="no_of_people"
                           value="{{ old('no_of_people', $booking->no_of_people ?? '') }}"
                           class="form-control">
                </div>
            </div>

            {{-- START DATE --}}
            <div class="col-md-3">
                <div class="form-group">
                    <label>Start Date *</label>
                    <input type="date"
                    id="start_date"
                           name="start_date"
                           value="{{ old(
        'start_date',
        $booking->start_date ?? $start ?? ''
    ) }}"
                           class="form-control"
                           required>

                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Start Time *</label>
                    <input type="time"
                        id="start_time"
                           name="start_time"
                           value="{{ old(
        'start_time',
        $booking->start_time ?? $start ?? ''
    ) }}"
                           class="form-control"
                           required>
                </div>
            </div>

            {{-- END DATE --}}
            <div class="col-md-3">
                <div class="form-group">
                    <label>End Date *</label>

                    @php
    $endDateValue = session('warning_message') && session('end_date')
        ? session('end_date')
        : old('end_date', $booking->end_date ?? $end ?? '');
                    @endphp

                    <input type="date"
                        id="end_date" 
                        name="end_date" 
                        value="{{ $endDateValue }}" 
                        class="form-control" 
                        required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>END Time *</label>
                    <input type="time"
                           name="end_time"
                           value="{{ old(
        'end_time',
        $booking->end_time ?? $start ?? ''
    ) }}"
                           class="form-control"
                           required>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group">
                    <label>No. of Hours</label>
                    <input type="number" id="no_of_hours" name="no_of_hours"
                           value="{{ old('no_of_hours', $booking->no_of_hours ?? '') }}"
                           class="form-control">
                </div>
            </div>

            {{-- STATUS --}}
            <div class="col-md-4">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="pending"
                            {{ old('status', $booking->status ?? '') == 'pending' ? 'selected' : '' }}>
                            Pending
                        </option>
                        <option value="confirmed"
                            {{ old('status', $booking->status ?? '') == 'confirmed' ? 'selected' : '' }}>
                            Confirmed
                        </option>
                        <option value="cancelled"
                            {{ old('status', $booking->status ?? '') == 'cancelled' ? 'selected' : '' }}>
                            Cancelled
                        </option>
                    </select>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label>Approx fuel Litre</label>
                    <input name="approx_fuel_litre" type="number"
                           value="{{ old('approx_fuel_litre', $booking->approx_fuel_litre ?? '') }}"
                           class="form-control">
                </div>
            </div>

                    <div class="col-md-12">
                        <h4 class="mb-3">Signage Information</h4>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Information on the signage<span class="text-danger"></span></label>
                            <textarea name="signage_information" 
                                    id="signageInformation"
                                    class="form-control" 
                                    rows="4"
                                    placeholder="Please describe the signage in detail">{{ $booking->signage_information ?? '' }}</textarea>
                        </div>
                    </div>

            {{-- NOTES --}}
            <div class="col-md-12">
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" rows="3"
                              class="form-control">{{ old('notes', $booking->notes ?? '') }}</textarea>
                </div>
            </div>

            </div>

            <div class="row">
                    <div class="col-md-12"><p><h2>Payment Information</h2></p>
                    </div>
                    </div>
                <div class="row">
                    <div class="row">

<div class="col-md-4">
<div class="form-group">
<label>Trip Category</label>
<select name="trip_category_id" id="trip_category_id" class="form-control">
<option value="">Select Category</option>
@foreach($tripCategories as $category)
<option value="{{ $category->id }}"
{{ old('trip_category_id', $booking->trip_category_id ?? '') == $category->id ? 'selected' : '' }}>
{{ $category->name }}
</option>
@endforeach
</select>
</div>
</div>

<div class="col-md-4">
<div class="form-group">
<label>Trip Route</label>
<select name="trip_route_id" id="trip_route_id" class="form-control">
<option value="">Select Route</option>
</select>
</div>
</div>

</div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Rate Per Day</label>
                            <input id="rate_per_day" name="rate_per_day" type="text" value="{{ old('rate_per_day', $booking->rate_per_day ?? '0') }}"
                                class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Sub Total</label>
                            <input id="sub_total" name="sub_total" type="text" value="{{ old('sub_total', $booking->sub_total ?? '0') }}"
                                class="form-control">
                        </div>
                    </div>
                  
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Discount Amount Type</label>
                            <select id="discount_amount_type" name="discount_amount_type" class="form-control">
                                <option value="amount"
                                    {{ old('discount_amount_type', $booking->discount_amount_type ?? '') == 'amount' ? 'selected' : '' }}>
                                    Amount
                                </option>
                                <option value="percentage"
                                    {{ old('discount_amount_type', $booking->discount_amount_type ?? '') == 'percentage' ? 'selected' : '' }}>
                                    Percentage
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Discount</label>
                            <input id="discount" name="discount" type="text" value="{{ old('discount', $booking->discount ?? '0') }}"
                                class="form-control">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Total Amount</label>
                            <input id="total_amount" name="total_amount" type="text" value="{{ old('total_amount', $booking->total_amount ?? '0') }}"
                                class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Payment Status</label>
                            <select name="payment_status" class="form-control">
                                <option value="0"
                                    {{ old('payment_status', $booking->payment_status ?? '') == '0' ? 'selected' : '' }}>
                                    Pending
                                </option>
                                <option value="1"
                                    {{ old('payment_status', $booking->payment_status ?? '') == '1' ? 'selected' : '' }}>
                                    Paid
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Paid Amount</label>
                            <input name="paid_amount" type="text" value="{{ old('paid_amount', $booking->paid_amount ?? '0') }}"
                                class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Payment Method</label>
                            <select name="payment_method" class="form-control">
                                <option value="">--Select Payment Method</option>
                                <option value="cash"
                                    {{ old('payment_method', $booking->payment_method ?? '') == 'cash' ? 'selected' : '' }}>
                                    Cash
                                </option>
                                <option value="bank_transfer"
                                    {{ old('payment_method', $booking->payment_method ?? '') == 'bank_transfer' ? 'selected' : '' }}>
                                    Bank Transfer
                                </option>
                                <option value="card"
                                    {{ old('payment_method', $booking->payment_method ?? '') == 'card' ? 'selected' : '' }}>
                                    Card
                                </option>
                                <option value="online"
                                    {{ old('payment_method', $booking->payment_method ?? '') == 'online' ? 'selected' : '' }}>
                                    Online
                                </option>
                                <option value="cheque"
                                    {{ old('payment_method', $booking->payment_method ?? '') == 'cheque' ? 'selected' : '' }}>
                                    cheque
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                @php
                $payment_date_date = date('Y-m-d');
                $payment_date_time = date('H:i');
                if(isset($booking)){
                    $payment_date = $booking->payment_date;
                    $payment_date_implode = explode(' ',$payment_date);

                    if(!empty($payment_date_implode)){
                        $payment_date_date = $payment_date_implode[0];
                        $payment_date_time = isset($payment_date_implode[1]) ? $payment_date_implode[1]:$payment_date_time ;
                    }
                  
                }
                @endphp 
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Payment Date</label>
                            <input type="date"
                                name="payment_date"
                                value="{{ $payment_date_date }}"
                                class="form-control"
                                required>

                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Payment Time *</label>
                            <input type="time"
                                name="payment_time"
                                value="{{ $payment_date_time }}"
                                class="form-control"
                                required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>payment Notes</label>
                            <textarea name="payment_note" rows="3"
                                    class="form-control">{{ old('payment_note', $booking->payment_note ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        {{ isset($booking) ? 'Update Booking' : 'Add Booking' }}
                    </button>
                </div>
            </div>

            

            </form>
            </div>
            </div>
            </section>
@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        
        // Function to calculate number of days
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

        function calculateAndSetSubTotal() {
            var days = calculateDays();
            var rate_per_day = parseFloat($("#rate_per_day").val()) || 0;
            
            console.log("Calculating subtotal - Days:", days, "Rate:", rate_per_day); // For debugging
            
            var sub_total = rate_per_day * days;
            
            $("#sub_total").val(sub_total.toFixed(2));
            
            calculateAndSetTotalAmount();
            
            return sub_total;
        }

        // Function to calculate and set total amount with discount
        function calculateAndSetTotalAmount() {
            var sub_total = parseFloat($("#sub_total").val()) || 0;
            var discount = parseFloat($("#discount").val()) || 0;
            var discount_amount_type = $("#discount_amount_type").val();
            
            var total_amount = sub_total;
            
            if (discount > 0) {
                if (discount_amount_type === 'percentage') {
                    var discount_amount = sub_total * (discount / 100);
                    total_amount = sub_total - discount_amount;
                } else {
                    total_amount = sub_total - discount;
                }
            }
            
            if (total_amount < 0) total_amount = 0;
            
            $("#total_amount").val(total_amount.toFixed(2));
            
            console.log("Total calculated:", total_amount); 
        }

        $("#rate_per_day").on("keyup change", function() {
            console.log("Rate per day changed to:", $(this).val());
            calculateAndSetSubTotal();
        });

        $("#start_date, #end_date, #no_of_hours").on("change keyup", function() {
            console.log("Date/hour changed");
            calculateAndSetSubTotal();
        });

        $("#discount, #discount_amount_type").on("change keyup", function() {
            console.log("Discount changed");
            calculateAndSetTotalAmount();
        });

        // $("#no_of_people").on("change keyup", function() {
        // });

        $('#trip_category_id').change(function() {
            var category_id = $(this).val();
            $('#trip_route_id').html('<option value="">Loading...</option>');

            $.get('/dashboard/get-trip-routes/' + category_id, function(routes) {
                var options = '<option value="">Select Route</option>';
                
                routes.forEach(function(route) {
                    options += '<option value="' + route.id + '" ' +
                        'data-car="' + (route.car_price || 0) + '" ' +
                        'data-hiace="' + (route.hiace_price || 0) + '" ' +
                        'data-coaster="' + (route.coaster_price || 0) + '" ' +
                        'data-bus="' + (route.bus_price || 0) + '">' +
                        route.title +
                        '</option>';
                });

                $('#trip_route_id').html(options);
                
                @if(isset($booking) && $booking->trip_route_id)
                    setTimeout(function() {
                        $('#trip_route_id').val('{{ $booking->trip_route_id }}');
                        $('#trip_route_id').trigger('change');
                    }, 100);
                @endif
            });
        });

        $('#trip_route_id').change(function() {
            var vehicleType = $('#vehicle_id option:selected').data('type');
            var selected = $(this).find(':selected');
            var rate = 0;

            if (vehicleType) {
                vehicleType = vehicleType.toLowerCase();
                if (vehicleType == 'car') {
                    rate = selected.data('car');
                } else if (vehicleType == 'hiace') {
                    rate = selected.data('hiace');
                } else if (vehicleType == 'coaster') {
                    rate = selected.data('coaster');
                } else if (vehicleType == 'bus') {
                    rate = selected.data('bus');
                }
            }

            console.log("Route selected - Vehicle type:", vehicleType, "Rate:", rate);
            
            if (rate > 0) {
                $('#rate_per_day').val(rate);
                $('#rate_per_day').trigger('change');
            } else {
                $('#rate_per_day').val('');
                $('#sub_total').val('0');
                $('#total_amount').val('0');
            }
        });

        @if(isset($booking) && $booking->trip_category_id)
            $(document).ready(function() {
                $('#trip_category_id').val('{{ $booking->trip_category_id }}');
                $('#trip_category_id').trigger('change');
            });
        @endif

        $('#vehicle_id').change(function() {
            if ($('#trip_route_id').val()) {
                $('#trip_route_id').trigger('change');
            }
        });

        @if(isset($booking))
            $(document).ready(function() {
                setTimeout(function() {
                    console.log("Initial calculation for edit mode");
                    if ($("#rate_per_day").val() > 0) {
                        calculateAndSetSubTotal();
                    }
                }, 500);
            });
        @endif

        $(window).on('load', function() {
            setTimeout(function() {
                if ($("#rate_per_day").val() > 0 && ($("#start_date").val() || $("#no_of_hours").val())) {
                    calculateAndSetSubTotal();
                }
            }, 300);
        });

    });
</script>