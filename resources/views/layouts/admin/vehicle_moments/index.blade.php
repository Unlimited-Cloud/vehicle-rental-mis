@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
<div class="container-fluid">
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0">Vehicle Movements</h1>
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Vehicle Movements</li>
    </ol>
</div>
</div>
</div>

<section class="content">
<div class="container-fluid">

<div class="card card-primary card-outline">
<div class="card-header">
    <h3 class="card-title">
        <i class="fas fa-history"></i> All Vehicle Movements
    </h3>
   
</div>

<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="table-responsive">
<table id="dataTable" class="table table-bordered table-striped show-search-bar">
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
    <div class="dropdown">
        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
            Actions
        </button>

        <div class="dropdown-menu">
            <a class="dropdown-item" href="{{ route('admin.vehicle_moments.show', $moment->id) }}">
                <i class="fas fa-eye text-info mr-2"></i> View Details
            </a>

            <a class="dropdown-item" href="{{ route('admin.vehicle_moments.edit', $moment->id) }}">
                <i class="fas fa-edit text-primary mr-2"></i> Edit
            </a>

            <a class="dropdown-item" href="{{ route('admin.vehicle_receipt.generate', [$moment->id,'vat']) }}" download>
                <i class="fas fa-file-invoice text-success mr-2"></i> VAT Receipt
            </a>

            <a class="dropdown-item" href="{{ route('admin.vehicle_receipt.generate', [$moment->id,'non_vat']) }}" download>
                <i class="fas fa-file text-secondary mr-2"></i> Non VAT Receipt
            </a>

            <div class="dropdown-divider"></div>

            <button type="button" 
                class="dropdown-item text-danger delete-btn" 
                data-id="{{ $moment->id }}">
                <i class="fas fa-trash mr-2"></i> Delete
            </button>
        </div>
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