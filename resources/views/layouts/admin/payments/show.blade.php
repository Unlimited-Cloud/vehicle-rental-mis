{{-- resources/views/admin/payments/show.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<style>
    .payment-details-card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .detail-row {
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .detail-label {
        font-weight: 600;
        color: #555;
        font-size: 0.9rem;
    }
    .detail-value {
        color: #333;
        font-size: 1rem;
    }
    .status-badge {
        font-size: 1rem;
        padding: 6px 12px;
    }
    .response-json {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        font-family: monospace;
        font-size: 12px;
        max-height: 400px;
        overflow-y: auto;
    }
    .info-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px;
        border-radius: 10px 10px 0 0;
    }
    .payment-method-icon {
        font-size: 3rem;
        margin-right: 15px;
    }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Payment Details</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Payments</a></li>
                    <li class="breadcrumb-item active">Payment Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">

@include('layouts.admin_theme.alert')

<div class="row">
    <div class="col-md-8">
        <!-- Main Payment Details -->
        <div class="card payment-details-card">
            <div class="info-header">
                <div class="d-flex align-items-center">
                    @if($method == 'esewa')
                        <div class="payment-method-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">eSewa Payment Details</h3>
                            <small>Transaction ID: {{ $payment->transaction_uuid }}</small>
                        </div>
                    @else
                        <div class="payment-method-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">Khalti Payment Details</h3>
                            <small>PIDX: {{ $payment->pidx ?? $payment->txn_id ?? 'N/A' }}</small>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($method == 'esewa')
                    <!-- eSewa Payment Details -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">Transaction UUID</div>
                                <div class="detail-value">
                                    <strong>{{ $payment->transaction_uuid }}</strong>
                                    <button class="btn btn-sm btn-link" onclick="copyToClipboard('{{ $payment->transaction_uuid }}')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Amount</div>
                                <div class="detail-value">
                                    <h4 class="text-success mb-0">रु {{ number_format($payment->amount, 2) }}</h4>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Payment Status</div>
                                <div class="detail-value">
                                    @if($payment->status == 'Completed')
                                        <span class="badge badge-success status-badge">
                                            <i class="fas fa-check-circle"></i> Completed
                                        </span>
                                    @elseif($payment->status == 'pending')
                                        <span class="badge badge-warning status-badge">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    @elseif($payment->status == 'failed')
                                        <span class="badge badge-danger status-badge">
                                            <i class="fas fa-times-circle"></i> Failed
                                        </span>
                                    @else
                                        <span class="badge badge-secondary status-badge">{{ $payment->status }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">Payment Date</div>
                                <div class="detail-value">
                                    <i class="fas fa-calendar-alt"></i> 
                                    {{ $payment->created_at->format('F d, Y h:i A') }}
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Booking ID</div>
                                <div class="detail-value">
                                    <a href="{{ route('admin.vehicle_bookings.show', $payment->booking_id) }}" class="btn btn-link p-0">
                                        #{{ $payment->booking_id }}
                                    </a>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Payment Method</div>
                                <div class="detail-value">
                                    <span class="badge badge-primary">
                                        <i class="fas fa-money-bill"></i> eSewa
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($payment->booking)
                    <div class="mt-4">
                        <h5><i class="fas fa-car"></i> Booking Information</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted">Customer Name</small>
                                <div>{{ $payment->booking->customer->name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Customer Phone</small>
                                <div>{{ $payment->booking->customer->phone ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Customer Email</small>
                                <div>{{ $payment->booking->customer->email ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($payment->esewa_response)
                    <div class="mt-4">
                        <h5><i class="fas fa-code"></i> eSewa Response</h5>
                        <hr>
                        <div class="response-json">
                            <pre class="mb-0">{{ json_encode(json_decode($payment->esewa_response), JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif
                    
                @else
                    <!-- Khalti Payment Details -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">Merchant Transaction ID</div>
                                <div class="detail-value">
                                    <strong>{{ $payment->merchant_transaction_id ?? 'N/A' }}</strong>
                                    @if($payment->merchant_transaction_id)
                                    <button class="btn btn-sm btn-link" onclick="copyToClipboard('{{ $payment->merchant_transaction_id }}')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">PIDX / Transaction ID</div>
                                <div class="detail-value">
                                    {{ $payment->pidx ?? $payment->txn_id ?? 'N/A' }}
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Amount Details</div>
                                <div class="detail-value">
                                    <strong>Amount: </strong>रु {{ number_format($payment->amount, 2) }}<br>
                                    @if($payment->fees)
                                    <strong>Fees: </strong>रु {{ number_format($payment->fees, 2) }}<br>
                                    @endif
                                    <strong>Total: </strong>
                                    <h5 class="text-success d-inline">रु {{ number_format($payment->total_amount ?? $payment->amount, 2) }}</h5>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="detail-row">
                                <div class="detail-label">Payment Status</div>
                                <div class="detail-value">
                                    @if($payment->status == 'completed')
                                        <span class="badge badge-success status-badge">
                                            <i class="fas fa-check-circle"></i> Completed
                                        </span>
                                    @elseif($payment->status == 'pending')
                                        <span class="badge badge-warning status-badge">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    @elseif($payment->status == 'failed')
                                        <span class="badge badge-danger status-badge">
                                            <i class="fas fa-times-circle"></i> Failed
                                        </span>
                                    @else
                                        <span class="badge badge-secondary status-badge">{{ $payment->status }}</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Payment Date</div>
                                <div class="detail-value">
                                    <i class="fas fa-calendar-alt"></i> 
                                    {{ $payment->created_at->format('F d, Y h:i A') }}
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Booking ID</div>
                                <div class="detail-value">
                                    <a href="{{ route('admin.vehicle_bookings.show', $payment->booking_id) }}" class="btn btn-link p-0">
                                        #{{ $payment->booking_id }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($payment->user_name || $payment->user_email || $payment->user_mobile)

                    <div class="mt-4">
                        <h5><i class="fas fa-user"></i> Customer Information</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted">Name</small>
                                <div>{{ $payment->user_name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Mobile</small>
                                <div>{{ $payment->user_mobile ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Email</small>
                                <div>{{ $payment->user_email ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($payment->booking)
                    <div class="mt-4">
                        <h5><i class="fas fa-car"></i> Booking Information</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted">Customer Name</small>
                                <div>{{ $payment->booking->customer->name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Customer Phone</small>
                                <div>{{ $payment->booking->customer->phone ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Customer Email</small>
                                <div>{{ $payment->booking->customer->email ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($payment->khalti_init_response)
                    <div class="mt-4">
                        <h5><i class="fas fa-code"></i> Khalti Response</h5>
                        <hr>
                        <div class="response-json">
                            <pre class="mb-0">{{ json_encode(json_decode($payment->khalti_init_response), JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Actions Card -->
        <div class="card payment-details-card">
            <div class="card-header bg-secondary">
                <h5 class="mb-0"><i class="fas fa-cogs"></i> Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-info btn-block">
                        <i class="fas fa-arrow-left"></i> Back to Payments
                    </a>
                    
                    <a href="{{ route('admin.vehicle_bookings.show', $payment->booking_id) }}" class="btn btn-primary btn-block mb-3">
                        <i class="fas fa-calendar-check"></i> View Booking Details
                    </a>
                    
                    {{-- <button class="btn btn-success btn-block" onclick="window.print();">
                        <i class="fas fa-print"></i> Print Receipt
                    </button> --}}
                    
                    <form action="{{ route('admin.payments.destroy', ['method' => $method, 'id' => $payment->id]) }}"
                          method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this payment record?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Payment Record
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Payment Summary Card -->
        <div class="card payment-details-card">
            <div class="card-header bg-info">
                <h5 class="mb-0"><i class="fas fa-chart-simple"></i> Payment Summary</h5>
            </div>
            <div class="card-body">
                <div class="detail-row">
                    <div class="detail-label">Transaction Status</div>
                    <div class="detail-value">
                        @if($method == 'esewa')
                            @if($payment->status == 'Completed')
                                <i class="fas fa-check-circle text-success"></i> Successfully Completed
                            @elseif($payment->status == 'pending')
                                <i class="fas fa-clock text-warning"></i> Awaiting Confirmation
                            @else
                                <i class="fas fa-times-circle text-danger"></i> Transaction Failed
                            @endif
                        @else
                            @if($payment->status == 'completed')
                                <i class="fas fa-check-circle text-success"></i> Successfully Completed
                            @elseif($payment->status == 'pending')
                                <i class="fas fa-clock text-warning"></i> Awaiting Confirmation
                            @else
                                <i class="fas fa-times-circle text-danger"></i> Transaction Failed
                            @endif
                        @endif
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Payment Processed</div>
                    <div class="detail-value">
                        {{ $payment->created_at->diffForHumans() }}
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Receipt Number</div>
                    <div class="detail-value">
                        <small class="text-muted">#{{ str_pad($payment->id, 8, '0', STR_PAD_LEFT) }}</small>
                    </div>
                </div>
            </div>
        </div>
        
        
    </div>
</div>

</div>
</section>



@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        toastr.success('Copied to clipboard!');
    }, function() {
        toastr.error('Failed to copy');
    });
}

$(document).ready(function() {
    // Format JSON responses for better readability
    $('.response-json pre').each(function() {
        try {
            let json = $(this).text();
            let obj = JSON.parse(json);
            $(this).text(JSON.stringify(obj, null, 2));
        } catch(e) {
            // Not valid JSON, leave as is
        }
    });
});
</script>
@endpush

<style media="print">
    .btn, .action-buttons, .sidebar, .main-header, .breadcrumb {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd !important;
    }
    body {
        background: white !important;
    }
</style>