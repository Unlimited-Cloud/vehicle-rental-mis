@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Vehicle Receipts / Invoices</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="table-responsive">
    <table id="dataTable" class="table table-bordered table-striped show-search-bar">
        <thead>
            <tr>
                <th>S.N.</th>
                <th>Receipt No.</th>
                <th>Booking ID</th>
                <th>Vehicle</th>
                <th>Customer</th>
                <th>Days</th>
                <th>Total Amount</th>
                <th>Invoice Type</th>
                <th>Generated Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
           @foreach($receipts as $index => $receipt)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $receipt->receipt_number }}</td>
                <td>#{{ $receipt->vehicle_booking_id }}</td>
                <td>
                    @if($receipt->vehicle)
                        {{ $receipt->vehicle->vehicle_name ?? $receipt->vehicle->name ?? 'N/A' }}
                    @else
                        Vehicle ID: {{ $receipt->vehicle_id }}
                    @endif
                </td>
                <td>
                    @if($receipt->customer)
                        {{ $receipt->customer->name ?? $receipt->customer->customer_name ?? 'N/A' }}
                    @else
                        Customer ID: {{ $receipt->customer_id }}
                    @endif
                </td>
                <td>{{ $receipt->days }}</td>
                <td>रू {{ number_format($receipt->total_amount, 2) }}</td>
                <td>
                    @if($receipt->invoice_type == 'vat')
                        <span class="badge badge-success">VAT</span>
                    @else
                        <span class="badge badge-secondary">Non VAT</span>
                    @endif
                </td>
                <td>{{ $receipt->created_at->format('Y-m-d H:i') }}</td>
                <td>
                    @if($receipt->pdf_path && file_exists(public_path($receipt->pdf_path)))
                        <a href="{{ route('admin.vehicle_receipt.download', $receipt->id) }}" 
                           class="btn btn-sm btn-primary" 
                           target="_blank">
                            <i class="fas fa-download"></i> Download PDF
                        </a>
                        
                        <a href="{{ asset($receipt->pdf_path) }}" 
                           class="btn btn-sm btn-info" 
                           target="_blank">
                            <i class="fas fa-eye"></i> View
                        </a>
                    @else
                        <span class="badge badge-danger">File Missing</span>
                        
                        <!-- Option to regenerate if needed -->
                        <a href="{{ route('admin.vehicle_receipt.generate', [$receipt->vehicle_moment_id, $receipt->invoice_type]) }}" 
                           class="btn btn-sm btn-warning">
                            <i class="fas fa-sync"></i> Regenerate
                        </a>
                    @endif
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "order": [[0, 'desc']] // Sort by S.N. descending (newest first)
    });
});
</script>
@endpush