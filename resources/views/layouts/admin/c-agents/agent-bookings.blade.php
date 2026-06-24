@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1>Agent Commission Management</h1>
        <div>
            <a href="{{ route('admin.agents.index') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-users"></i> Back to Agents
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
                <h3>{{ $totalBookings }}</h3>
                <p>Total Bookings</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-check"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>Rs. {{ number_format($totalCommission, 2) }}</h3>
                <p>Total Commission Payable</p>
            </div>
            <div class="icon"><i class="fas fa-percentage"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>Rs. {{ number_format($paidCommission, 2) }}</h3>
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
        <label>Agent</label>
        <select id="agentFilter" class="form-control form-control-sm">
            <option value="">All Agents</option>
            @foreach($agents as $agent)
                <option value="{{ $agent->agent_code }}"
                    {{ request('agent_code') == $agent->agent_code ? 'selected' : '' }}>
                    {{ $agent->user->name ?? 'N/A' }} ({{ $agent->agent_code }})
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
    <th>Agent Code</th>
    <th>Agent Name</th>
    <th>Contact</th>
    <th>Total Amount</th>
    <th>Booking Amount(Excl. VAT)</th>
    <th>Commission Rate</th>
    <th>Commission Amount</th>
    <th>Booking Date</th>
    <th>Action</th>
</tr>
</thead>
<tbody>
@forelse($bookings as $booking)
@php

    $commissionAmount = $booking->commissionAmt ?? 0;
    $isPaid = in_array($booking->id, $paidBookingIds);
    $isFailed= in_array($booking->id, $failBookingIds);
    
@endphp
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $booking->file_no ?? 'N/A' }}</td>
    <td><span class="badge bg-info">{{ $booking->agent_code }}</span></td>
    <td>{{ $booking->agent->user->name ?? 'N/A' }}</td>
    <td>{{ $booking->agent->contact_number ?? 'N/A' }}</td>
    <td>Rs. {{ number_format($booking->total_amount ?? 0, 2) }}</td>
    <td>Rs. {{ number_format($booking->commissionBase, 2) }}</td>
    <td>{{ $booking->agent->commission_rate ?? 0 }}%</td>
    <td><strong class="text-primary">Rs. {{ number_format($commissionAmount, 2) }}</strong></td>
    <td>{{ $booking->created_at->format('Y-m-d') }}</td>
    <td>
        @if($isPaid)
            <span class="badge bg-success">
                <i class="fas fa-check mr-1"></i> Paid
            </span>
            <a href="{{ route('admin.agent-bookings.commission-statement', $booking->id) }}"
                target="_blank"
                class="btn btn-sm btn-outline-primary ml-1">
                <i class="fas fa-file-invoice"></i> Statement
             </a>
        @elseif($isFailed)
            <span class="badge bg-danger">
                <i class="fas fa-times mr-1"></i> Failed
            </span>
        @else
            <button class="btn btn-sm btn-info"
                onclick="payCommission('{{ $booking->agent_code }}', {{ $booking->id }}, {{ $commissionAmount }})">
                <i class="fas fa-university"></i> Pay Commission
            </button>
        @endif
    </td>
</tr>
@empty
<tr>
    <td colspan="10" class="text-center text-muted py-4">No agent bookings found</td>
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
                    <i class="fas fa-university mr-2"></i> Pay Agent Commission via Bank Transfer
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="bookingId">
                <input type="hidden" id="agentCode">

                <div class="alert alert-info" id="agentDetails">
                    <i class="fa fa-spinner fa-spin"></i> Loading agent details...
                </div>

                <div class="form-group">
                    <label><i class="fas fa-building mr-1"></i> Agent Bank Details</label>
                    <div class="alert alert-secondary" id="bankDetailsDisplay">
                        Loading bank details...
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-money-bill-wave mr-1"></i> Commission Amount</label>
                    <div class="alert alert-success" id="amountDisplay">
                        <strong>Rs. 0.00</strong>
                    </div>
                </div>

                <div class="form-group">
                    <label for="bankRemarks"><i class="fas fa-comment mr-1"></i> Remarks</label>
                    <textarea class="form-control" id="bankRemarks" rows="3"
                              placeholder="Enter payment remarks..."></textarea>
                </div>

                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Confirmation Required:</strong> Please verify the bank details before proceeding.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="confirmBankPaymentBtn" onclick="processTransfer()">
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
        $('#agentCode').val('');
        $('#confirmBankPaymentBtn').prop('disabled', false);
    });
});

function applyFilter() {
    let p = new URLSearchParams();
    let a = $('#agentFilter').val(),
        f = $('#startDateFilter').val(),
        t = $('#endDateFilter').val();
    if (a) p.set('agent_code', a);
    if (f) p.set('start_date', f);
    if (t) p.set('end_date', t);
    window.location.href = "{{ route('admin.agent-bookings.index') }}?" + p.toString();
}

function clearFilters() {
    window.location.href = "{{ route('admin.agent-bookings.index') }}";
}

function payCommission(agentCode, bookingId, commissionAmount) {
    $('#bankRemarks').val('');
    $('#bookingId').val(bookingId);
    $('#agentCode').val(agentCode);
    $('#amountDisplay').html('<strong>Rs. ' + parseFloat(commissionAmount).toFixed(2) + '</strong>');
    $('#agentDetails').html('<i class="fa fa-spinner fa-spin"></i> Loading agent details...');
    $('#bankDetailsDisplay').html('Loading bank details...');
    $('#confirmBankPaymentBtn').prop('disabled', false);

    // Single AJAX call — agentCode is passed as a route segment, not a query param
    $.ajax({
        url: "{{ url('dashboard/agent-bookings/get-commission-details') }}/" + agentCode,
        type: 'GET',
        success: function (response) {
            if (!response.success) {
                toastr.error(response.message || 'Failed to load agent details');
                return;
            }

            let d = response.data;

            $('#agentDetails').html(
                '<strong>Agent:</strong> ' + d.agent_name + '<br>' +
                '<strong>Code:</strong> ' + d.agent_code + '<br>' +
                '<strong>Commission Rate:</strong> ' + d.commission_rate + '%'
            );

            if (d.has_bank_details) {
                $('#bankDetailsDisplay').html(
                    '<i class="fas fa-check-circle text-success mr-1"></i>' +
                    '<strong>Bank:</strong> ' + d.bank_name + '<br>' +
                    '<strong>Account Holder:</strong> ' + d.bank_account_name + '<br>' +
                    '<strong>Account Number:</strong> ' + d.bank_account_number + '<br>' +
                    '<strong>Bank Code:</strong> ' + (d.bank_code || 'N/A')
                );
                $('#confirmBankPaymentBtn').prop('disabled', false);
            } else {
                $('#bankDetailsDisplay').html(
                    '<i class="fas fa-exclamation-triangle text-danger mr-1"></i>' +
                    '<strong>No bank details found for this agent.</strong><br>' +
                    'Please update the agent profile with bank information first.'
                );
                $('#confirmBankPaymentBtn').prop('disabled', true);
            }

            $('#bankPaymentModal').modal('show');
        },
        error: function (xhr) {
            toastr.error(xhr.responseJSON?.message || 'Failed to load agent details');
        }
    });
}

function processTransfer() {
    let agentCode = $('#agentCode').val();
    let bookingId = $('#bookingId').val();
    let remarks   = $('#bankRemarks').val();
    let amount    = $('#amountDisplay').text();

    if (!confirm(
        'Confirm bank transfer?\n\n' +
        'Agent Code: ' + agentCode + '\n' +
        'Amount: ' + amount + '\n\n' +
        'This action cannot be undone.'
    )) return;

    let btn = $('#confirmBankPaymentBtn');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');

    $.ajax({
        url: "{{ route('admin.agent-bookings.transfer-commission') }}",
        type: 'POST',
        data: {
            agent_code: agentCode,
            booking_id: bookingId,
            remarks:    remarks,
            _token:     "{{ csrf_token() }}"
        },
        success: function (response) {
            if (response.success) {
                $('#bankPaymentModal').modal('hide');
                toastr.success(response.message || 'Commission paid successfully!');
                setTimeout(() => window.location.reload(), 2000);
            } else {
                toastr.error(response.message || 'Transfer failed');
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Initiate Transfer');
            }
        },
        error: function (xhr) {
            toastr.error(xhr.responseJSON?.message || 'Bank transfer failed');
            btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Initiate Transfer');
        }
    });
}


</script>
@endsection