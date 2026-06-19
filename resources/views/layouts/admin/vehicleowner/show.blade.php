{{-- resources/views/layouts/admin/vehicleowner/show.blade.php --}}

@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Vehicle Owner Details: {{ $vehicleowner->name }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-body">
                @include('layouts.admin_theme.alert')

                {{-- Personal Information --}}
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-user mr-2"></i>Personal Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th style="width: 200px;">Company/Individual Name</th>
                                                <td>{{ $vehicleowner->name }}</td>
                                            </tr>
                                            <tr>
                                                <th>Full Name</th>
                                                <td>{{ $vehicleowner->full_name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Phone</th>
                                                <td>{{ $vehicleowner->phone }}</td>
                                            </tr>
                                            <tr>
                                                <th>Email</th>
                                                <td>{{ $vehicleowner->email ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Address</th>
                                                <td>{{ $vehicleowner->address ?? 'N/A' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th style="width: 200px;">City</th>
                                                <td>{{ $vehicleowner->city ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>State</th>
                                                <td>{{ $vehicleowner->state ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>PAN Number</th>
                                                <td>{{ $vehicleowner->pan_number ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Status</th>
                                                <td>{!! $vehicleowner->status_badge !!}</td>
                                            </tr>
                                            <tr>
                                                <th>Commission Rate</th>
                                                <td>
                                                    @if($vehicleowner->commission_rate)
                                                        <span class="badge bg-warning">{{ $vehicleowner->commission_rate }}%</span>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bank & Wallet Details --}}
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card card-outline card-success">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-university mr-2"></i>Bank Details</h3>
                            </div>
                            <div class="card-body">
                                @if($vehicleowner->bank_name || $vehicleowner->bank_account_number)
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 150px;">Bank Name</th>
                                            <td>{{ $vehicleowner->bank_name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Bank Code</th>
                                            <td>
                                                @if($vehicleowner->bank_code)
                                                    <span class="badge bg-primary">{{ $vehicleowner->bank_code }}</span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Account Holder Name</th>
                                            <td>{{ $vehicleowner->bank_account_name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Account Number</th>
                                            <td>
                                                @if($vehicleowner->bank_account_number)
                                                    <span class="text-success font-weight-bold">
                                                        {{ substr($vehicleowner->bank_account_number, 0, 4) . str_repeat('*', 8) . substr($vehicleowner->bank_account_number, -4) }}
                                                    </span>
                                                   
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                @else
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        No bank details configured for this owner.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-wallet mr-2"></i>Wallet Details</h3>
                            </div>
                            <div class="card-body">
                                @if($vehicleowner->wallet_name || $vehicleowner->wallet_number)
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 150px;">Wallet Name</th>
                                            <td>
                                                @if($vehicleowner->wallet_name)
                                                    <span class="badge bg-info">{{ $vehicleowner->wallet_name }}</span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Wallet Number</th>
                                            <td>
                                                @if($vehicleowner->wallet_number)
                                                    <span class="text-primary font-weight-bold">{{ $vehicleowner->wallet_number }}</span>
                                                    <button class="btn btn-sm btn-link" onclick="copyToClipboard('{{ $vehicleowner->wallet_number }}')">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                @else
                                    <div class="alert alert-secondary mb-0">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        No wallet details configured for this owner.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Associated Vehicles --}}
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card card-outline card-secondary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-car mr-2"></i>Associated Vehicles</h3>
                                <div class="card-tools">
                                    <span class="badge bg-primary">{{ $vehicleowner->vehicles->count() }} Vehicles</span>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($vehicleowner->vehicles->count())
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Vehicle Name</th>
                                                    <th>Brand</th>
                                                    <th>Registration</th>
                                                    <th>Seater</th>
                                                    <th>Fuel Type</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($vehicleowner->vehicles as $vehicle)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $vehicle->vehicle_name ?? 'N/A' }}</td>
                                                        <td>{{ $vehicle->brand ?? 'N/A' }}</td>
                                                        <td>{{ $vehicle->registration_number ?? 'N/A' }}</td>
                                                        <td>{{ $vehicle->seater ?? 'N/A' }}</td>
                                                        <td>{{ $vehicle->fuel_type ?? 'N/A' }}</td>
                                                        <td>
                                                            @if($vehicle->status == 'active')
                                                                <span class="badge bg-success">Active</span>
                                                            @else
                                                                <span class="badge bg-danger">Inactive</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        No vehicles found for this owner.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payment History --}}
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card card-outline card-success">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-money-bill-wave mr-2"></i>Payment History</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span class="badge bg-success ml-2">
                                        Total: Rs. {{ number_format($payments->sum('amount') ?? 0, 2) }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                @php
                                    // Get all vehicle IDs owned by this owner
                                    $vehicleIds = $vehicleowner->vehicles->pluck('id')->toArray();
                                    
                                    // Get payments for bookings that belong to these vehicles
                                    $payments = DB::table('payments')
                                        ->join('vehicle_bookings', 'payments.vehicle_booking_id', '=', 'vehicle_bookings.id')
                                        ->join('vehicles', 'vehicle_bookings.vehicle_id', '=', 'vehicles.id')
                                        ->whereIn('vehicles.id', $vehicleIds)
                                        ->where('payments.payment_type', 'owner_payout')
                                        ->orderBy('payments.created_at', 'desc')
                                        ->select(
                                            'payments.*',
                                            'vehicle_bookings.file_no',
                                            'vehicle_bookings.id as booking_id',
                                            'vehicles.registration_number'
                                        )
                                        ->get();
                                @endphp

                                @if($payments->count())
                                    <div class="table-responsive">
                                       <table id="dataTable" class="table table-bordered table-striped show-search-bar">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Booking #</th>
                                                    <th>File No</th>
                                                    <th>Vehicle</th>
                                                    <th>Amount</th>
                                                    <th>Payment Method</th>
                                                    <th>Status</th>
                                                    <th>Transaction Ref</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($payments as $payment)
                                                    @php
                                                        $statusColors = [
                                                            'completed' => 'success',
                                                            'pending' => 'warning',
                                                            'failed' => 'danger',
                                                            'cancelled' => 'secondary'
                                                        ];
                                                        $statusColor = $statusColors[$payment->status] ?? 'secondary';
                                                        
                                                        $statusIcons = [
                                                            'completed' => 'check-circle',
                                                            'pending' => 'clock',
                                                            'failed' => 'times-circle',
                                                            'cancelled' => 'ban'
                                                        ];
                                                        $statusIcon = $statusIcons[$payment->status] ?? 'circle';
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>
                                                            <a href="{{ route('admin.vehicle_bookings.show', $payment->booking_id ?? 0) }}" 
                                                               class="text-primary" target="_blank">
                                                                #{{ $payment->booking_id ?? 'N/A' }}
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-info">
                                                                {{ $payment->file_no ?? 'N/A' }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $payment->registration_number ?? 'N/A' }}</td>
                                                        <td>
                                                            <span class="font-weight-bold text-success">
                                                                Rs. {{ number_format($payment->amount ?? 0, 2) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @if($payment->payment_method == 'bank_transfer')
                                                                <span class="badge bg-primary">
                                                                    <i class="fas fa-university mr-1"></i> Bank
                                                                </span>
                                                            @elseif($payment->payment_method == 'wallet_transfer')
                                                                <span class="badge bg-info">
                                                                    <i class="fas fa-wallet mr-1"></i> Wallet
                                                                </span>
                                                            @else
                                                                <span class="badge bg-secondary">{{ $payment->payment_method ?? 'N/A' }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-{{ $statusColor }}">
                                                                <i class="fas fa-{{ $statusIcon }} mr-1"></i>
                                                                {{ ucfirst($payment->status ?? 'N/A') }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @if($payment->transaction_reference)
                                                                <span class="text-muted small">{{ $payment->transaction_reference }}</span>
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            {{ $payment->created_at ? \Carbon\Carbon::parse($payment->created_at)->format('d-m-Y H:i:s') : 'N/A' }}
                                                        </td>
                                                        
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="bg-light">
                                                    <th colspan="4" class="text-right">Total:</th>
                                                    <th colspan="6">
                                                        <span class="font-weight-bold text-success">
                                                            Rs. {{ number_format($payments->sum('amount'), 2) }}
                                                        </span>
                                                    </th>
                                                </tr>
                                                <tr class="bg-light">
                                                    <th colspan="4" class="text-right">Completed:</th>
                                                    <th colspan="6">
                                                        <span class="font-weight-bold text-success">
                                                            Rs. {{ number_format($payments->where('status', 'completed')->sum('amount'), 2) }}
                                                        </span>
                                                    </th>
                                                </tr>
                                                <tr class="bg-light">
                                                    <th colspan="4" class="text-right">Pending:</th>
                                                    <th colspan="6">
                                                        <span class="font-weight-bold text-warning">
                                                            Rs. {{ number_format($payments->where('status', 'pending')->sum('amount'), 2) }}
                                                        </span>
                                                    </th>
                                                </tr>
                                                <tr class="bg-light">
                                                    <th colspan="4" class="text-right">Failed:</th>
                                                    <th colspan="6">
                                                        <span class="font-weight-bold text-danger">
                                                            Rs. {{ number_format($payments->where('status', 'failed')->sum('amount'), 2) }}
                                                        </span>
                                                    </th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-secondary mb-0">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        No payment history found for this owner.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payment Summary Stats --}}
                @if($payments->count())
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Payment Summary</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="small-box bg-success">
                                            <div class="inner">
                                                <h3>Rs. {{ number_format($payments->where('status', 'completed')->sum('amount'), 2) }}</h3>
                                                <p>Total Paid</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="small-box bg-warning">
                                            <div class="inner">
                                                <h3>Rs. {{ number_format($payments->where('status', 'pending')->sum('amount'), 2) }}</h3>
                                                <p>Pending</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-clock"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="small-box bg-danger">
                                            <div class="inner">
                                                <h3>Rs. {{ number_format($payments->where('status', 'failed')->sum('amount'), 2) }}</h3>
                                                <p>Failed</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-times-circle"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="small-box bg-primary">
                                            <div class="inner">
                                                <h3>{{ $payments->count() }}</h3>
                                                <p>Total Transactions</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- System Information --}}
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card card-outline card-secondary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>System Information</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <th style="width: 200px;">Created At</th>
                                        <td>{{ $vehicleowner->created_at ? $vehicleowner->created_at->format('d-m-Y H:i:s') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Last Updated</th>
                                        <td>{{ $vehicleowner->updated_at ? $vehicleowner->updated_at->format('d-m-Y H:i:s') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>ID</th>
                                        <td>#{{ $vehicleowner->id }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="text-right mt-3">
                    <a href="{{ route('admin.vehicleowner.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('admin.vehicleowner.edit', $vehicleowner->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Vehicle Owner
                    </a>
                    @if($vehicleowner->bank_account_number && $vehicleowner->bank_name)
                        <a href="{{ route('admin.owner-bookings.index') }}?owner_id={{ $vehicleowner->id }}" class="btn btn-success">
                            <i class="fas fa-money-bill-wave"></i> View All Payments
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</section>

{{-- Payment Notes Modal --}}
<div class="modal fade" id="paymentNotesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-sticky-note mr-2"></i>Payment Notes</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="paymentNotesContent">
                    <pre class="bg-light p-3 rounded" style="white-space: pre-wrap; max-height: 400px; overflow-y: auto;"></pre>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Receipt Modal --}}
<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-receipt mr-2"></i>Payment Receipt</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="receiptContent">
                <div class="text-center p-4">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p class="mt-2">Loading receipt...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
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

// Copy to clipboard function
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            toastr.success('Copied to clipboard!');
        }).catch(function() {
            fallbackCopy(text);
        });
    } else {
        fallbackCopy(text);
    }
}

function fallbackCopy(text) {
    var input = document.createElement('input');
    input.value = text;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
    toastr.success('Copied to clipboard!');
}

// Show payment notes in modal
function showPaymentNotes(notes) {
    try {
        var notesData = JSON.parse(notes);
        var formattedNotes = JSON.stringify(notesData, null, 2);
        $('#paymentNotesContent pre').text(formattedNotes);
    } catch (e) {
        $('#paymentNotesContent pre').text(notes);
    }
    $('#paymentNotesModal').modal('show');
}

// Show payment receipt
function showPaymentReceipt(paymentId) {
    $('#receiptModal').modal('show');
    $('#receiptContent').html(`
        <div class="text-center p-4">
            <i class="fas fa-spinner fa-spin fa-3x"></i>
            <p class="mt-2">Loading receipt...</p>
        </div>
    `);

    // You can load receipt data via AJAX if needed
    $.ajax({
       url = "{{ route('admin.payment_receipt.download', ':id') }}"
    .replace(':id', paymentId),
        type: 'GET',
        success: function(response) {
            $('#receiptContent').html(response);
        },
        error: function() {
            $('#receiptContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Failed to load receipt. Please try again.
                </div>
            `);
        }
    });
}
</script>
@endsection