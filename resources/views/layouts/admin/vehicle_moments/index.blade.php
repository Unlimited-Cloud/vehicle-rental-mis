@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
<div class="container-fluid">
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0">Vehicle Moments</h1>
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Vehicle Moments</li>
    </ol>
</div>
</div>
</div>

<section class="content">
<div class="container-fluid">

<div class="card card-primary card-outline">
<div class="card-header">
    <h3 class="card-title">
        <i class="fas fa-history"></i> All Vehicle Moments
    </h3>
   
</div>

<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="table-responsive">
<table id="vehicleMomentsTable" class="table table-bordered table-striped table-hover">
    <thead class="bg-primary text-white">
        <tr>
            <th width="50">SN</th>
            <th>Vehicle</th>
            <th>Driver</th>
            <th>Helper</th>
            <th>Start KM</th>
            <th>End KM</th>
            <th>Distance</th>
            <th>Incident</th>
            <th width="120">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($moments as $moment)
        <tr>
            <td>{{ $loop->iteration }}</td>
            
            
            <!-- Vehicle -->
            <td>
                <strong>{{ $moment->vehicle_name }}</strong>
            </td>
            
            <!-- Driver -->
            <td>
                <i class="fas fa-user-circle text-info"></i> {{ $moment->driver_name }}
            </td>
            
            <!-- Helper -->
            <td>
                @if($moment->helper_name != 'N/A')
                    <i class="fas fa-user-friends text-success"></i> {{ $moment->helper_name }}
                @else
                    <span class="text-muted">N/A</span>
                @endif
            </td>
            
            <!-- Start KM -->
            <td>
                <span class="badge badge-info">{{ number_format($moment->start_km) }} km</span>
            </td>
            
            <!-- End KM -->
            <td>
                <span class="badge badge-warning">{{ number_format($moment->end_km) }} km</span>
            </td>
            
            <!-- Distance -->
            <td>
                <span class="badge badge-success">{{ number_format($moment->end_km - $moment->start_km) }} km</span>
            </td>
            
            <!-- Incident -->
            <td class="text-center">
                @if($moment->has_incident)
                    <span class="badge bg-danger" data-toggle="tooltip" title="{{ $moment->incident_report }}">
                        <i class="fas fa-exclamation-triangle"></i> Yes
                    </span>
                @else
                    <span class="badge bg-success">
                        <i class="fas fa-check-circle"></i> No
                    </span>
                @endif
            </td>
            
            <!-- Actions -->
            <td>
                <div class="btn-group">
                    <a href="{{ route('admin.vehicle_moments.show', $moment->id) }}" class="btn btn-sm btn-info" data-toggle="tooltip" title="View Details">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('admin.vehicle_moments.edit', $moment->id) }}" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="{{ $moment->id }}" data-toggle="tooltip" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
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

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<style>
    .badge {
        font-size: 11px;
        padding: 5px 8px;
    }
    .table td {
        vertical-align: middle;
    }
    .btn-group .btn {
        margin: 0 2px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#vehicleMomentsTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "pageLength": 10,
        "order": [[0, 'desc']]
    });

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Handle delete with confirmation
    $('.delete-btn').on('click', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.vehicle_moments.index") }}/' + id;
                form.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endpush