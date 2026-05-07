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
            <th>Images</th>
            <th>Incident</th>
            <th>Status</th>
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
            
            <!-- Images -->
<td>
                     @if($moment->start_image)
                        <button class="btn btn-sm btn-info image-btn"
                            data-src="{{ asset($moment->start_image) }}"
                            data-title="Start Image"
                            data-file="{{ basename($moment->start_image) }}">
                            <i class="fas fa-play"></i>
                        </button>
                    @endif

                    <!-- END -->
                    @if($moment->end_image)
                        <button class="btn btn-sm btn-warning image-btn"
                            data-src="{{ asset($moment->end_image) }}"
                            data-title="End Image"
                            data-file="{{ basename($moment->end_image) }}">
                            <i class="fas fa-stop"></i>
                        </button>
                    @endif
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
            <td class="text-center">
    @if($moment->end_datetime)
        <span class="badge bg-success">
            <i class="fas fa-check"></i> Completed
        </span>
    @else
        <span class="badge bg-danger">
            <i class="fas fa-times"></i> Not Completed
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
                        <a class="dropdown-item" href="{{ route('admin.attendance.createAllowance', ['vehicle_moment_id' => $moment->id]) }}">
                            <i class="fas fa-money-bill-wave text-success mr-2"></i> Add Bhatta/Allowance
                        </a>

                        {{-- <a class="dropdown-item" href="{{ route('admin.vehicle_receipt.generate', [$moment->id,'vat']) }}" download>
                            <i class="fas fa-file-invoice text-success mr-2"></i> VAT Receipt
                        </a>

                        <a class="dropdown-item" href="{{ route('admin.vehicle_receipt.generate', [$moment->id,'non_vat']) }}" download>
                            <i class="fas fa-file text-secondary mr-2"></i> Non VAT Receipt
                        </a> --}}

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

<!-- MODAL -->
<div class="modal fade" id="imageModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 id="modalTitle">Image</h5>
                <button class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body text-center">
                <img id="modalImg" class="img-fluid" style="max-height:500px;">
                <p id="modalFile" class="mt-2 text-muted"></p>
            </div>

        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<style>
    .btn-group .btn {
        margin: 0 2px;
        border-radius: 4px !important;
        padding: 0.25rem 0.5rem;
    }
    .btn-group .btn.disabled {
        opacity: 0.3;
        cursor: not-allowed;
        pointer-events: none;
    }
    #imageModal .modal-header {
        border-bottom: none;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    #imageModal .close {
        opacity: 0.8;
        text-shadow: none;
    }
    #imageModal .close:hover {
        opacity: 1;
    }
    #imageModal .modal-body {
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    #imageLoading {
        width: 100%;
    }
    #modalImage {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        max-width: 100%;
    }
    .badge-info {
        background-color: #17a2b8;
        color: white;
    }
    .dropdown-menu {
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .dropdown-item i {
        width: 20px;
    }
    .table td {
        vertical-align: middle;
    }
</style>
@endpush
@section('scripts')
<script>

$(document).ready(function() {

    $('#dataTable').DataTable();

    // CLICK EVENT (NO GLOBAL FUNCTION)
    $(document).on('click', '.image-btn', function() {

        let src = $(this).data('src');
        let title = $(this).data('title');
        let file = $(this).data('file');

        $('#modalImg').attr('src', src);
        $('#modalTitle').text(title);
        $('#modalFile').text(file);

        $('#imageModal').modal('show');
    });

});
</script>
@endsection