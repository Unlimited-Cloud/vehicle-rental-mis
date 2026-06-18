@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Passengers</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('admin.passengers.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Add Passenger
    </a>
</div>

<div class="table-responsive">
<table id="dataTable" class="table table-bordered table-striped show-search-bar">
<thead>
<tr>
    <th>SN</th>
    <th>Contact Person</th>
    <th>Contact Email</th>
    <th>Contact Number</th>
    <th>Customer ID</th>
    <th>Booking ID</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
@foreach($passengers as $p)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $p->contact_person }}</td>
    <td>{{ $p->contact_email }}</td>
    <td>{{ $p->contact_number }}</td>
    <td>{{ $p->customer_id ?? 'N/A' }}</td>
    <td>{{ $p->booking_id }}</td>
    <td>
        <a href="{{ route('admin.passengers.edit', $p->id) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i>
        </a>

        <form action="{{ route('admin.passengers.destroy', $p->id) }}"
              method="POST"
              style="display:inline-block;"
              onsubmit="return confirm('Delete this passenger?');">
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
    $('#dataTable').DataTable();
});
</script>
@endpush