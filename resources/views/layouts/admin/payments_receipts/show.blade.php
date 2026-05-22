{{-- resources/views/layouts/admin/payment_receipts/show.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Payment Receipt Details</h1>
            <div>
                <a href="{{ route('admin.payment_receipt.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                @if($paymentReceipt->pdf_path && file_exists(public_path($paymentReceipt->pdf_path)))
                    <a href="{{ route('admin.payment_receipt.download', $paymentReceipt->id) }}" 
                       class="btn btn-primary" target="_blank">
                        <i class="fas fa-download"></i> Download PDF
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title">Receipt Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Receipt Number:</th>
                                    <td><strong>{{ $paymentReceipt->receipt_number }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Payment Date:</th>
                                    <td>{{ $paymentReceipt->payment_date->format('Y-m-d') }}</td>
                                </tr>
                                <tr>
                                    <th>Customer Name:</th>
                                    <td>{{ $paymentReceipt->customer->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Customer PAN:</th>
                                    <td>{{ $paymentReceipt->customer->pan_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Customer Address:</th>
                                    <td>{{ $paymentReceipt->customer->address ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Payment Method:</th>
                                    <td><span class="badge badge-info">{{ ucfirst($paymentReceipt->payment_method) }}</span></td>
                                </tr>
                                @if($paymentReceipt->payment_method == 'bank')
                                <tr>
                                    <th>Bank Name:</th>
                                    <td>{{ $paymentReceipt->bank_name }}</td>
                                </tr>
                                @if($paymentReceipt->bank_account_number)
                                <tr>
                                    <th>Account Number:</th>
                                    <td>{{ $paymentReceipt->bank_account_number }}</td>
                                </tr>
                                @endif
                                @endif
                                @if($paymentReceipt->payment_method == 'cheque')
                                <tr>
                                    <th>Cheque Number:</th>
                                    <td>{{ $paymentReceipt->cheque_number }}</td>
                                </tr>
                                @if($paymentReceipt->cheque_date)
                                <tr>
                                    <th>Cheque Date:</th>
                                    <td>{{ $paymentReceipt->cheque_date->format('Y-m-d') }}</td>
                                </tr>
                                @endif
                                @endif
                                @if($paymentReceipt->transaction_id)
                                <tr>
                                    <th>Transaction ID:</th>
                                    <td>{{ $paymentReceipt->transaction_id }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>TDS Applied:</th>
                                    <td>
                                        @if($paymentReceipt->tds_applied)
                                            <span class="badge badge-warning">Yes ({{ $paymentReceipt->tds_rate }}%)</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($paymentReceipt->notes)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <strong>Notes:</strong> {{ $paymentReceipt->notes }}
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if(abs($paymentReceipt->difference_amount) > 0.01)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-warning">
                                <strong>Payment Difference Alert:</strong> {{ $paymentReceipt->difference_note ?? ($paymentReceipt->difference_amount > 0 ? 'Overpayment detected' : 'Short payment detected') }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title">Payment Summary</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box bg-light">
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Invoice Amount</span>
                                    <span class="info-box-number">रू {{ number_format($paymentReceipt->total_invoice_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-light">
                                <div class="info-box-content">
                                    <span class="info-box-text">Received Amount</span>
                                    <span class="info-box-number">रू {{ number_format($paymentReceipt->received_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        @if($paymentReceipt->tds_applied)
                        <div class="col-md-4">
                            <div class="info-box bg-warning">
                                <div class="info-box-content">
                                    <span class="info-box-text">TDS Deduction ({{ $paymentReceipt->tds_rate }}%)</span>
                                    <span class="info-box-number">- रू {{ number_format($paymentReceipt->tds_deduction, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    @if($paymentReceipt->tds_applied)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-info">
                                <div class="info-box-content">
                                    <span class="info-box-text">Net Payable Amount</span>
                                    <span class="info-box-number">रू {{ number_format($paymentReceipt->net_paid_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        @if(abs($paymentReceipt->difference_amount) > 0.01)
                        <div class="col-md-6">
                            <div class="info-box {{ $paymentReceipt->difference_amount > 0 ? 'bg-warning' : 'bg-danger' }}">
                                <div class="info-box-content">
                                    <span class="info-box-text">
                                        @if($paymentReceipt->difference_amount > 0)
                                            Overpayment Amount
                                        @else
                                            Short Payment Amount
                                        @endif
                                    </span>
                                    <span class="info-box-number">
                                        @if($paymentReceipt->difference_amount > 0)
                                            + रू {{ number_format($paymentReceipt->difference_amount, 2) }}
                                        @else
                                            रू {{ number_format($paymentReceipt->difference_amount, 2) }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Invoices Paid</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="invoicesTable">
                            <thead>
                                <tr>
                                    <th>S.N.</th>
                                    <th>Invoice Number</th>
                                    <th>Invoice Date</th>
                                    <th class="text-right">Invoice Amount (रू)</th>
                                    <th class="text-right">Paid Amount (रू)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paymentReceipt->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->invoice_number }}</td>
                                    <td>{{ $item->vehicleReceipt ? $item->vehicleReceipt->created_at->format('Y-m-d') : 'N/A' }}</td>
                                    <td class="text-right">रू {{ number_format($item->invoice_amount, 2) }}</td>
                                    <td class="text-right">रू {{ number_format($item->paid_amount, 2) }}</td>
                                    <td><span class="badge badge-success">Paid</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">Total:</th>
                                    <th class="text-right">रू {{ number_format($paymentReceipt->items->sum('invoice_amount'), 2) }}</th>
                                    <th class="text-right">रू {{ number_format($paymentReceipt->items->sum('paid_amount'), 2) }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Payment Timeline</h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="time-label">
                            <span class="bg-green">{{ $paymentReceipt->created_at->format('Y-m-d') }}</span>
                        </div>
                        <div>
                            <i class="fas fa-file-invoice bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> {{ $paymentReceipt->created_at->format('H:i') }}</span>
                                <h3 class="timeline-header">Payment Receipt Generated</h3>
                                <div class="timeline-body">
                                    Receipt Number: {{ $paymentReceipt->receipt_number }}<br>
                                    Amount Received: रू {{ number_format($paymentReceipt->received_amount, 2) }}
                                </div>
                            </div>
                        </div>
                        @if($paymentReceipt->pdf_path && file_exists(public_path($paymentReceipt->pdf_path)))
                        <div>
                            <i class="fas fa-file-pdf bg-red"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> {{ $paymentReceipt->updated_at->format('H:i') }}</span>
                                <h3 class="timeline-header">PDF Generated</h3>
                                <div class="timeline-body">
                                    <a href="{{ route('admin.payment_receipt.download', $paymentReceipt->id) }}" target="_blank">
                                        <i class="fas fa-download"></i> Download Payment Receipt PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#invoicesTable').DataTable({
        ordering: false,
        pageLength: 25,
        responsive: true
    });
});
</script>
@endsection