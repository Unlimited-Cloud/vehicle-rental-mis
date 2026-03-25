@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1>Vehicle Assignments</h1>
        <a href="{{ route('admin.vehicle_assignments.create') }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Assign Vehicle
        </a>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    @include('layouts.admin_theme.alert')

    <div class="card card-primary card-outline">
        <div class="card-body">
        <table id="dataTable" class="table table-bordered table-striped show-search-bar">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Vehicle</th>
                        <th>Driver</th>
                        <th>Helper</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Shift</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignments as $assignment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $assignment->vehicle->vehicle_name ?? 'N/A' }}</td>
                        <td>{{ $assignment->driver->name ?? 'N/A' }}</td>
                        <td>{{ $assignment->helper->name ?? 'N/A' }}</td>
                        <td>{{ $assignment->start_date }}</td>
                        <td>{{ $assignment->end_date ?? '-' }}</td>
                        <td>{{ $assignment->shift ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.vehicle_assignments.edit', $assignment->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>

                            <form action="{{ route('admin.vehicle_assignments.destroy', $assignment->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this assignment?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
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