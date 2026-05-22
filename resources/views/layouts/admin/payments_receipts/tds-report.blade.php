{{-- resources/views/layouts/admin/reports/tds-report.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>TDS Report</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total TDS Deducted (Year to Date)</span>
                    <span class="info-box-number">रू {{ number_format($totalTDSDeducted, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">TDS Payment History</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="tdsTable">
                    <thead>
                        <tr>
                            <th>S.N.</th>
                            <th>Receipt No.</th>
                            <th>Customer Name</th>
                            <th>Customer PAN</th>
                            <th>Invoice Amount</th>
                            <th>TDS Rate</th>
                            <th>TDS Amount</th>
                            <th>Net Paid</th>
                            <th>Payment Date</th>
                            <th>Payment Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tdsPayments as $index => $payment)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $payment->receipt_number }}</td>
                            <td>{{ $payment->customer->name ?? 'N/A' }}</td>
                            <td>{{ $payment->customer->pan_number ?? 'N/A' }}</td>
                            <td class="text-right">रू {{ number_format($payment->total_invoice_amount, 2) }}</td>
                            <td class="text-center">{{ $payment->tds_rate }}%</td>
                            <td class="text-right text-danger">रू {{ number_format($payment->tds_deduction, 2) }}</td>
                            <td class="text-right">रू {{ number_format($payment->net_paid_amount, 2) }}</td>
                            <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                            <td><span class="badge badge-info">{{ ucfirst($payment->payment_method) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-right">Total TDS Deducted:</th>
                            <th class="text-right">रू {{ number_format($totalTDSDeducted, 2) }}</th>
                            <th colspan="3"></th>
                         </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
</section>

<script>
$(document).ready(function() {
    $('#tdsTable').DataTable({
        order: [[8, 'desc']],
        pageLength: 25
    });
});
</script>
@endsection