@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Receipt Details</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.receipt.index') }}">Invoices</a></li>
                    <li class="breadcrumb-item active">Receipt Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Main Card -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-receipt"></i> Receipt #{{ $receipt->receipt_number }}
                </h3>
                <div class="card-tools">
                    @if($receipt->receipt_path && file_exists(public_path($receipt->receipt_path)))
                        <a href="{{ route('admin.vehicle_final_receipt.download', $receipt->id) }}" 
                           class="btn btn-sm btn-success" target="_blank">
                            <i class="fas fa-download"></i> Download Receipt PDF
                        </a>
                    @endif
                    @if($receipt->pdf_path && file_exists(public_path($receipt->pdf_path)))
                        <a href="{{ asset($receipt->pdf_path) }}" class="btn btn-sm btn-info" target="_blank">
                            <i class="fas fa-file-invoice"></i> View Invoice
                        </a>
                    @endif
                </div>
            </div>
            
            <div class="card-body">
                <!-- Two Column Layout -->
                <div class="row">
                    <!-- Left Column - Booking Information -->
                    <div class="col-md-6">
                        <div class="card card-info card-outline">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-info-circle"></i> Booking Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-sm">
                                    <tr>
                                        <th width="35%">File Number:</th>
                                        <td>
                                            <span class="badge badge-info" style="font-size: 14px;">
                                                {{ $receipt->file_no ?? 'N/A' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Receipt Number:</th>
                                        <td><strong>{{ $receipt->receipt_number }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Invoice Type:</th>
                                        <td>
                                            @if($receipt->invoice_type == 'credit')
                                                <span class="badge badge-success">Credit Invoice</span>
                                            @elseif($receipt->invoice_type == 'vat')
                                                <span class="badge badge-primary">VAT Invoice</span>
                                            @else
                                                <span class="badge badge-secondary">Non-VAT</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Generated Date:</th>
                                        <td>{{ $receipt->created_at->format('F d, Y h:i A') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Invoice Due Date:</th>
                                        <td>
                                            @if($receipt->invoice_due_date)
                                                {{ date('F d, Y', strtotime($receipt->invoice_due_date)) }}

                                                @if(is_null($receipt->amount) && now()->gt($receipt->invoice_due_date))
                                                    <span class="badge badge-danger ml-2">Overdue</span>
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                    @if($receipt->remarks)
                                    <tr>
                                        <th>Remarks:</th>
                                        <td>{{ $receipt->remarks }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>

                        <!-- Customer Information -->
                        <div class="card card-success card-outline mt-3">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-user"></i> Customer Information
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($receipt->customer)
                                    <table class="table table-bordered table-sm">
                                        <tr>
                                            <th width="35%">Customer Name:</th>
                                            <td><strong>{{ $receipt->customer->name ?? $receipt->customer->customer_name }}</strong></td>
                                        </tr>
                                        @if($receipt->customer->phone)
                                        <tr>
                                            <th>Phone:</th>
                                            <td>{{ $receipt->customer->phone }}</td>
                                        </tr>
                                        @endif
                                        @if($receipt->customer->email)
                                        <tr>
                                            <th>Email:</th>
                                            <td>{{ $receipt->customer->email }}</td>
                                        </tr>
                                        @endif
                                        @if($receipt->customer->address)
                                        <tr>
                                            <th>Address:</th>
                                            <td>{{ $receipt->customer->address }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                @else
                                    <p class="text-muted">Customer information not available</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Payment Information -->
                    <div class="col-md-6">
                        <div class="card card-warning card-outline">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-money-bill-wave"></i> Payment Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-sm">
                                    <tr>
                                        <th width="35%">Payment Status:</th>
                                        <td>
                                            @if($receipt->paid)
                                                <span class="badge badge-success" style="font-size: 14px;">
                                                    <i class="fas fa-check-circle"></i> Paid
                                                </span>
                                            @else
                                                <span class="badge badge-warning" style="font-size: 14px;">
                                                    <i class="fas fa-clock"></i> Pending
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Payment Method:</th>
                                        <td>
                                            @if($receipt->payment_method == 'cash')
                                                <span class="badge badge-success">Cash</span>
                                            @elseif($receipt->payment_method == 'cheque')
                                                <span class="badge badge-info">Cheque</span>
                                            @elseif($receipt->payment_method == 'bank')
                                                <span class="badge badge-primary">Bank Transfer</span>
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($receipt->payment_method == 'cheque')
                                    <tr>
                                        <th>Cheque Number:</th>
                                        <td>{{ $receipt->check_no ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Cheque Date:</th>
                                        <td>{{ $receipt->check_date ? date('F d, Y', strtotime($receipt->check_date)) : 'N/A' }}</td>
                                    </tr>
                                    @endif
                                    @if($receipt->payment_method == 'bank')
                                    <tr>
                                        <th>Bank Name:</th>
                                        <td>{{ $receipt->bank_name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Bank Account:</th>
                                        <td>{{ $receipt->bank_account ?? 'N/A' }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th>Paid Amount:</th>
                                        <td>
                                            <h5 class="text-success mb-0">
                                                रू {{ number_format($receipt->amount ?? $receipt->total_amount, 2) }}
                                            </h5>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Financial Summary -->
                        <div class="card card-danger card-outline mt-3">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-chart-line"></i> Financial Summary
                                </h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-sm">
                                    <tr class="bg-light">
                                        <th width="35%">Sub Total:</th>
                                        <td class="text-right">रू {{ number_format($receipt->sub_total, 2) }}</td>
                                    </tr>
                                    @if($receipt->discount > 0)
                                    <tr>
                                        <th>Discount:</th>
                                        <td class="text-right text-danger">- रू {{ number_format($receipt->discount, 2) }}</td>
                                    </tr>
                                    @endif
                                    @if($receipt->tax > 0)
                                    <tr>
                                        <th>Tax (13%):</th>
                                        <td class="text-right">+ रू {{ number_format($receipt->tax, 2) }}</td>
                                    </tr>
                                    @endif
                                    <tr class="bg-info text-white">
                                        <th><strong>Total Amount:</strong></th>
                                        <td class="text-right">
                                            <strong>रू {{ number_format($receipt->total_amount, 2) }}</strong>
                                        </td>
                                    </tr>
                                    @if($receipt->amount && $receipt->amount < $receipt->total_amount)
                                    <tr class="bg-warning">
                                        <th>Due Amount:</th>
                                        <td class="text-right text-danger">
                                            <strong>रू {{ number_format($receipt->total_amount - $receipt->amount, 2) }}</strong>
                                        </td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bookings Details Section -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-calendar-alt"></i> Bookings Details (File #{{ $receipt->file_no }})
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($bookings && count($bookings) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>S.N.</th>
                                                    <th>Vehicle</th>
                                                    <th>Start Date</th>
                                                    <th>End Date</th>
                                                    <th>Trip Route</th>
                                                    <th>Rate/Day</th>
                                                    <th>Sub Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $grandTotal = 0; @endphp
                                                @foreach($bookings as $index => $booking)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <strong>{{ $booking->vehicle->vehicle_name ?? $booking->vehicle->name ?? 'N/A' }}</strong>
                                                        @if($booking->vehicle->registration_number)
                                                            <br>
                                                            <small class="text-muted">{{ $booking->vehicle->registration_number }}</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ date('F d, Y ', strtotime($booking->start_date)) }}</td>
                                                    <td>{{ date('F d, Y', strtotime($booking->end_date)) }}</td>
                        
                                                     <td>{{ ($booking->tripRoute->title) }}</td>
                                                    <td>रू {{ number_format($booking->rate_per_day, 2) }}</td>
                                                    <td class="text-right">
                                                        <strong>रू {{ number_format($booking->sub_total, 2) }}</strong>
                                                    </td>
                                                </tr>
                                                @php $grandTotal += $booking->sub_total; @endphp
                                                @endforeach
                                            </tbody>
                                            <tfoot class="bg-light">
                                                <tr>
                                                    <th colspan="6" class="text-right">Total from Bookings:</th>
                                                    <th class="text-right">रू {{ number_format($grandTotal, 2) }}</th>
                                                </tr>
                                                @if($receipt->discount > 0)
                                                <tr>
                                                    <th colspan="6" class="text-right text-danger">Discount Applied:</th>
                                                    <th class="text-right text-danger">- रू {{ number_format($receipt->discount, 2) }}</th>
                                                </tr>
                                                @endif
                                                @if($receipt->tax > 0)
                                                <tr>
                                                    <th colspan="6" class="text-right">Tax (13%):</th>
                                                    <th class="text-right">+ रू {{ number_format($receipt->tax, 2) }}</th>
                                                </tr>
                                                @endif
                                                <tr class="bg-info text-white">
                                                    <th colspan="6" class="text-right">Grand Total:</th>
                                                    <th class="text-right">रू {{ number_format($receipt->total_amount, 2) }}</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> No booking details found for this file number.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                @if($receipt->remarks)
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card card-secondary">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-comment"></i> Additional Remarks
                                </h5>
                            </div>
                            <div class="card-body">
                                {{ $receipt->remarks }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="row mt-4">
                    <div class="col-12 text-center">
                        <a href="{{ route('admin.receipt.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        @if($receipt->receipt_path && file_exists(public_path($receipt->receipt_path)))
                            <a href="{{ route('admin.vehicle_final_receipt.download', $receipt->id) }}" 
                               class="btn btn-success" target="_blank">
                                <i class="fas fa-print"></i> Print Receipt
                            </a>
                        @endif
                        {{-- @if($receipt->pdf_path && file_exists(public_path($receipt->pdf_path)))
                            <a href="{{ asset($receipt->pdf_path) }}" class="btn btn-info" target="_blank">
                                <i class="fas fa-file-pdf"></i> View Invoice
                            </a>
                        @endif --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .table-sm th, .table-sm td {
        padding: 8px;
        vertical-align: middle;
    }
    .card-outline {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    }
    .table tfoot th {
        font-size: 14px;
    }
    @media print {
        .card-tools, .breadcrumb, .action-buttons {
            display: none;
        }
        .card {
            border: none;
        }
        .btn {
            display: none;
        }
    }
</style>
@endsection