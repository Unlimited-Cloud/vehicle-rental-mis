{{-- resources/views/layouts/admin/petrol_pumps/index.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Proforma Invoice</h1>
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
                <th>Invoice No</th>
                <th>Booking</th>
                <th>Vehicle</th>
                <th>Days</th>
                <th>Total</th>
                <th>Version</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
           @foreach($invoices as $invoice)
            <tr>
            <td>{{ $invoice->invoice_number }}</td>
            <td>
                <a href="{{ route('admin.vehicle_bookings.show', $invoice->vehicle_booking_id) }}" 
                class="text-primary" 
                title="View Booking Details">
                    #{{ $invoice->vehicle_booking_id }}
                </a>
            </td>
            <td>{{ $invoice->vehicle->vehicle_name }}</td>
            <td>{{ $invoice->days }}</td>
            <td>{{ $invoice->total_amount }}</td>
            <td>V{{ $invoice->version }}</td>

            <td>
            <a href="{{ route('admin.proforma.download',$invoice->id) }}"
            class="btn btn-sm btn-primary">
            Download PDF
            </a>
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
        "responsive": true
    });
});
</script>
@endpush