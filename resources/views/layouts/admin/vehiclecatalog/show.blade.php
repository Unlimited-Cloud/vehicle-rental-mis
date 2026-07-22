@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>
            {{ $vehiclecatalog->brand }}
        </h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

@include('layouts.admin_theme.alert')

<div class="card card-primary card-outline card-tabs">
<div class="card-header p-0 pt-1 border-bottom-0">
<ul class="nav nav-tabs" id="vehicleTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link {{ session('active_tab') == 'details' || !session('active_tab') ? 'active' : '' }}" 
           data-toggle="pill" href="#details" role="tab" aria-selected="true">
            <i class="fas fa-info-circle"></i> Vehicle Details
        </a>
    </li>
    
</ul>
</div>

<div class="card-body">
<div class="tab-content">
    <!-- ================= VEHICLE DETAILS TAB ================= -->
    <div class="tab-pane fade {{ session('active_tab') == 'details' || !session('active_tab') ? 'show active' : '' }}" 
         id="details" role="tabpanel">
        
        <!-- Edit Button Row -->
        <div class="row mb-3">
            <div class="col-12 text-right">
                <a href="{{ route('admin.vehiclecatalog.edit', $vehiclecatalog->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit VehicleCatalog
                </a>
            </div>
        </div>

        <!-- Main Vehicle Information Grid -->
        <div class="row">
            <!-- Vehicle Image Column -->
            <div class="col-md-4">
                <div class="card card-primary card-outline h-100">
                    <div class="card-header">
                        <h3 class="card-title">Vehicle Image</h3>
                    </div>
                    <div class="card-body text-center">
                        @if($vehiclecatalog->image)
                            <img src="{{ asset($vehiclecatalog->image) }}" alt="{{ $vehiclecatalog->brand }}" 
                                 class="img-fluid img-thumbnail" style="max-height: 250px; width: auto;">
                        @else
                            <div class="bg-light p-5 text-center rounded">
                                <i class="fas fa-car fa-5x text-muted"></i>
                                <p class="mt-3 text-muted">No Image Available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Basic Information Column -->
            <div class="col-md-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Basic Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-info"><i class="fas fa-tag"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Brand</span>
                                        <span class="info-box-number">{{ $vehiclecatalog->brand }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-info"><i class="fas fa-cog"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Seater</span>
                                        <span class="info-box-number">{{ $vehiclecatalog->seater }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-dark"><i class="fas fa-palette"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Color</span>
                                        <span class="info-box-number">
                                            {{ $vehiclecatalog->car_color ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-gas-pump"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Fuel Type</span>
                                        <span class="info-box-number">{{ $vehiclecatalog->fuel_type }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-secondary"><i class="fas fa-cogs"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Transmission</span>
                                        <span class="info-box-number">{{ $vehiclecatalog->transmission }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-{{ $vehiclecatalog->status ? 'success' : 'danger' }}">
                                        <i class="fas fa-power-off"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Status</span>
                                        <span class="info-box-number">
                                            @if($vehiclecatalog->status)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registration Details Card -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-registered"></i> Registration Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <strong>Registration Number:</strong>
                                <p class="text-muted">{{ $vehiclecatalog->registration_number ?? 'N/A' }}</p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Registered At:</strong>
                                <p class="text-muted">{{ $vehiclecatalog->registered_at ?? 'N/A' }}</p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Number Plate Color:</strong>
                                <p>
                                    @if($vehiclecatalog->number_plate_color)
                                        <span class="badge" style="background-color: {{ strtolower($vehiclecatalog->number_plate_color) }}; color: white; padding: 5px 10px;">
                                            {{ $vehiclecatalog->number_plate_color }}
                                        </span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Registration Expiry:</strong>
                                <p class="text-muted">{{ $vehiclecatalog->registration_expiry ? date('d M, Y', strtotime($vehiclecatalog->registration_expiry)) : 'N/A' }}</p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Bill Book Number:</strong>
                                <p class="text-muted">{{ $vehiclecatalog->bill_book_number ?? 'N/A' }}</p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Bill Book Image:</strong>
                                <p>
                                    @if($vehiclecatalog->bill_book_image)
                                        <a href="{{ asset($vehiclecatalog->bill_book_image) }}" target="_blank" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View Document
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Insurance Details Card -->
            <div class="col-md-6">
                <div class="card card-outline card-success">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title"><i class="fas fa-shield-alt"></i>Vehicle Insurance Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <strong>Policy Number:</strong>
                                <p class="text-muted">{{ $vehiclecatalog->insurance_policy_no ?? 'N/A' }}</p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Insurance Company:</strong>
                                <p class="text-muted">{{ $vehiclecatalog->insurance_company ?? 'N/A' }}</p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Insurance Type:</strong>
                                <p class="text-muted">{{ $vehiclecatalog->insurance_type ?? 'N/A' }}</p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Valid Till:</strong>
                                <p class="text-muted">{{ $vehiclecatalog->insurance_till ? date('d M, Y', strtotime($vehiclecatalog->insurance_till)) : 'N/A' }}</p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Cost Per Annum:</strong>
                                <p class="text-muted">{{ $vehiclecatalog->insurance_cost_per_annum ? 'Rs '.number_format($vehiclecatalog->insurance_cost_per_annum, 2) : 'N/A' }}</p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Policy Document:</strong>
                                <p>
                                    @if($vehiclecatalog->insurance_policy_document)
                                        <a href="{{ asset($vehiclecatalog->insurance_policy_document) }}" target="_blank" class="btn btn-sm btn-success">
                                            <i class="fas fa-file-pdf"></i> View Policy
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Passenger Insurance Details -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">
                            <i class="fas fa-user-shield"></i> Passenger Insurance Details
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <strong>Passenger Insured:</strong>
                                <p class="text-muted">
                                    @if($vehiclecatalog->passenger_insured)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-danger">No</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Insured Amount:</strong>
                                <p class="text-muted">
                                    {{ $vehiclecatalog->passenger_insured_amount
                                        ? 'Rs '.number_format($vehiclecatalog->passenger_insured_amount, 2)
                                        : 'N/A' }}
                                </p>
                            </div>
                            <div class="col-sm-12 mb-3">
                                <strong>Insurance Company:</strong>
                                <p class="text-muted">
                                    {{ $vehiclecatalog->passenger_insurance_company ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= SAFETY FEATURES SECTION ================= -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card card-outline card-warning">
                    <div class="card-header bg-warning">
                        <h3 class="card-title text-dark">
                            <i class="fas fa-shield-virus"></i> Safety Features
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            
                            <!-- Dash Cam -->
                            <div class="col-md-4 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-info">
                                        <i class="fas fa-video"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Dash Cam</span>
                                        <span class="info-box-number">
                                            @if($vehiclecatalog->dash_cam)
                                                <span class="badge badge-success">Available</span>
                                                @if($vehiclecatalog->dash_cam_image)
                                                    <br>
                                                    <small>
                                                        <a href="{{ asset('uploads/vehiclecatalog/security-features/' . $vehiclecatalog->dash_cam_image) }}" 
                                                           target="_blank" class="text-info">
                                                            <i class="fas fa-image"></i> View Image
                                                        </a>
                                                    </small>
                                                @endif
                                            @else
                                                <span class="badge badge-secondary">Not Available</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- EBS -->
                            <div class="col-md-4 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-danger">
                                        <i class="fas fa-brake-warning"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">EBS</span>
                                        <span class="info-box-number">
                                            @if($vehiclecatalog->ebs)
                                                <span class="badge badge-success">Available</span>
                                                @if($vehiclecatalog->ebs_image)
                                                    <br>
                                                    <small>
                                                        <a href="{{ asset('uploads/vehiclecatalog/security-features/' . $vehiclecatalog->ebs_image) }}" 
                                                           target="_blank" class="text-info">
                                                            <i class="fas fa-image"></i> View Image
                                                        </a>
                                                    </small>
                                                @endif
                                            @else
                                                <span class="badge badge-secondary">Not Available</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Air Conditioning -->
                            <div class="col-md-4 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-primary">
                                        <i class="fas fa-snowflake"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Air Conditioning</span>
                                        <span class="info-box-number">
                                            @if($vehiclecatalog->air_conditioning)
                                                <span class="badge badge-success">Available</span>
                                                @if($vehiclecatalog->air_conditioning_image)
                                                    <br>
                                                    <small>
                                                        <a href="{{ asset('uploads/vehiclecatalog/security-features/' . $vehiclecatalog->air_conditioning_image) }}" 
                                                           target="_blank" class="text-info">
                                                            <i class="fas fa-image"></i> View Image
                                                        </a>
                                                    </small>
                                                @endif
                                            @else
                                                <span class="badge badge-secondary">Not Available</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Reverse Camera -->
                            <div class="col-md-4 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-success">
                                        <i class="fas fa-camera-retro"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Reverse Camera</span>
                                        <span class="info-box-number">
                                            @if($vehiclecatalog->reverse_camera)
                                                <span class="badge badge-success">Available</span>
                                                @if($vehiclecatalog->reverse_camera_image)
                                                    <br>
                                                    <small>
                                                        <a href="{{ asset('uploads/vehiclecatalog/security-features/' . $vehiclecatalog->reverse_camera_image) }}" 
                                                           target="_blank" class="text-info">
                                                            <i class="fas fa-image"></i> View Image
                                                        </a>
                                                    </small>
                                                @endif
                                            @else
                                                <span class="badge badge-secondary">Not Available</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Camera 360 -->
                            <div class="col-md-4 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-warning">
                                        <i class="fas fa-camera"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Camera 360</span>
                                        <span class="info-box-number">
                                            @if($vehiclecatalog->camera_360)
                                                <span class="badge badge-success">Available</span>
                                                @if($vehiclecatalog->camera_360_image)
                                                    <br>
                                                    <small>
                                                        <a href="{{ asset('uploads/vehiclecatalog/security-features/' . $vehiclecatalog->camera_360_image) }}" 
                                                           target="_blank" class="text-info">
                                                            <i class="fas fa-image"></i> View Image
                                                        </a>
                                                    </small>
                                                @endif
                                            @else
                                                <span class="badge badge-secondary">Not Available</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Braking System -->
                            <div class="col-md-4 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-danger">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Emergency Braking System</span>
                                        <span class="info-box-number">
                                            @if($vehiclecatalog->emergency_braking_system)
                                                <span class="badge badge-success">Available</span>
                                                @if($vehiclecatalog->emergency_braking_system_image)
                                                    <br>
                                                    <small>
                                                        <a href="{{ asset('uploads/vehiclecatalog/security-features/' . $vehiclecatalog->emergency_braking_system_image) }}" 
                                                           target="_blank" class="text-info">
                                                            <i class="fas fa-image"></i> View Image
                                                        </a>
                                                    </small>
                                                @endif
                                            @else
                                                <span class="badge badge-secondary">Not Available</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Hillside Braking System -->
                            <div class="col-md-4 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-dark">
                                        <i class="fas fa-mountain"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Hillside Braking System</span>
                                        <span class="info-box-number">
                                            @if($vehiclecatalog->hillside_braking_system)
                                                <span class="badge badge-success">Available</span>
                                                @if($vehiclecatalog->hillside_braking_system_image)
                                                    <br>
                                                    <small>
                                                        <a href="{{ asset('uploads/vehiclecatalog/security-features/' . $vehiclecatalog->hillside_braking_system_image) }}" 
                                                           target="_blank" class="text-info">
                                                            <i class="fas fa-image"></i> View Image
                                                        </a>
                                                    </small>
                                                @endif
                                            @else
                                                <span class="badge badge-secondary">Not Available</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Hill Descent Control -->
                            <div class="col-md-4 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-secondary">
                                        <i class="fas fa-arrow-down"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Hill Descent Control</span>
                                        <span class="info-box-number">
                                            @if($vehiclecatalog->hill_descent_control)
                                                <span class="badge badge-success">Available</span>
                                                @if($vehiclecatalog->hill_descent_control_image)
                                                    <br>
                                                    <small>
                                                        <a href="{{ asset('uploads/vehiclecatalog/security-features/' . $vehiclecatalog->hill_descent_control_image) }}" 
                                                           target="_blank" class="text-info">
                                                            <i class="fas fa-image"></i> View Image
                                                        </a>
                                                    </small>
                                                @endif
                                            @else
                                                <span class="badge badge-secondary">Not Available</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicle Gallery -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-images"></i> Vehicle Gallery
                        </h3>
                    </div>
                    <div class="card-body">
                        @php
                            $carImages = $vehiclecatalog->car_images ?? [];
                            if (is_string($carImages)) {
                                $carImages = json_decode($carImages, true);
                            }
                            if (!is_array($carImages)) {
                                $carImages = [];
                            }
                        @endphp

                        @if(!empty($carImages))
                            <div class="row">
                                @foreach($carImages as $img)
                                    @php
                                        $imagePath = is_string($img) ? $img : (is_array($img) ? ($img['path'] ?? null) : null);
                                    @endphp
                                    @if(!empty($imagePath))
                                        <div class="col-md-3 mb-3">
                                            <img src="{{ asset($imagePath) }}"
                                                 class="img-fluid img-thumbnail"
                                                 style="height:180px; object-fit:cover; width:100%;">
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No gallery images available.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-align-left"></i> Description
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($vehiclecatalog->description)
                            <div class="vehicle-description">
                                {!! $vehiclecatalog->description !!}
                            </div>
                        @else
                            <p class="text-muted">No description available.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-clock"></i> System Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Created At:</strong>
                                <p class="text-muted">{{ $vehiclecatalog->created_at ? date('d M, Y h:i A', strtotime($vehiclecatalog->created_at)) : 'N/A' }}</p>
                            </div>
                            <div class="col-md-3">
                                <strong>Updated At:</strong>
                                <p class="text-muted">{{ $vehiclecatalog->updated_at ? date('d M, Y h:i A', strtotime($vehiclecatalog->updated_at)) : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    </div>
</div>
</div>
</div>

</div>
</section>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTables with consistent IDs
    if ($('#permitTable').length) {
        $('#permitTable').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "pageLength": 10
        });
    }
    
    if ($('#serviceTable').length) {
        $('#serviceTable').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "pageLength": 10
        });
    }
    
    if ($('#repairTable').length) {
        $('#repairTable').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "pageLength": 10
        });
    }
    
    if ($('#tyreTable').length) {
        $('#tyreTable').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "pageLength": 10
        });
    }

    // Handle tab click to store active tab in session
    $('a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
        var target = $(e.target).attr("href");
        var tabId = target.substring(1);
        
        // Store in localStorage for client-side persistence
        localStorage.setItem('activeVehicleTab', tabId);
        
        // Store in session via AJAX
        $.ajax({
            url: '{{ route("admin.vehicles.set-active-tab") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                tab: tabId,
                vehicle_id: {{ $vehiclecatalog->id }}
            }
        });
    });

    // Check localStorage for active tab on page load
    var activeTab = localStorage.getItem('activeVehicleTab');
    if (activeTab) {
        $('.nav-link[href="#' + activeTab + '"]').tab('show');
    }
});
</script>
@endpush