@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1>Owner Commission Management</h1>
        <div>
            <a href="{{ route('admin.vehicleowner.index') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-users"></i> Back to Owners
            </a>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">

@include('layouts.admin_theme.alert')

{{-- Summary Cards --}}
<div class="row mb-3">
    <div class="col-md-4">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $bookings->count() }}</h3>
                <p>Total Bookings</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-check"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>Rs. {{ number_format($totalOwnerPayable, 2) }}</h3>
                <p>Total Owner Payable</p>
            </div>
            <div class="icon"><i class="fas fa-percentage"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>Rs. {{ number_format($paidOwnerCommission ?? 0, 2) }}</h3>
                <p>Commission Paid</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
</div>

<div class="card card-primary card-outline">
<div class="card-body">

{{-- Filters --}}
<div class="row mb-3">
    <div class="col-md-3">
        <label>Owner</label>
        <select id="ownerFilter" class="form-control form-control-sm">
            <option value="">All Owners</option>
            @foreach($owners ?? [] as $owner)
                <option value="{{ $owner->id }}"
                    {{ request('owner_id') == $owner->id ? 'selected' : '' }}>
                    {{ $owner->name ?? 'N/A' }} (ID: {{ $owner->id }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label>From Date</label>
        <input type="date" id="startDateFilter" class="form-control form-control-sm"
               value="{{ request('start_date') }}">
    </div>
    <div class="col-md-3">
        <label>To Date</label>
        <input type="date" id="endDateFilter" class="form-control form-control-sm"
               value="{{ request('end_date') }}">
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <button class="btn btn-primary btn-sm mr-1" onclick="applyFilter()">
            <i class="fa fa-filter"></i> Filter
        </button>
        <button class="btn btn-secondary btn-sm" onclick="clearFilters()">
            <i class="fa fa-refresh"></i> Reset
        </button>
    </div>
</div>

<div class="table-responsive">
<table id="dataTable" class="table table-bordered table-striped show-search-bar">
<thead>
<tr>
    <th>SN</th>
    <th>File No</th>
    <th>Owner Name</th>
    <th>Vehicle</th>
    <th>Total Amount</th>
    <th>Amount (Excl. VAT)</th>
    <th>Platform Commission</th>
    <th>Agent Commission</th>
    <th>Owner Payable</th>
    <th>Booking Date</th>
    <th>Action</th>
</tr>
</thead>
<tbody>
@forelse($bookings as $booking)
@php
    $isPaid = isset($paidBookingIds) && in_array($booking->id, $paidBookingIds);
    $isFailed = isset($failBookingIds) && in_array($booking->id, $failBookingIds);
    $ownerPayable = $booking->ownerPayable ?? 0;
    $ownerId = $booking->vehicle?->vehicle_owner_id ?? null;
@endphp
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $booking->file_no ?? 'N/A' }}</td>
    <td>{{ $booking->vehicle?->vehicleOwner?->name ?? 'N/A' }}</td>
    <td>{{ $booking->vehicle?->vehicle_name ?? 'N/A' }}</td>
    <td>Rs. {{ number_format($booking->total_amount ?? 0, 2) }}</td>
    <td>Rs. {{ number_format($booking->amountExcludingTax ?? 0, 2) }}</td>
    <td>Rs. {{ number_format($booking->platformCommission ?? 0, 2) }}</td>
    <td>Rs. {{ number_format($booking->agentCommission ?? 0, 2) }}</td>
    <td><strong class="text-success">Rs. {{ number_format($ownerPayable, 2) }}</strong></td>
    <td>{{ $booking->created_at->format('Y-m-d') }}</td>
    <td>
        @if($isPaid)
            <span class="badge bg-success">
                <i class="fas fa-check mr-1"></i> Paid
            </span>
        @elseif($isFailed)
            <span class="badge bg-danger">
                <i class="fas fa-times mr-1"></i> Failed
            </span>
        @elseif($ownerPayable > 0 && $ownerId)
            <button class="btn btn-sm btn-info"
                onclick="payOwner('{{ $ownerId }}', {{ $booking->id }}, {{ $ownerPayable }})">
                <i class="fas fa-university"></i> Pay Owner
            </button>
        @else
            <span class="badge bg-secondary">
                <i class="fas fa-minus mr-1"></i> No Payment
            </span>
        @endif
    </td>
</tr>
@empty
<tr>
    <td colspan="11" class="text-center text-muted py-4">No owner bookings found</td>
</tr>
@endforelse
</tbody>
</table>
</div>

</div>
</div>
</div>
</section>

{{-- Bank Payment Modal --}}
<div class="modal fade" id="bankPaymentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-university mr-2"></i> Pay Owner Commission via Bank Transfer
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="bookingId">
                <input type="hidden" id="ownerId">

                <div class="alert alert-info" id="ownerDetails">
                    <i class="fa fa-spinner fa-spin"></i> Loading owner details...
                </div>

                <div class="form-group">
                    <label><i class="fas fa-building mr-1"></i> Owner Bank Details</label>
                    <div class="alert alert-secondary" id="bankDetailsDisplay">
                        Loading bank details...
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-wallet mr-1"></i> Wallet Details</label>
                    <div class="alert alert-light" id="walletDetailsDisplay">
                        Loading wallet details...
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-money-bill-wave mr-1"></i> Owner Payable Amount</label>
                    <div class="alert alert-success" id="amountDisplay">
                        <strong>Rs. 0.00</strong>
                    </div>
                </div>

                <div class="form-group">
                    <label for="paymentMethod"><i class="fas fa-credit-card mr-1"></i> Payment Method</label>
                    <select class="form-control" id="paymentMethod">
                        <option value="bank_transfer">Bank Transfer</option>
                        {{-- <option value="wallet_transfer">Wallet Transfer</option> --}}
                    </select>
                </div>

                <div class="form-group">
                    <label for="bankRemarks"><i class="fas fa-comment mr-1"></i> Remarks</label>
                    <textarea class="form-control" id="bankRemarks" rows="3"
                              placeholder="Enter payment remarks..."></textarea>
                </div>

                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Confirmation Required:</strong> Please verify the owner details before proceeding.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="confirmBankPaymentBtn" onclick="processOwnerTransfer()">
                    <i class="fas fa-paper-plane mr-1"></i> Initiate Transfer
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<script>
$(document).ready(function () {
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

    $('#bankPaymentModal').on('hidden.bs.modal', function () {
        $('#bankRemarks').val('');
        $('#bookingId').val('');
        $('#ownerId').val('');
        $('#paymentMethod').val('bank_transfer');
        $('#confirmBankPaymentBtn').prop('disabled', false);
    });

    // Toggle payment method display
    $('#paymentMethod').on('change', function() {
        var method = $(this).val();
        if (method === 'bank_transfer') {
            $('#bankDetailsDisplay').closest('.form-group').show();
            $('#walletDetailsDisplay').closest('.form-group').hide();
        } else {
            $('#bankDetailsDisplay').closest('.form-group').hide();
            $('#walletDetailsDisplay').closest('.form-group').show();
        }
    });
});

function applyFilter() {
    let p = new URLSearchParams();
    let a = $('#ownerFilter').val(),
        f = $('#startDateFilter').val(),
        t = $('#endDateFilter').val();
    if (a) p.set('owner_id', a);
    if (f) p.set('start_date', f);
    if (t) p.set('end_date', t);
    window.location.href = "{{ route('admin.owner-bookings.index') }}?" + p.toString();
}

function clearFilters() {
    window.location.href = "{{ route('admin.owner-bookings.index') }}";
}

function payOwner(ownerId, bookingId, ownerPayable) {
    $('#bankRemarks').val('');
    $('#bookingId').val(bookingId);
    $('#ownerId').val(ownerId);
    $('#amountDisplay').html('<strong>Rs. ' + parseFloat(ownerPayable).toFixed(2) + '</strong>');
    $('#ownerDetails').html('<i class="fa fa-spinner fa-spin"></i> Loading owner details...');
    $('#bankDetailsDisplay').html('Loading bank details...');
    $('#walletDetailsDisplay').html('Loading wallet details...');
    $('#confirmBankPaymentBtn').prop('disabled', false);

    // Show bank details by default
    $('#bankDetailsDisplay').closest('.form-group').show();
    $('#walletDetailsDisplay').closest('.form-group').hide();

    // AJAX call to get owner payment details
    $.ajax({
        url: "{{ url('dashboard/owner-bookings/get-owner-payment-details') }}/" + ownerId,
        type: 'GET',
        success: function (response) {
            if (!response.success) {
                toastr.error(response.message || 'Failed to load owner details');
                return;
            }

            let d = response.data;

            $('#ownerDetails').html(
                '<strong>Owner:</strong> ' + d.owner_name + '<br>' +
                '<strong>Commission Rate:</strong> ' + d.commission_rate + '%'
            );

            // Bank Details
            if (d.has_bank_details) {
                $('#bankDetailsDisplay').html(
                    '<i class="fas fa-check-circle text-success mr-1"></i>' +
                    '<strong>Bank:</strong> ' + (d.bank_name || 'N/A') + '<br>' +
                    '<strong>Account Holder:</strong> ' + (d.bank_account_name || 'N/A') + '<br>' +
                    '<strong>Account Number:</strong> ' + (d.bank_account_number || 'N/A') + '<br>' +
                    '<strong>Bank Code:</strong> ' + (d.bank_code || 'N/A')
                );
            } else {
                $('#bankDetailsDisplay').html(
                    '<i class="fas fa-exclamation-triangle text-danger mr-1"></i>' +
                    '<strong>No bank details found for this owner.</strong><br>' +
                    'Please update the owner profile with bank information first.'
                );
            }

            // Wallet Details
            if (d.wallet_name || d.wallet_number) {
                $('#walletDetailsDisplay').html(
                    '<i class="fas fa-check-circle text-success mr-1"></i>' +
                    '<strong>Wallet:</strong> ' + (d.wallet_name || 'N/A') + '<br>' +
                    '<strong>Wallet Number:</strong> ' + (d.wallet_number || 'N/A')
                );
            } else {
                $('#walletDetailsDisplay').html(
                    '<i class="fas fa-exclamation-triangle text-warning mr-1"></i>' +
                    '<strong>No wallet details found for this owner.</strong>'
                );
            }

            // Check if any payment method is available
            var hasBank = d.has_bank_details;
            var hasWallet = d.wallet_name || d.wallet_number;

            if (!hasBank && !hasWallet) {
                $('#confirmBankPaymentBtn').prop('disabled', true);
                toastr.warning('No payment method available for this owner. Please update bank or wallet details.');
            } else if (hasBank) {
                $('#paymentMethod').val('bank_transfer');
                $('#bankDetailsDisplay').closest('.form-group').show();
                $('#walletDetailsDisplay').closest('.form-group').hide();
            } else if (hasWallet) {
                $('#paymentMethod').val('wallet_transfer');
                $('#bankDetailsDisplay').closest('.form-group').hide();
                $('#walletDetailsDisplay').closest('.form-group').show();
            }

            $('#bankPaymentModal').modal('show');
        },
        error: function (xhr) {
            toastr.error(xhr.responseJSON?.message || 'Failed to load owner details');
        }
    });
}

function processOwnerTransfer() {
    let ownerId = $('#ownerId').val();
    let bookingId = $('#bookingId').val();
    let remarks = $('#bankRemarks').val();
    let amount = $('#amountDisplay').text();
    let paymentMethod = $('#paymentMethod').val();

    if (!confirm(
        'Confirm payment transfer?\n\n' +
        'Owner ID: ' + ownerId + '\n' +
        'Amount: ' + amount + '\n' +
        'Payment Method: ' + paymentMethod.replace('_', ' ').toUpperCase() + '\n\n' +
        'This action cannot be undone.'
    )) return;

    let btn = $('#confirmBankPaymentBtn');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');

    $.ajax({
        url: "{{ route('admin.owner-bookings.owner-transfer-commission') }}",
        type: 'POST',
        data: {
            owner_id: ownerId,
            booking_id: bookingId,
            remarks: remarks,
            payment_method: paymentMethod,
            _token: "{{ csrf_token() }}"
        },
        success: function (response) {
            if (response.success) {
                $('#bankPaymentModal').modal('hide');
                toastr.success(response.message || 'Payment transferred successfully!');
                setTimeout(() => window.location.reload(), 2000);
            } else {
                toastr.error(response.message || 'Transfer failed');
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Initiate Transfer');
            }
        },
        error: function (xhr) {
            var errorMsg = xhr.responseJSON?.message || 'Transfer failed';
            if (xhr.responseJSON?.errors) {
                var errors = Object.values(xhr.responseJSON.errors).flat();
                errorMsg = errors.join(', ');
            }
            toastr.error(errorMsg);
            btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Initiate Transfer');
        }
    });
}
</script>

@endsection