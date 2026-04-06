@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Coupons</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('admin.coupons.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Add Coupon
    </a>
</div>

<div class="table-responsive">
<table id="dataTable" class="table table-bordered table-striped show-search-bar">
<thead>
<tr>
    <th>SN</th>
    <th>Coupon Number</th>
    <th>Petrol Pump ID</th>
    <th>Amount</th>
    <th>Booking ID</th>
    <th>Used At</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>
@foreach($items as $item)
<tr>
    <td>{{ $loop->iteration }}</td>

    <td>{{ $item->coupon_number }}</td>

    <td>{{ $item->petrolPump->name ?? 'N/A' }}</td>

    <td>{{ $item->amount }}</td>

    <td>{{ $item->booking_id ?? 'N/A' }}</td>

    <td>
        @if($item->used)
        <p class="text-sm font-semibold text-gray-600">
            <span class="text-violet-600">
                {{ $item->used_at ? $item->used_at->setTimezone('Asia/Kathmandu')->format('l, F j, Y g:i A') : 'N/A' }}
            </span>
        </p>
        @else
            <span class="badge badge-secondary">No</span>
        @endif
    </td>

    <td>
        <a href="{{ route('admin.coupons.pdf', $item->id) }}" 
            class="btn btn-success btn-sm" title="Download Coupon">
                <i class="fa fa-file-pdf"></i>
        </a>
        <a href="{{ route('admin.coupons.edit', $item->id) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i>
        </a>
    
        <form action="{{ route('admin.coupons.destroy', $item->id) }}"
              method="POST"
              style="display:inline-block;"
              onsubmit="return confirm('Are you sure you want to delete this data?');">
            @csrf
            @method('DELETE')

            <button type="submit" class="btn btn-sm bg-red">
                <i class="fa fa-trash"></i>
            </button>
        </form>
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