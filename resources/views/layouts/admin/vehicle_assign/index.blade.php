@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Vehicle Assignments</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('admin.vehicle_assign.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Add Assignment
    </a>
</div>

<div class="table-responsive">
<table id="dataTable" class="table table-bordered table-striped show-search-bar">
<thead>
<tr>
    <th>SN</th>
    <th>Date</th>
    <th>Vehicle</th>
    <th>Driver</th>
    <th>Helper</th>
    <th>Remarks</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
@foreach($assigns as $a)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $a->date }}</td>
    <td>{{ $a->vehicle->name ?? 'N/A' }}</td>
    <td>{{ $a->driver->user->name ?? 'N/A' }}</td>
    <td>{{ $a->helper->user->name ?? 'N/A' }}</td>
    <td>{{ $a->remarks ?? 'N/A' }}</td>
    <td>
        <a href="{{ route('admin.vehicle_assign.edit', $a->id) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i>
        </a>

        <a href="{{ route('admin.vehicle_assign.show', $a->id) }}" class="btn btn-info btn-sm">
            <i class="fas fa-eye"></i>
        </a>

        <form action="{{ route('admin.vehicle_assign.destroy', $a->id) }}"
              method="POST"
              style="display:inline-block;"
              onsubmit="return confirm('Are you sure you want to delete this assignment?');">
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