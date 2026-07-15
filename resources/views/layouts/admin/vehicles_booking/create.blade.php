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
{{-- <div class="col-md-4">
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
</div> --}}

                        
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
                    {{-- ITINERARY SECTION --}}
                        <div class="col-md-12">
                            <hr>
                            <h4 class="mb-3"><i class="fas fa-route mr-1"></i> Itinerary (Day-wise Plan)</h4>

                            <table class="table table-bordered table-sm" id="itineraryTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width:14%">Date</th>
                                        <th style="width:16%">From</th>
                                        <th style="width:16%">To</th>
                                        <th style="width:10%">Est. KM</th>
                                        <th style="width:10%">Est. Hours</th>
                                        <th style="width:10%">Overnight</th>
                                        <th style="width:10%">Est. Price</th>
                                        <th style="width:14%">Notes</th>
                                        <th style="width:5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itineraryRows">
                                    {{-- rows injected by JS --}}
                                </tbody>
                            </table>

                            <button type="button" class="btn btn-sm btn-outline-primary" id="addItineraryRow">
                                <i class="fas fa-plus mr-1"></i> Add Day
                            </button>
                        </div>

                    <hr>
                    <h4 class="mb-3">Financial Details</h4>
                    
                    <div class="row">

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
@endsection

@section('scripts')
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script type="text/javascript">
// Define VAT_RATE globally
const VAT_RATE = 0.13;

// ===== GLOBAL FUNCTIONS (outside document.ready) =====

// Function to calculate days
function calculateDays() {
    var start_date = $("#start_date").val();
    var end_date = $("#end_date").val();

    if (start_date && end_date) {
        var start = new Date(start_date);
        var end = new Date(end_date);
        var diffTime = end - start;
        var days = diffTime / (1000 * 60 * 60 * 24);
        if (days < 0) days = 0;
        return days + 1; 
    }
    return 1;
}

// Update remaining balance
function updateRemainingBalance() {
    var total = parseFloat($("#total_amount").val()) || 0;
    var paid = parseFloat($("#paid_amount").val()) || 0;
    var remaining = total - paid;
    $("#remaining_balance").val(remaining.toFixed(2));
}

// Main calculation function - NOW GLOBAL
function calculateTotal() {
    var days = calculateDays();
    var rate_per_day = parseFloat($("#rate_per_day").val()) || 0;
    
    var sub_total = rate_per_day; 
    $("#sub_total").val(sub_total.toFixed(2));

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

    var after_discount = Math.max(0, sub_total - discount_amount);
    var apply_vat = $("#vat").val() == '1';
    var vat_amount = 0;
    
    if (apply_vat && after_discount > 0) {
        vat_amount = after_discount * VAT_RATE;
    }
    $("#vat_amount").val(vat_amount.toFixed(2));

    var total = after_discount + vat_amount;
    $("#total_amount").val(total.toFixed(2));

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

// ===== DOCUMENT READY =====
$(document).ready(function() {
    let suppressRouteRateCalc = false;

    $('#trip_category_id, #trip_route_id').select2({
        placeholder: 'Select an option',
        allowClear: true,
        dropdownAutoWidth: false
    });

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
            $('#end_date').trigger('change');
            
            var btn = $(this);
            var originalHtml = btn.html();
            btn.html('<i class="fas fa-check"></i>');
            setTimeout(function() {
                btn.html(originalHtml);
            }, 1000);
        } else {
            alert('Please select a start date first');
        }
    });

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
        $('#trip_route_id').html('<option value="">Loading...</option>').trigger('change');

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
                    
                    @if(isset($booking) && $booking->trip_route_id)
                        suppressRouteRateCalc = true;
                        $('#trip_route_id').val('{{ $booking->trip_route_id }}').trigger('change');
                        suppressRouteRateCalc = false;
                    @else
                        $('#trip_route_id').trigger('change');
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
        if (suppressRouteRateCalc) return;
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

// ===== ITINERARY FUNCTIONS (outside document.ready) =====
let itineraryIndex = 0;
let vehicleRates = { per_km_rate: 0, per_hour_rate: 0, overnight_price: 0 };

function calcRowPrice($row) {
    var km = parseFloat($row.find('.itinerary-est-km').val()) || 0;
    var hrs = parseFloat($row.find('.itinerary-est-hours').val()) || 0;
    var overnight = $row.find('.itinerary-overnight').val() == '1';

    var price = (km * vehicleRates.per_km_rate)
        + (hrs * vehicleRates.per_hour_rate)
        + (overnight ? vehicleRates.overnight_price : 0);

    $row.find('.itinerary-est-price').val(price.toFixed(2));
    $row.find('.itinerary-per-km-rate').val(vehicleRates.per_km_rate);
    $row.find('.itinerary-per-hour-rate').val(vehicleRates.per_hour_rate);
    $row.find('.itinerary-overnight-charge').val(overnight ? vehicleRates.overnight_price : 0);

    updateItineraryTotal();
}

function recalcAllRows() {
    $('#itineraryRows tr').each(function() {
        calcRowPrice($(this));
    });
}

function updateItineraryTotal() {
    var total = 0;
    $('.itinerary-est-price').each(function() {
        total += parseFloat($(this).val()) || 0;
    });
    $('#rate_per_day').val(total.toFixed(2));
    // Now calculateTotal is globally accessible
    calculateTotal();
}

function addItineraryRow(data = {}) {
    var idx = itineraryIndex++;
    var overnightYes = data.is_overnight == 1 || data.is_overnight === true ? 'selected' : '';
    var overnightNo = !overnightYes ? 'selected' : '';
    const today = new Date().toISOString().split('T')[0];

    var row = `
        <tr data-idx="${idx}">
            <td>
                <input type="date" name="itineraries[${idx}][itinerary_date]"
                    class="form-control form-control-sm"
                    value="${data.itinerary_date || today}">
            </td>
            <td><input type="text" name="itineraries[${idx}][from_destination]" class="form-control form-control-sm" value="${data.from_destination ?? ''}" placeholder="From"></td>
            <td><input type="text" name="itineraries[${idx}][to_destination]" class="form-control form-control-sm" value="${data.to_destination ?? ''}" placeholder="To"></td>
            <td><input type="number" step="0.01" name="itineraries[${idx}][est_km]" class="form-control form-control-sm itinerary-est-km" value="${data.est_km ?? 0}"></td>
            <td><input type="number" step="0.01" name="itineraries[${idx}][est_hours]" class="form-control form-control-sm itinerary-est-hours" value="${data.est_hours ?? 0}"></td>
            <td>
                <select name="itineraries[${idx}][is_overnight]" class="form-control form-control-sm itinerary-overnight">
                    <option value="0" ${overnightNo}>No</option>
                    <option value="1" ${overnightYes}>Yes</option>
                </select>
            </td>
            <td>
                <input type="number" step="0.01" name="itineraries[${idx}][est_price]" class="form-control form-control-sm itinerary-est-price" value="${data.est_price ?? 0}" readonly>
                <input type="hidden" name="itineraries[${idx}][per_km_rate]" class="itinerary-per-km-rate" value="${data.per_km_rate ?? 0}">
                <input type="hidden" name="itineraries[${idx}][per_hour_rate]" class="itinerary-per-hour-rate" value="${data.per_hour_rate ?? 0}">
                <input type="hidden" name="itineraries[${idx}][overnight_charge]" class="itinerary-overnight-charge" value="${data.overnight_charge ?? 0}">
            </td>
            <td><input type="text" name="itineraries[${idx}][notes]" class="form-control form-control-sm" value="${data.notes ?? ''}" placeholder="Notes"></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger removeItineraryRow"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
    `;

    $('#itineraryRows').append(row);

    if (!data.est_price && !data.itinerary_date) {
        calcRowPrice($('#itineraryRows tr[data-idx="' + idx + '"]'));
    }
}

// ===== EVENT HANDLERS (outside document.ready) =====
$(document).on('input change', '.itinerary-est-km, .itinerary-est-hours, .itinerary-overnight', function() {
    calcRowPrice($(this).closest('tr'));
});

$('#addItineraryRow').click(function() {
    addItineraryRow();
});

$(document).on('click', '.removeItineraryRow', function() {
    $(this).closest('tr').remove();
    updateItineraryTotal();
});

// Fetch vehicle rates whenever vehicle changes
$('#vehicle_id').on('change', function() {
    var vehicleId = $(this).val();

    if (!vehicleId) {
        vehicleRates = { per_km_rate: 0, per_hour_rate: 0, overnight_price: 0 };
        recalcAllRows();
        return;
    }

    $.ajax({
        url: '/dashboard/get-vehicle-rate/' + vehicleId,
        type: 'GET',
        success: function(rate) {
            vehicleRates = {
                per_km_rate: parseFloat(rate.per_km_rate) || 0,
                per_hour_rate: parseFloat(rate.per_hour_rate) || 0,
                overnight_price: parseFloat(rate.overnight_price) || 0
            };
            recalcAllRows();
        },
        error: function() {
            vehicleRates = { per_km_rate: 0, per_hour_rate: 0, overnight_price: 0 };
            console.error('Could not load vehicle rates');
        }
    });
});

// Prefill rows in edit mode
@if(isset($booking) && $booking->itineraries && $booking->itineraries->count())
    var existingItineraries = {!! $booking->itineraries->map(function($i) {
        return [
            'itinerary_date' => optional($i->itinerary_date)->format('Y-m-d'),
            'from_destination' => $i->from_destination,
            'to_destination' => $i->to_destination,
            'est_km' => $i->est_km,
            'est_hours' => $i->est_hours,
            'is_overnight' => $i->is_overnight ? 1 : 0,
            'est_price' => $i->est_price,
            'per_km_rate' => $i->per_km_rate,
            'per_hour_rate' => $i->per_hour_rate,
            'overnight_charge' => $i->overnight_charge,
            'notes' => $i->notes,
        ];
    })->values()->toJson() !!};
    existingItineraries.forEach(function(item) {
        addItineraryRow(item);
    });

    @if($booking->vehicle_id)
        $('#vehicle_id').trigger('change');
    @endif
@else
    @if(isset($booking) && $booking->vehicle_id)
        $('#vehicle_id').trigger('change');
    @endif
    addItineraryRow();
@endif

// ===== MODAL FUNCTIONS =====
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

function reloadCategoryDropdown(selectedId = null) {
    $.ajax({
        url: "{{ route('admin.ajax.trip-categories.list') }}",
        type: "GET",
        success: function (data) {
            let options = '<option value="">Select Category</option>';
            data.forEach(function (category) {
                options += `<option value="${category.id}">${category.name}</option>`;
            });
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

// SAVE CATEGORY
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
            if (res.success) {
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

// SAVE ROUTE
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
            if (res.success) {
                reloadRoutes(res.id);
                $('#trip_category_id').trigger('change');
                $('#routeModal').modal('hide');
                $('#r_title, #r_km, #r_car, #r_hiace, #r_coaster, #r_bus').val('');
            }
        },
        error: function (err) {
            console.error('Error saving route:', err.responseText);
            alert('Error saving route. Please check console for details.');
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

.btn-same-date {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border-left: none;
}

/* Fix Select2 + Bootstrap input-group layout */
.input-group > .select2-container {
    flex: 1 1 auto;
    width: 1% !important; /* forces it to shrink/grow like .form-control does in input-group */
}

.input-group > .select2-container .select2-selection--single {
    height: calc(2.25rem + 2px); /* match Bootstrap .form-control height */
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    display: flex;
    align-items: center;
}

/* When Select2 sits with a button after it, kill the right rounding
   so it looks continuous with the input-group-append button */
.input-group > .select2-container:not(:last-child) .select2-selection--single {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.select2-selection__rendered {
    line-height: calc(2.25rem) !important;
    padding-left: 0.75rem !important;
}

.select2-selection__arrow {
    height: calc(2.25rem + 2px) !important;
}

/* Keep the clear (x) and arrow from overlapping/stacking oddly */
.select2-selection__clear {
    margin-right: 6px;
}

/* Match focus state to other inputs */
.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default .select2-selection--single:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}
</style>
@endsection