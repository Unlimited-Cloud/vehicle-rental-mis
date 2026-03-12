@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
<div class="container-fluid">
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0">Fuel Purchases</h1>

    <a href="{{ route('admin.fuel_purchased.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Fuel Purchase
    </a>
</div>
</div>
</div>

<section class="content">
<div class="container-fluid">

<div class="card card-primary card-outline">

<div class="card-header">
<h3 class="card-title">
<i class="fas fa-gas-pump"></i> Fuel Purchase Records
</h3>
</div>

<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="table-responsive">

<table id="dataTable" class="table table-bordered table-striped show-search-bar">

<thead class="bg-primary text-white">
<tr>
<th>SN</th>
<th>Date</th>
<th>Vehicle</th>
<th>Driver</th>
<th>Pump</th>
<th>Liters</th>
<th>Rate</th>
<th>Amount</th>
<th>Actions</th>
</tr>
</thead>

<tbody>

@foreach($fuels as $fuel)

<tr>

<td>{{ $loop->iteration }}</td>

<td>{{ \Carbon\Carbon::parse($fuel->date_time)->format('d M Y H:i') }}</td>

<td>{{ $fuel->vehicle->vehicle_name ?? '-' }}</td>

<td>{{ optional(optional($fuel->driver)->user)->name ?? '-' }}</td>

<td>{{ $fuel->petrolPump->name ?? '-' }}</td>

<td>
<span class="badge badge-info">
{{ $fuel->liters }} L
</span>
</td>

<td>{{ $fuel->rate }}</td>

<td>
<span class="badge badge-success">
{{ $fuel->amount }}
</span>
</td>

<td>

<div class="btn-group">

<a href="{{ route('admin.fuel_purchased.show',$fuel->id) }}"
class="btn btn-sm btn-info">
<i class="fas fa-eye"></i>
</a>

<a href="{{ route('admin.fuel_purchased.edit',$fuel->id) }}"
class="btn btn-sm btn-primary">
<i class="fas fa-edit"></i>
</a>

<form action="{{ route('admin.fuel_purchased.destroy',$fuel->id) }}"
method="POST"
style="display:inline">

@csrf
@method('DELETE')

<button class="btn btn-sm btn-danger"
onclick="return confirm('Delete this record?')">
<i class="fas fa-trash"></i>
</button>

</form>

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