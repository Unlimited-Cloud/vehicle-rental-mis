{{-- resources/views/layouts/admin/payment_receipts/index.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Payment Receipts</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<div class="card card-primary card-outline mb-4">
    <div class="card-header">
        <h3 class="card-title">Payment Management</h3>
        <div class="card-tools">
            <a href="{{ route('admin.payment_receipt.create') }}" class="btn btn-success">
                <i class="fas fa-money-bill-wave"></i> Receive Payment
            </a>
        </div>
    </div>
</div>

<div class="card card-primary card-outline">
    <div class="card-body">
        @include('layouts.admin_theme.alert')
        
        <div class="table-responsive">
            <table id="dataTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>S.N.</th>
                        <th>Receipt No.</th>
                        <th>Customer</th>
                        <th>Total Invoice Amount</th>
                        <th>TDS Deducted</th>
                        <th>Net Paid Amount</th>
                        <th>Received Amount</th>
                        <th>Difference</th>
                        <th>Payment Method</th>
                        <th>Payment Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paymentReceipts as $index => $receipt)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $receipt->receipt_number }}</td>
                        <td>{{ $receipt->customer->name ?? 'N/A' }}</td>
                        <td>रू {{ number_format($receipt->total_invoice_amount, 2) }}</td>
                        <td>
                            @if($receipt->tds_applied)
                                रू {{ number_format($receipt->tds_deduction, 2) }}
                                <small class="text-muted">({{ $receipt->tds_rate }}%)</small>
                            @else
                                <span class="badge badge-secondary">N/A</span>
                            @endif
                        </td>
                        <td>रू {{ number_format($receipt->net_paid_amount, 2) }}</td>
                        <td>रू {{ number_format($receipt->received_amount, 2) }}</td>
                        <td>
                            @if($receipt->difference_amount > 0)
                                <span class="badge badge-warning">+ रू {{ number_format($receipt->difference_amount, 2) }}</span>
                                <small class="text-muted">(Overpayment)</small>
                            @elseif($receipt->difference_amount < 0)
                                <span class="badge badge-danger">रू {{ number_format($receipt->difference_amount, 2) }}</span>
                                <small class="text-muted">(Short)</small>
                            @else
                                <span class="badge badge-success">Exact</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-info">{{ ucfirst($receipt->payment_method) }}</span>
                        </td>
                        <td>{{ $receipt->payment_date->format('Y-m-d') }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                @if($receipt->pdf_path)
                                    <a href="{{ route('admin.payment_receipt.download', $receipt->id) }}" 
                                       class="btn btn-sm btn-primary" title="Download PDF" target="_blank">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="{{ route('admin.payment_receipt.show', $receipt->id) }}" 
                                       class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @else
                                    <span class="text-muted">No PDF</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                
            </table>
        </div>
    </div>
</div>

</div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        order: [[9, 'desc']], // Order by payment date (index 9)
        pageLength: 25,
        responsive: true,
        footerCallback: function(row, data, start, end, display) {
            var api = this.api();
            
            // Remove the formatting to get integer data for summation
            var intVal = function(i) {
                return typeof i === 'string' ? 
                    i.replace(/[\रू,]/g, '') * 1 : 
                    typeof i === 'number' ? i : 0;
            };
            
            // Total over current page
            totalTotalInvoice = api
                .column(3)
                .data()
                .reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
                
            totalTDS = api
                .column(4)
                .data()
                .reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
                
            totalNetPaid = api
                .column(5)
                .data()
                .reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
                
            totalReceived = api
                .column(6)
                .data()
                .reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            
            // Update footer
            $(api.column(3).footer()).html('रू ' + totalTotalInvoice.toFixed(2));
            $(api.column(4).footer()).html('रू ' + totalTDS.toFixed(2));
            $(api.column(5).footer()).html('रू ' + totalNetPaid.toFixed(2));
            $(api.column(6).footer()).html('रू ' + totalReceived.toFixed(2));
        }
    });
});
</script>
@endsection