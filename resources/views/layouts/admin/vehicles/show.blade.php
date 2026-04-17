@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>
            {{ $vehicle->vehicle_name }} ({{ $vehicle->brand }} {{ $vehicle->model }})
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
    <li class="nav-item">
        <a class="nav-link {{ session('active_tab') == 'permits' ? 'active' : '' }}" 
           data-toggle="pill" href="#permits" role="tab" aria-selected="false">
            <i class="fas fa-file-alt"></i> Permits
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ session('active_tab') == 'services' ? 'active' : '' }}" 
           data-toggle="pill" href="#services" role="tab" aria-selected="false">
            <i class="fas fa-tools"></i> Services
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ session('active_tab') == 'repairs' ? 'active' : '' }}" 
           data-toggle="pill" href="#repairs" role="tab" aria-selected="false">
            <i class="fas fa-wrench"></i> Repairs
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ session('active_tab') == 'tyres' ? 'active' : '' }}" 
           data-toggle="pill" href="#tyres" role="tab" aria-selected="false">
            <i class="fas fa-circle"></i> Tyre Changes
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
                <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Vehicle
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
                        @if($vehicle->image)
                            <img src="{{ asset($vehicle->image) }}" alt="{{ $vehicle->vehicle_name }}" 
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
                                    <span class="info-box-icon bg-info"><i class="fas fa-car"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Vehicle Name</span>
                                        <span class="info-box-number">{{ $vehicle->vehicle_name }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-info"><i class="fas fa-tag"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Brand</span>
                                        <span class="info-box-number">{{ $vehicle->brand }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-info"><i class="fas fa-cog"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Model</span>
                                        <span class="info-box-number">{{ $vehicle->model }}</span>
                                    </div>
                                </div>
                            </div>
                             <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-info"><i class="fas fa-cog"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Seater</span>
                                        <span class="info-box-number">{{ $vehicle->seater }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-info"><i class="fas fa-calendar"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Year</span>
                                        <span class="info-box-number">{{ $vehicle->year }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-dark"><i class="fas fa-tachometer-alt"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Mileage</span>
                                        <span class="info-box-number">
                                            {{ $vehicle->mileage ?? 'N/A' }} KM
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-dark"><i class="fas fa-horse"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Horsepower</span>
                                        <span class="info-box-number">
                                            {{ $vehicle->horsepower ?? 'N/A' }} HP
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-dark"><i class="fas fa-palette"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Color</span>
                                        <span class="info-box-number">
                                            {{ $vehicle->car_color ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-gas-pump"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Fuel Type</span>
                                        <span class="info-box-number">{{ $vehicle->fuel_type }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-secondary"><i class="fas fa-cogs"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Transmission</span>
                                        <span class="info-box-number">{{ $vehicle->transmission }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-info"><i class="fas fa-user"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Helper Needed</span>
                                        <span class="info-box-number">
                                            @if($vehicle->is_helper_needed)
                                                <span class="badge badge-success">Yes</span>
                                            @else
                                                <span class="badge badge-secondary">No</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-{{ $vehicle->status ? 'success' : 'danger' }}">
                                        <i class="fas fa-power-off"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Status</span>
                                        <span class="info-box-number">
                                            @if($vehicle->status)
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
                                <p class="text-muted">{{ $vehicle->registration_number ?? 'N/A' }}</p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Registered At:</strong>
                                <p class="text-muted">{{ $vehicle->registered_at ?? 'N/A' }}</p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Number Plate Color:</strong>
                                <p>
                                    @if($vehicle->number_plate_color)
                                        <span class="badge" style="background-color: {{ strtolower($vehicle->number_plate_color) }}; color: white; padding: 5px 10px;">
                                            {{ $vehicle->number_plate_color }}
                                        </span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Registration Expiry:</strong>
                                <p class="text-muted">{{ $vehicle->registration_expiry ? date('d M, Y', strtotime($vehicle->registration_expiry)) : 'N/A' }}</p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Bill Book Number:</strong>
                                <p class="text-muted">{{ $vehicle->bill_book_number ?? 'N/A' }}</p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Bill Book Image:</strong>
                                <p>
                                    @if($vehicle->bill_book_image)
                                        <a href="{{ asset($vehicle->bill_book_image) }}" target="_blank" class="btn btn-sm btn-info">
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
                        <h3 class="card-title"><i class="fas fa-shield-alt"></i> Insurance Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <strong>Policy Number:</strong>
                                <p class="text-muted">{{ $vehicle->insurance_policy_no ?? 'N/A' }}</p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Insurance Company:</strong>
                                <p class="text-muted">{{ $vehicle->insurance_company ?? 'N/A' }}</p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Insurance Type:</strong>
                                <p class="text-muted">{{ $vehicle->insurance_type ?? 'N/A' }}</p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Valid Till:</strong>
                                <p class="text-muted">{{ $vehicle->insurance_till ? date('d M, Y', strtotime($vehicle->insurance_till)) : 'N/A' }}</p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Cost Per Annum:</strong>
                                <p class="text-muted">{{ $vehicle->insurance_cost_per_annum ? 'Rs '.number_format($vehicle->insurance_cost_per_annum, 2) : 'N/A' }}</p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>Policy Document:</strong>
                                <p>
                                    @if($vehicle->insurance_policy_document)
                                        <a href="{{ asset($vehicle->insurance_policy_document) }}" target="_blank" class="btn btn-sm btn-success">
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
$carImages = $vehicle->car_images ?? [];
@endphp

@if($carImages && count($carImages))
    <div class="row">
        @foreach($carImages as $img)
            <div class="col-md-3 mb-3">
                <img src="{{ asset($img) }}" 
                     class="img-fluid img-thumbnail"
                     style="height:180px; object-fit:cover; width:100%;">
            </div>
        @endforeach
    </div>
@else
    <p class="text-muted">No gallery images available.</p>
@endif
            </div>
        </div>
    </div>
</div>
<div class="row mt-4">
    <div class="col-12">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-align-left"></i> Description
                </h3>
            </div>
            <div class="card-body">

                @if($vehicle->description)
                    <div class="vehicle-description">
                        {!! $vehicle->description !!}
                    </div>
                @else
                    <p class="text-muted">No description available.</p>
                @endif

            </div>
        </div>
    </div>
</div>

        <!-- Additional Information Row if needed -->
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
                                <p class="text-muted">{{ $vehicle->created_at ? date('d M, Y h:i A', strtotime($vehicle->created_at)) : 'N/A' }}</p>
                            </div>
                            <div class="col-md-3">
                                <strong>Updated At:</strong>
                                <p class="text-muted">{{ $vehicle->updated_at ? date('d M, Y h:i A', strtotime($vehicle->updated_at)) : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= PERMITS TAB ================= -->
    <div class="tab-pane fade {{ session('active_tab') == 'permits' ? 'show active' : '' }}" 
         id="permits" role="tabpanel">
        <div class="mb-3">
            <a href="{{ route('admin.vehicle-permits.create',['vehicle_id'=>$vehicle->id]) }}"
               class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Add Permit
            </a>
        </div>

        <table id="permitTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>SN</th>
                    <th>Organization</th>
                    <th>Expiry Date</th>
                    <th>Document</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vehicle->permits as $permit)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $permit->permit_from_organization }}</td>
                    <td>{{ $permit->permit_expiry_date }}</td>
                    <td>
                        @if($permit->permit_document)
                            <a href="{{ asset($permit->permit_document) }}" target="_blank">View</a>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.vehicle-permits.edit',$permit->id) }}"
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>

                        <form action="{{ route('admin.vehicle-permits.destroy',$permit->id) }}"
                              method="POST"
                              style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- ================= SERVICES TAB ================= -->
    <div class="tab-pane fade {{ session('active_tab') == 'services' ? 'show active' : '' }}" 
         id="services" role="tabpanel">
        <div class="mb-3">
            <a href="{{ route('admin.vehicle-services.create',['vehicle_id'=>$vehicle->id]) }}"
               class="btn btn-primary btn-sm">
                Add Service
            </a>
        </div>

        <table id="serviceTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>SN</th>
                    <th>Date</th>
                    <th>Done At</th>
                    <th>Amount</th>
                    <th>Next Service</th>
                    <th>Next Service (KM)</th>
                    <th>Bill</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vehicle->services as $service)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $service->service_date }}</td>
                    <td>{{ $service->service_done_at }}</td>
                    <td>Rs {{ number_format($service->service_amount,2) }}</td>
                    <td>{{ $service->next_service_date }}</td>
                    <td>{{ $service->next_service_km }}</td>
                     <td>
                        @if($service->service_bill_copy)
                            <a href="{{ asset($service->service_bill_copy) }}" target="_blank">View</a>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.vehicle-services.edit',$service->id) }}"
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>

                        <form action="{{ route('admin.vehicle-services.destroy',$service->id) }}"
                              method="POST"
                              style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- ================= REPAIRS TAB ================= -->
    <div class="tab-pane fade {{ session('active_tab') == 'repairs' ? 'show active' : '' }}" 
         id="repairs" role="tabpanel">
        <div class="mb-3">
            <a href="{{ route('admin.vehicle-repairs.create',['vehicle_id'=>$vehicle->id]) }}"
               class="btn btn-primary btn-sm">
                Add Repair
            </a>
        </div>

        <table id="repairTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>SN</th>
                    <th>Date</th>
                    <th>Vendor Name</th>
                    {{-- <th>Driver Name</th> --}}
                    <th>Amount</th>
                    <th>Bill</th>
                    <th>Insurance Claim</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vehicle->repairs as $repair)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $repair->repair_date }}</td>
                    <td>{{ $repair->vendor ? $repair->vendor->name : 'N/A' }}</td>
                    {{-- <td>{{ $repair->driver ? $repair->driver->name : 'N/A' }}</td>  --}}
                    <td>Rs {{ number_format($repair->repair_amount,2) }}</td>
                    <td>
                    @if($repair->bill)
                        <a href="{{ asset($repair->bill) }}" target="_blank" class="btn btn-info btn-sm">View Bill</a>
                    @else
                        No Bill
                    @endif
                </td>
                <td>{{ $repair->claim_insurance == 'Y' ? 'Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('admin.vehicle-repairs.edit',$repair->id) }}"
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>

                        <form action="{{ route('admin.vehicle-repairs.destroy',$repair->id) }}"
                              method="POST"
                              style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- ================= TYRE TAB ================= -->
    <div class="tab-pane fade {{ session('active_tab') == 'tyres' ? 'show active' : '' }}" 
         id="tyres" role="tabpanel">
        <div class="mb-3">
            <a href="{{ route('admin.vehicle-tyre-changes.create',['vehicle_id'=>$vehicle->id]) }}"
               class="btn btn-primary btn-sm">
                Add Tyre Change
            </a>
        </div>

        <table id="tyreTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>SN</th>
                    <th>Date</th>
                    <th>Position</th>
                    <th>Manufacturer</th>
                    <th>Amount</th>
                    <th>Invoice</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vehicle->tyreChanges as $tyre)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $tyre->change_date }}</td>
                    <td>{{ $tyre->tyre_position }}</td>
                    <td>{{ $tyre->tyre_manufacturer }}</td>
                    <td>Rs {{ number_format($tyre->amount,2) }}</td>
                     <td>
                        @if($tyre->invoice_upload)
                            <a href="{{ asset($tyre->invoice_upload) }}" target="_blank">View</a>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.vehicle-tyre-changes.edit',$tyre->id) }}"
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>

                        <form action="{{ route('admin.vehicle-tyre-changes.destroy',$tyre->id) }}"
                              method="POST"
                              style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
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
                vehicle_id: {{ $vehicle->id }}
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