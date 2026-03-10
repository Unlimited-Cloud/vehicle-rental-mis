@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="m-0">
                <i class="fas fa-history"></i> Vehicle Moment Details
                <small class="text-muted">#{{ $moment->id }}</small>
            </h1>
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.vehicle_moments.index') }}">Vehicle Moments</a></li>
                <li class="breadcrumb-item active">Details</li>
            </ol>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">

@include('layouts.admin_theme.alert')

<div class="card card-primary card-outline card-tabs">
<div class="card-header p-0 pt-1 border-bottom-0">
    <ul class="nav nav-tabs" id="momentTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="pill" href="#overview" role="tab">
                <i class="fas fa-info-circle"></i> Overview
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#journey" role="tab">
                <i class="fas fa-route"></i> Journey Details
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#questionnaire" role="tab">
                <i class="fas fa-clipboard-list"></i> Questionnaire
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#incident" role="tab">
                <i class="fas fa-exclamation-triangle"></i> Incident Report
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#images" role="tab">
                <i class="fas fa-images"></i> Images
            </a>
        </li>
    </ul>
</div>

<div class="card-body">
<div class="tab-content">
    
    <!-- ================= OVERVIEW TAB ================= -->
    <div class="tab-pane fade show active" id="overview" role="tabpanel">
        
        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12 text-right">
                <a href="{{ route('admin.vehicle_moments.edit', $moment->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Moment
                </a>
                <a href="{{ route('admin.vehicle_moments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format($moment->end_km - $moment->start_km) }} km</h3>
                        <p>Total Distance</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-road"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ \Carbon\Carbon::parse($moment->start_datetime)->format('d M') }}</h3>
                        <p>Start Date</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ \Carbon\Carbon::parse($moment->end_datetime)->format('d M') }}</h3>
                        <p>End Date</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $moment->has_incident ? 'Yes' : 'No' }}</h3>
                        <p>Incident</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Information Grid -->
        <div class="row mt-4">
            <!-- Booking Information -->
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-book"></i> Booking Information</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                           <tr>
                                <th width="150">Booking ID:</th>
                                <td>
                                    <a href="{{ route('admin.vehicle_bookings.show', $moment->booking_id) }}" title="View Details">
                                        <span class="badge badge-primary p-2">#{{ $moment->booking_id }}</span>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Customer:</th>
                                <td>{{ $moment->customer_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Start Date:</th>
                                <td>{{ \Carbon\Carbon::parse($moment->start_date)->format('d M, Y h:i A') }}</td>
                            </tr>
                            <tr>
                                <th>End Date:</th>
                                <td>{{ \Carbon\Carbon::parse($moment->end_date)->format('d M, Y h:i A') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Vehicle & Crew Information -->
            <div class="col-md-6">
                <div class="card card-outline card-success">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title"><i class="fas fa-truck"></i> Vehicle & Crew</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">Vehicle:</th>
                                <td>
                                    <strong>{{ $moment->vehicle_name ?? 'N/A' }}</strong>
                                    <small class="text-muted">({{ $moment->vehicle_no ?? 'N/A' }})</small>
                                </td>
                            </tr>
                            <tr>
                                <th>Driver:</th>
                                <td>
                                    <i class="fas fa-user text-info"></i> 
                                    {{ $moment->driver_name ?? 'N/A' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Helper:</th>
                                <td>
                                    <i class="fas fa-user-friends text-success"></i> 
                                    {{ $moment->helper_name ?? 'N/A' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= JOURNEY DETAILS TAB ================= -->
    <div class="tab-pane fade" id="journey" role="tabpanel">
        <div class="row">
            <!-- Start Journey Card -->
            <div class="col-md-6">
                <div class="card card-outline card-info">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title"><i class="fas fa-play-circle"></i> Start Journey</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">Date & Time:</th>
                                <td>{{ \Carbon\Carbon::parse($moment->start_datetime)->format('d M, Y h:i A') }}</td>
                            </tr>
                            <tr>
                                <th>Start KM:</th>
                                <td><span class="badge badge-info p-2">{{ number_format($moment->start_km) }} km</span></td>
                            </tr>
                            <tr>
                                <th>Start Comments:</th>
                                <td>{{ $moment->start_comments ?? 'No comments' }}</td>
                            </tr>
                            <tr>
                                <th>Start Image:</th>
                                <td>
                                    @if($moment->start_image)
                                        <a href="{{ asset($moment->start_image) }}" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View Image
                                        </a>
                                    @else
                                        <span class="text-muted">No image</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- End Journey Card -->
            <div class="col-md-6">
                <div class="card card-outline card-warning">
                    <div class="card-header bg-warning text-white">
                        <h3 class="card-title"><i class="fas fa-stop-circle"></i> End Journey</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">Date & Time:</th>
                                <td>{{ \Carbon\Carbon::parse($moment->end_datetime)->format('d M, Y h:i A') }}</td>
                            </tr>
                            <tr>
                                <th>End KM:</th>
                                <td><span class="badge badge-warning p-2">{{ number_format($moment->end_km) }} km</span></td>
                            </tr>
                            <tr>
                                <th>End Comments:</th>
                                <td>{{ $moment->end_comments ?? 'No comments' }}</td>
                            </tr>
                            <tr>
                                <th>End Image:</th>
                                <td>
                                    @if($moment->end_image)
                                        <a href="{{ asset($moment->end_image) }}" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View Image
                                        </a>
                                    @else
                                        <span class="text-muted">No image</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- <!-- Fuel Information -->
        @if($moment->approx_fuel_litre)
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card card-outline card-secondary">
                    <div class="card-header bg-secondary text-white">
                        <h3 class="card-title"><i class="fas fa-gas-pump"></i> Fuel Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-secondary"><i class="fas fa-gas-pump"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Approx Fuel Used</span>
                                <span class="info-box-number">{{ $moment->approx_fuel_litre }} Litres</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif --}}
    </div>

    <!-- ================= QUESTIONNAIRE TAB ================= -->
    <div class="tab-pane fade" id="questionnaire" role="tabpanel">
        <div class="card card-outline card-primary">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title"><i class="fas fa-clipboard-check"></i> Inspection Questionnaire Answers</h3>
            </div>
           <div class="card-body">
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="bg-light">
                <tr>
                    <th width="50">SN</th>
                    <th>Question</th>
                    <th>Answer</th>
                </tr>
            </thead>
            <tbody>
                @foreach($questionnaireAnswers as $index => $answer)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $answer->question ?? 'N/A' }}</td>
                    <td>
                        @php
                            $answerText = $answer->answer;

                            if($answerText == 'yes') {
                                echo '<span class="badge badge-success p-2"><i class="fas fa-check"></i> Yes</span>';
                            } elseif($answerText == 'no') {
                                echo '<span class="badge badge-danger p-2"><i class="fas fa-times"></i> No</span>';
                            } elseif($answerText == 'na') {
                                echo '<span class="badge badge-secondary p-2">N/A</span>';
                            } else {
                                echo nl2br(e($answerText));
                            }
                        @endphp
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
        </div>
    </div>

    <!-- ================= INCIDENT TAB ================= -->
    <div class="tab-pane fade" id="incident" role="tabpanel">
        <div class="card card-outline card-danger">
            <div class="card-header bg-danger text-white">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Incident Report</h3>
            </div>
            <div class="card-body">
                @if($moment->has_incident)
                    <div class="callout callout-danger">
                        <h5><i class="fas fa-exclamation-circle"></i> Incident Details:</h5>
                        <p>{{ $moment->incident_report ?? 'No details provided' }}</p>
                    </div>
                @else
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> No incidents reported for this journey.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ================= IMAGES TAB ================= -->
    <div class="tab-pane fade" id="images" role="tabpanel">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-outline card-info">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title"><i class="fas fa-camera"></i> Start Image</h3>
                    </div>
                    <div class="card-body text-center">
                        @if($moment->start_image)
                            <a href="{{ asset($moment->start_image) }}" target="_blank">
                                <img src="{{ asset($moment->start_image) }}" alt="Start Image" class="img-fluid img-thumbnail" style="max-height: 400px;">
                            </a>
                            <p class="mt-2">
                                <a href="{{ asset($moment->start_image) }}" download class="btn btn-sm btn-primary">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </p>
                        @else
                            <div class="bg-light p-5 rounded">
                                <i class="fas fa-camera fa-5x text-muted"></i>
                                <p class="mt-3">No start image available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-outline card-warning">
                    <div class="card-header bg-warning text-white">
                        <h3 class="card-title"><i class="fas fa-camera"></i> End Image</h3>
                    </div>
                    <div class="card-body text-center">
                        @if($moment->end_image)
                            <a href="{{ asset($moment->end_image) }}" target="_blank">
                                <img src="{{ asset($moment->end_image) }}" alt="End Image" class="img-fluid img-thumbnail" style="max-height: 400px;">
                            </a>
                            <p class="mt-2">
                                <a href="{{ asset($moment->end_image) }}" download class="btn btn-sm btn-primary">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </p>
                        @else
                            <div class="bg-light p-5 rounded">
                                <i class="fas fa-camera fa-5x text-muted"></i>
                                <p class="mt-3">No end image available</p>
                            </div>
                        @endif
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

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<style>
    .small-box {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .card-header {
        border-radius: 8px 8px 0 0 !important;
    }
    .callout {
        border-radius: 5px;
        padding: 15px;
    }
    .callout-danger {
        background-color: #fff5f5;
        border-left: 5px solid #dc3545;
    }
    .info-box {
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .nav-tabs .nav-link {
        font-weight: 500;
        padding: 10px 20px;
    }
    .nav-tabs .nav-link.active {
        border-bottom: 3px solid #007bff;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Handle tab click to store active tab
    $('a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
        localStorage.setItem('activeMomentTab', $(e.target).attr('href'));
    });

    // Check localStorage for active tab
    var activeTab = localStorage.getItem('activeMomentTab');
    if (activeTab) {
        $('.nav-tabs a[href="' + activeTab + '"]').tab('show');
    }

    // Image zoom on click
    $('.img-thumbnail').on('click', function() {
        window.open($(this).attr('src'), '_blank');
    });
});
</script>
@endpush