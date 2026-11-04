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
                <h3 class="card-title"><i class="fas fa-car mr-1"></i> Multiple Bookings Creation</h3>
            </div>

            <form method="POST" action="{{ route('admin.vehicle_bookings.multiple.store') }}" id="multipleBookingForm">
                @csrf
                <div class="card-body">

                    {{-- COMMON INFORMATION --}}
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
                                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-success" id="addCustomerBtn"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Passenger</label>
                                        <input type="text" name="passenger" class="form-control" placeholder="Enter passenger">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>File No</label>
                                        <input type="text" name="file_no" class="form-control" placeholder="Enter file no">
                                    </div>
                                </div>

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
                                                    <option value="{{ $driver->id }}">{{ $driver->user->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-success" id="addDriverBtn"><i class="fas fa-plus"></i></button>
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
                                                    <option value="{{ $helper->id }}">{{ $helper->user->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-success" id="addHelperBtn"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Signage Information</label>
                                        <textarea name="signage_information" class="form-control" rows="2" placeholder="Describe signage details..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-success btn-sm" id="addBookingRowBtn">
                            <i class="fas fa-plus mr-1"></i> Add Another Booking Row
                        </button>
                    </div>

                    <div class="card card-success">
                        <div class="card-header"><h4 class="card-title">Booking Details</h4></div>
                        <div class="card-body">
                            <div id="bookingsRowsContainer"></div>
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

{{-- MODALS --}}
<div class="modal fade" id="customerModal">
    <div class="modal-dialog"><div class="modal-content p-3">
        <h5>Add Customer</h5>
        <input id="c_name" class="form-control mb-2" placeholder="Name">
        <input id="c_phone" class="form-control mb-2" placeholder="Phone">
        <input id="c_email" class="form-control mb-2" placeholder="Email">
        <input id="c_address" class="form-control mb-2" placeholder="Address">
        <button class="btn btn-primary" id="saveCustomer">Save</button>
    </div></div>
</div>

<div class="modal fade" id="driverModal">
    <div class="modal-dialog"><div class="modal-content p-3">
        <h5>Add Driver</h5>
        <input id="d_name" class="form-control mb-2" placeholder="Driver Name">
        <input id="d_phone" class="form-control mb-2" placeholder="Phone">
        <input id="d_email" class="form-control mb-2" placeholder="Email">
        <input id="d_license" class="form-control mb-2" placeholder="License Number">
        <button class="btn btn-primary" id="saveDriver">Save</button>
    </div></div>
</div>

<div class="modal fade" id="helperModal">
    <div class="modal-dialog"><div class="modal-content p-3">
        <h5>Add Helper</h5>
        <input id="h_name" class="form-control mb-2" placeholder="Helper Name">
        <input id="h_phone" class="form-control mb-2" placeholder="Phone">
        <input id="h_email" class="form-control mb-2" placeholder="Email">
        <button class="btn btn-primary" id="saveHelper">Save</button>
    </div></div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
const VAT_RATE = 0.13;
let rowCounter = 0;
let vehicleRates = { per_km_rate: 0, per_hour_rate: 0, overnight_price: 0 };
const itinCounters = {};

function todayStr() { return new Date().toISOString().split('T')[0]; }

/* ---------- ROW TEMPLATE ---------- */
function bookingRowHtml(i) {
    const p = `bookings[${i}]`;
    return `
    <div class="booking-row card card-info mb-3" data-row-index="${i}">
        <div class="card-header">
            <h5 class="card-title">Booking #${i}</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-danger btn-sm remove-row-btn"><i class="fas fa-trash"></i> Remove</button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><div class="form-group">
                    <label>Pickup Destination</label>
                    <input type="text" name="${p}[from_destination]" class="form-control" placeholder="Enter pickup location">
                </div></div>
                <div class="col-md-4"><div class="form-group">
                    <label>Dropout Destination</label>
                    <input type="text" name="${p}[to_destination]" class="form-control" placeholder="Enter dropoff location">
                </div></div>
                <div class="col-md-4"><div class="form-group">
                    <label>No. of People</label>
                    <input type="number" name="${p}[no_of_people]" class="form-control" min="1">
                </div></div>

                <div class="col-md-3"><div class="form-group">
                    <label>Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="${p}[start_date]" class="form-control start-date" value="${todayStr()}" required>
                </div></div>
                <div class="col-md-2"><div class="form-group">
                    <label>Start Time <span class="text-danger">*</span></label>
                    <input type="time" name="${p}[start_time]" class="form-control" required>
                </div></div>
                <div class="col-md-3"><div class="form-group">
                    <label>End Date <span class="text-danger">*</span></label>
                    <input type="date" name="${p}[end_date]" class="form-control end-date" value="${todayStr()}" required>
                </div></div>
                <div class="col-md-4"><div class="form-group">
                    <label>Status</label>
                    <select name="${p}[status]" class="form-control">
                        <option value="confirmed">Confirmed</option>
                        <option value="pending">Pending</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div></div>
            </div>

            <hr>
            <h6><i class="fas fa-route mr-1"></i> Itinerary (Day-wise Plan)</h6>
            <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr>
                        <th style="width:14%">Date</th><th style="width:15%">From</th><th style="width:15%">To</th>
                        <th style="width:9%">Est. KM</th><th style="width:9%">Est. Hours</th><th style="width:9%">Overnight</th>
                        <th style="width:10%">Est. Price</th><th style="width:14%">Notes</th><th style="width:5%"></th>
                    </tr>
                </thead>
                <tbody class="itinerary-rows"></tbody>
            </table>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary add-itinerary-btn mb-3">
                <i class="fas fa-plus mr-1"></i> Add Day
            </button>

            <hr>
            <h6>Financial Details</h6>
            <div class="row">
                <div class="col-md-3"><div class="form-group">
                    <label>Amount</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">रू</span></div>
                        <input type="number" step="0.01" name="${p}[rate_per_day]" class="form-control rate-per-day" value="0">
                    </div>
                </div></div>
                <div class="col-md-3"><div class="form-group">
                    <label>Discount Type</label>
                    <select name="${p}[discount_amount_type]" class="form-control discount-type">
                        <option value="amount">Fixed Amount</option>
                        <option value="percentage">Percentage</option>
                    </select>
                </div></div>
                <div class="col-md-3"><div class="form-group">
                    <label>Discount</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text discount-symbol">रू</span></div>
                        <input type="number" step="0.01" name="${p}[discount]" class="form-control discount" value="0">
                    </div>
                </div></div>
                <div class="col-md-3"><div class="form-group">
                    <label>Apply VAT (13%)</label>
                    <select name="${p}[vat]" class="form-control vat">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div></div>
                <div class="col-md-3"><div class="form-group">
                    <label>VAT Amount</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">रू</span></div>
                        <input type="number" step="0.01" name="${p}[tax]" class="form-control vat-amount" value="0" readonly>
                    </div>
                </div></div>
                <div class="col-md-3"><div class="form-group">
                    <label>Total Amount</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">रू</span></div>
                        <input type="number" step="0.01" name="${p}[total_amount]" class="form-control total-amount" value="0" readonly>
                    </div>
                </div></div>
            </div>

            <hr>
            <h6>Payment Information</h6>
            <div class="row">
                <div class="col-md-3"><div class="form-group">
                    <label>Paid Amount</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">रू</span></div>
                        <input type="number" step="0.01" name="${p}[paid_amount]" class="form-control paid-amount" value="0">
                    </div>
                </div></div>
                <div class="col-md-3"><div class="form-group">
                    <label>Remaining Balance</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">रू</span></div>
                        <input type="number" step="0.01" name="${p}[remaining_balance]" class="form-control remaining-balance" value="0" readonly>
                    </div>
                </div></div>
                <div class="col-md-3"><div class="form-group">
                    <label>Payment Method</label>
                    <select name="${p}[payment_method]" class="form-control">
                        <option value="">Select Payment Method</option>
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="card">Card</option>
                        <option value="online">Online</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div></div>
                <div class="col-md-3"><div class="form-group">
                    <label>Payment Status</label>
                    <select name="${p}[payment_status]" class="form-control">
                        <option value="0">Pending</option>
                        <option value="1">Paid</option>
                        <option value="2">Partial</option>
                    </select>
                </div></div>
                <div class="col-md-3"><div class="form-group">
                    <label>Payment Date</label>
                    <input type="date" name="${p}[payment_date]" class="form-control" value="${todayStr()}">
                </div></div>
                <div class="col-md-9"><div class="form-group">
                    <label>Payment Notes</label>
                    <textarea name="${p}[payment_note]" rows="2" class="form-control"></textarea>
                </div></div>
                <div class="col-md-12"><div class="form-group">
                    <label>Additional Notes</label>
                    <textarea name="${p}[notes]" rows="2" class="form-control" placeholder="Any additional notes..."></textarea>
                </div></div>
            </div>
        </div>
    </div>`;
}

/* ---------- ITINERARY ---------- */
function addItineraryRow($row, data = {}) {
    const i = $row.data('row-index');
    const j = itinCounters[i]++;
    const n = `bookings[${i}][itineraries][${j}]`;
    const date = data.itinerary_date || $row.find('.start-date').val() || todayStr();

    const tr = `
    <tr>
        <td><input type="date" name="${n}[itinerary_date]" class="form-control form-control-sm" value="${date}"></td>
        <td><input type="text" name="${n}[from_destination]" class="form-control form-control-sm" placeholder="From"></td>
        <td><input type="text" name="${n}[to_destination]" class="form-control form-control-sm" placeholder="To"></td>
        <td><input type="number" step="0.01" name="${n}[est_km]" class="form-control form-control-sm itin-km" value="0"></td>
        <td><input type="number" step="0.01" name="${n}[est_hours]" class="form-control form-control-sm itin-hours" value="0"></td>
        <td>
            <select name="${n}[is_overnight]" class="form-control form-control-sm itin-overnight">
                <option value="0">No</option><option value="1">Yes</option>
            </select>
        </td>
        <td>
            <input type="number" step="0.01" name="${n}[est_price]" class="form-control form-control-sm itin-price" value="0" readonly>
            <input type="hidden" name="${n}[per_km_rate]" class="itin-per-km" value="0">
            <input type="hidden" name="${n}[per_hour_rate]" class="itin-per-hour" value="0">
            <input type="hidden" name="${n}[overnight_charge]" class="itin-overnight-charge" value="0">
        </td>
        <td><input type="text" name="${n}[notes]" class="form-control form-control-sm" placeholder="Notes"></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-itin-btn"><i class="fas fa-trash"></i></button></td>
    </tr>`;
    const $tr = $(tr).appendTo($row.find('.itinerary-rows'));
    calcItineraryRow($tr);
}

function calcItineraryRow($tr) {
    const km = parseFloat($tr.find('.itin-km').val()) || 0;
    const hrs = parseFloat($tr.find('.itin-hours').val()) || 0;
    const overnight = $tr.find('.itin-overnight').val() == '1';
    const overnightCharge = overnight ? vehicleRates.overnight_price : 0;

    const price = (km * vehicleRates.per_km_rate) + (hrs * vehicleRates.per_hour_rate) + overnightCharge;

    $tr.find('.itin-price').val(price.toFixed(2));
    $tr.find('.itin-per-km').val(vehicleRates.per_km_rate);
    $tr.find('.itin-per-hour').val(vehicleRates.per_hour_rate);
    $tr.find('.itin-overnight-charge').val(overnightCharge);

    updateRowTotals($tr.closest('.booking-row'), true);
}

/* ---------- TOTALS ---------- */
function updateRowTotals($row, fromItinerary = false) {
    if (fromItinerary) {
        let sum = 0;
        $row.find('.itin-price').each(function () { sum += parseFloat($(this).val()) || 0; });
        $row.find('.rate-per-day').val(sum.toFixed(2));
    }

    const subTotal = parseFloat($row.find('.rate-per-day').val()) || 0;
    const discount = parseFloat($row.find('.discount').val()) || 0;
    const type = $row.find('.discount-type').val();

    let discountAmount = 0;
    if (discount > 0) discountAmount = type === 'percentage' ? subTotal * discount / 100 : discount;

    const afterDiscount = Math.max(0, subTotal - discountAmount);
    const vat = ($row.find('.vat').val() == '1' && afterDiscount > 0) ? afterDiscount * VAT_RATE : 0;
    const total = afterDiscount + vat;
    const paid = parseFloat($row.find('.paid-amount').val()) || 0;

    $row.find('.vat-amount').val(vat.toFixed(2));
    $row.find('.total-amount').val(total.toFixed(2));
    $row.find('.remaining-balance').val((total - paid).toFixed(2));
    $row.find('.discount-symbol').text(type === 'percentage' ? '%' : 'रू');
}

/* ---------- ADD / REMOVE BOOKING ROW ---------- */
function addBookingRow() {
    rowCounter++;
    itinCounters[rowCounter] = 0;
    const $row = $(bookingRowHtml(rowCounter)).appendTo('#bookingsRowsContainer');
    addItineraryRow($row);
    updateRowTotals($row, true);
}

$('#addBookingRowBtn').on('click', addBookingRow);

$(document).on('click', '.remove-row-btn', function () {
    if ($('.booking-row').length === 1) { alert('At least one booking is required.'); return; }
    $(this).closest('.booking-row').remove();
});

$(document).on('click', '.add-itinerary-btn', function () {
    addItineraryRow($(this).closest('.booking-row'));
});

$(document).on('click', '.remove-itin-btn', function () {
    const $row = $(this).closest('.booking-row');
    $(this).closest('tr').remove();
    updateRowTotals($row, true);
});

/* ---------- EVENTS ---------- */
$(document).on('input change', '.itin-km, .itin-hours, .itin-overnight', function () {
    calcItineraryRow($(this).closest('tr'));
});

$(document).on('input change', '.rate-per-day, .discount, .discount-type, .vat, .paid-amount', function () {
    updateRowTotals($(this).closest('.booking-row'));
});

// start date -> copy to end date
$(document).on('change', '.start-date', function () {
    const $row = $(this).closest('.booking-row');
    if ($(this).val()) $row.find('.end-date').val($(this).val());
});

// vehicle change -> load rates -> recalc every itinerary row in every booking
$('#vehicle_id').on('change', function () {
    const type = $(this).find(':selected').data('type');
    if (!type) {
        vehicleRates = { per_km_rate: 0, per_hour_rate: 0, overnight_price: 0 };
        recalcAll();
        return;
    }
    $.get('/dashboard/get-vehicle-rate/' + encodeURIComponent(type), function (rate) {
        vehicleRates = {
            per_km_rate: parseFloat(rate.per_km_rate) || 0,
            per_hour_rate: parseFloat(rate.per_hour_rate) || 0,
            overnight_price: parseFloat(rate.overnight_price) || 0
        };
        recalcAll();
    }).fail(function () {
        vehicleRates = { per_km_rate: 0, per_hour_rate: 0, overnight_price: 0 };
        console.error('Could not load vehicle rates');
    });
});

function recalcAll() {
    $('.itinerary-rows tr').each(function () { calcItineraryRow($(this)); });
}

/* ---------- MODALS ---------- */
$('#addCustomerBtn').click(() => $('#customerModal').modal('show'));
$('#addDriverBtn').click(() => $('#driverModal').modal('show'));
$('#addHelperBtn').click(() => $('#helperModal').modal('show'));

$('#saveCustomer').click(function () {
    $.post("{{ route('admin.ajax.customers.store') }}", {
        _token: "{{ csrf_token() }}",
        name: $('#c_name').val(), phone: $('#c_phone').val(),
        email: $('#c_email').val(), address: $('#c_address').val()
    }, function (res) {
        if (res.success) {
            $.get("{{ route('admin.ajax.customers.list') }}", function (data) {
                let o = '<option value="">Select Customer</option>';
                data.forEach(c => o += `<option value="${c.id}">${c.name}</option>`);
                $('#customer_id').html(o).val(res.id);
            });
            $('#customerModal').modal('hide');
            $('#c_name, #c_phone, #c_email, #c_address').val('');
        }
    });
});

$('#saveDriver').click(function () {
    $.post("{{ route('admin.ajax.drivers.store') }}", {
        _token: "{{ csrf_token() }}",
        name: $('#d_name').val(), phone: $('#d_phone').val(),
        email: $('#d_email').val(), license_number: $('#d_license').val()
    }, function (res) {
        if (res.success) {
            let o = '<option value="">Select Driver</option>';
            res.drivers.forEach(d => o += `<option value="${d.id}">${d.user.name}</option>`);
            $('#driver_id').html(o).val(res.id);
            $('#driverModal').modal('hide');
            $('#d_name, #d_phone, #d_email, #d_license').val('');
        }
    });
});

$('#saveHelper').click(function () {
    $.post("{{ route('admin.ajax.helpers.store') }}", {
        _token: "{{ csrf_token() }}",
        name: $('#h_name').val(), phone: $('#h_phone').val(), email: $('#h_email').val()
    }, function (res) {
        if (res.success) {
            let o = '<option value="">Select Helper</option>';
            res.helpers.forEach(h => o += `<option value="${h.id}">${h.user.name}</option>`);
            $('#helper_id').html(o).val(res.id);
            $('#helperModal').modal('hide');
            $('#h_name, #h_phone, #h_email').val('');
        }
    });
});

/* ---------- INIT ---------- */
addBookingRow();
</script>

<style>
.input-group-text { background-color: #e9ecef; }
.form-group label { font-weight: 500; margin-bottom: 0.3rem; }
hr { border-top: 2px solid rgba(0,0,0,.1); }
</style>
@endsection