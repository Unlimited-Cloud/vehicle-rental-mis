@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
<div class="container-fluid">
<h1>Add Vehicle Movement</h1>
</div>
</div>

<section class="content">
<div class="container-fluid">

<div class="card card-primary card-outline">
<div class="card-header">
    <h3 class="card-title">Vehicle Movement Details</h3>
</div>

<form action="{{ isset($moment) ? route('admin.vehicle_moments.update', $moment->id) : route('admin.vehicle_moments.store') }}" method="POST" enctype="multipart/form-data">

@csrf
@if(isset($moment))
@method('PUT')
@endif

<div class="card-body">

@include('layouts.admin_theme.alert')

<!-- Booking Information -->
<div class="row">
    <div class="col-12">
        <h4 class="mb-3">Booking Information</h4>
    </div>
    
    <input type="hidden" name="booking_id" value="{{ $booking->id }}">

    <div class="col-md-4">
        <div class="form-group">
            <label>Vehicle <span class="text-danger">*</span></label>
            <select name="vehicle_no" class="form-control select2" required>
                <option value="">Select Vehicle</option>
                @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" 
                        {{ $booking->vehicle_id == $vehicle->id ? 'selected' : '' }}
                        data-vehicle-type="{{ $vehicle->vehicle_type }}">
                        {{ $vehicle->vehicle_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Driver</label>
            <input type="hidden" name="driver_id" value="{{ $booking->driver_id }}">
            <input type="text" class="form-control bg-light" 
                   value="{{ $booking->driver_name }}" 
                   readonly>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Helper</label>
            <select name="helper_id" class="form-control select2">
                <option value="">Select Helper</option>
                @foreach($helpers as $helper)
                    <option value="{{ $helper->crew_id }}" 
                        {{ $booking->helper_id == $helper->crew_id ? 'selected' : '' }}>
                        {{ $helper->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<!-- Trip Category and Route Section -->
<div class="row mt-4">
    <div class="col-12">
        <h4 class="mb-3">Trip Information</h4>
    </div>
    
    <div class="col-md-6">
        <div class="form-group">
            <label>Trip Category</label>
            <select name="trip_category_id" id="trip_category_id" class="form-control select2">
                <option value="">Select Category</option>
                @foreach($tripCategories as $category)
                    <option value="{{ $category->id }}" 
                        {{ old('trip_category_id', $moment->trip_category_id ?? $booking->trip_category_id ?? '') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Leave empty to use booking default</small>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Trip Route</label>
            <select name="trip_route_id" id="trip_route_id" class="form-control select2">
                <option value="">Select Route</option>
                @if(isset($moment) && $moment->trip_route_id)
                    @php
                        $selectedRoute = \App\Models\TripRoute::find($moment->trip_route_id);
                    @endphp
                    @if($selectedRoute)
                        <option value="{{ $selectedRoute->id }}" selected>{{ $selectedRoute->title }}</option>
                    @endif
                @elseif(isset($booking) && $booking->trip_route_id)
                    @php
                        $selectedRoute = \App\Models\TripRoute::find($booking->trip_route_id);
                    @endphp
                    @if($selectedRoute)
                        <option value="{{ $selectedRoute->id }}" selected>{{ $selectedRoute->title }}</option>
                    @endif
                @endif
            </select>
            <small class="text-muted">Leave empty to use booking default</small>
        </div>
    </div>
</div>

<!-- Start Journey Details -->
<div class="row mt-4">
    <div class="col-12">
        <h4 class="mb-3">Start Journey Details</h4>
    </div>
    
    <div class="col-md-4">
        <div class="form-group">
            <label>Start Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local"
                   name="start_datetime"
                   class="form-control"
                  value="{{ \Carbon\Carbon::parse($booking->start_date . ' ' . $booking->start_time)->format('Y-m-d\TH:i') }}"
                   required>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Start KM <span class="text-danger">*</span></label>
            <input type="number" 
                   name="start_km" 
                   class="form-control" 
                   value="{{ old('start_km', $moment->start_km ?? $booking->start_km ?? '') }}" 
                   step="0.01" 
                   min="0"
                   placeholder="Enter starting kilometer"
                   required>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Start Image</label>
            <div class="custom-file">
                <input type="file" name="start_image" class="custom-file-input" id="startImage">
                <label class="custom-file-label" for="startImage">Choose file</label>
            </div>
           @if(isset($moment) && $moment->start_image)
            <img src="{{ asset($moment->start_image) }}" width="120" class="mt-2">
            @endif
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label>Start Comments</label>
            <textarea name="start_comments" 
                      class="form-control" 
                      rows="2"
                      placeholder="Any notes about the start of journey">{{ old('start_comments', $moment->start_comments ?? $booking->start_comments ?? '') }}</textarea>
        </div>
    </div>
</div>

<!-- Questionnaires -->
@forelse($questionnaires as $index => $question)

@php
$selectedAnswer = old('answers.' . $question->id, $answers[$question->id] ?? null);
@endphp

<div class="row mb-3 questionnaire-item" data-question-id="{{ $question->id }}">
    <div class="col-md-12">
        <div class="form-group">

            <label>
                <strong>{{ $index + 1 }}. {{ $question->question }}</strong>
                @if($question->is_required)
                    <span class="text-danger">*</span>
                @endif
            </label>

            <input type="hidden" name="questionnaire_ids[]" value="{{ $question->id }}">

            @if($question->type == 'yes_no')
            <div class="mt-2">

                <div class="form-check form-check-inline">
                    <input class="form-check-input"
                           type="radio"
                           name="answers[{{ $question->id }}]"
                           id="yes_{{ $question->id }}"
                           value="yes"
                           {{ $selectedAnswer == 'yes' ? 'checked' : '' }}
                           {{ $question->is_required ? 'required' : '' }}>
                    <label class="form-check-label" for="yes_{{ $question->id }}">Yes</label>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input"
                           type="radio"
                           name="answers[{{ $question->id }}]"
                           id="no_{{ $question->id }}"
                           value="no"
                           {{ $selectedAnswer == 'no' ? 'checked' : '' }}>
                    <label class="form-check-label" for="no_{{ $question->id }}">No</label>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input"
                           type="radio"
                           name="answers[{{ $question->id }}]"
                           id="na_{{ $question->id }}"
                           value="na"
                           {{ $selectedAnswer == 'na' ? 'checked' : '' }}>
                    <label class="form-check-label" for="na_{{ $question->id }}">N/A</label>
                </div>

            </div>

            @else

            <textarea class="form-control mt-2"
                      name="answers[{{ $question->id }}]"
                      rows="2"
                      placeholder="Enter your answer here..."
                      {{ $question->is_required ? 'required' : '' }}>{{ $selectedAnswer }}</textarea>

            @endif

            @if($question->type == 'yes_no')
                <small class="text-muted">Select one option</small>
            @else
                <small class="text-muted">Please provide detailed information</small>
            @endif

        </div>
    </div>
</div>

@if(!$loop->last)
<hr>
@endif

@empty
<div class="alert alert-info mb-0">
    <i class="fas fa-info-circle"></i> No questionnaires available.
</div>
@endforelse

<!-- End Journey Details -->
<div class="row mt-4">
    <div class="col-12">
        <h4 class="mb-3">End Journey Details</h4>
    </div>
    
    <div class="col-md-4">
        <div class="form-group">
            <label>End Date & Time </label>
            <input type="datetime-local"
                   name="end_datetime"
                   class="form-control"
                   value="{{ \Carbon\Carbon::parse($booking->end_date)->format('Y-m-d\TH:i') }}"
                   >
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>End KM </label>
            <input type="number" 
                   name="end_km" 
                   class="form-control"
                   value="{{ $booking->end_km ?? '' }}"
                   step="0.01" 
                   min="0"
                   placeholder="Enter ending kilometer"
                   >
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>End Image</label>
            <div class="custom-file">
                <input type="file" name="end_image" class="custom-file-input" id="endImage">
                <label class="custom-file-label" for="endImage">Choose file</label>
            </div>
           @if(isset($moment) && $moment->end_image)
            <img src="{{ asset($moment->end_image) }}" width="120" class="mt-2">
            @endif
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label>End Comments</label>
            <textarea name="end_comments" 
                      class="form-control" 
                      rows="2"
                      placeholder="Any notes about the end of journey">{{ old('end_comments', $moment->end_comments ?? $booking->end_comments ?? '') }}</textarea>
        </div>
    </div>
</div>

<!-- ALLOWANCES SECTION -->
<div class="row mt-4">
    <div class="col-12">
        <h4 class="mb-3">Allowances & Salary Details</h4>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Enter allowances for driver and helper.
        </div>
    </div>

    <!-- Driver Allowances -->
    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header">
                <h5 class="mb-0">Driver Allowance</h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Allowances Date</label>
                    <input type="date" 
                           name="attendance_date" 
                           class="form-control" 
                            value="{{ old('attendance_date', \Carbon\Carbon::now()->format('Y-m-d')) }}"
                           >
                </div>

                <div class="form-group">
                    <label>Driver Allowance</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" 
                               name="driver_allowance" 
                               class="form-control" 
                               value="{{ old('driver_allowance', $driverAllowance ?? 0) }}"
                               step="0.01" 
                               min="0"
                               placeholder="Enter driver allowance">
                    </div>
                </div>


                <div class="form-group">
                    <label>Driver Remarks</label>
                    <textarea name="driver_remarks" 
                              class="form-control" 
                              rows="2"
                              placeholder="Driver remarks">{{ old('driver_remarks', $driverRemarks ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Helper Allowances -->
    <div class="col-md-6">
        <div class="card card-success">
            <div class="card-header">
                <h5 class="mb-0">Helper Allowance</h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Helper Allowance</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" 
                               name="helper_allowance" 
                               class="form-control" 
                               value="{{ old('helper_allowance', $helperAllowance ?? 0) }}"
                               step="0.01" 
                               min="0"
                               placeholder="Enter helper allowance">
                    </div>
                </div>

               
                <div class="form-group">
                    <label>Helper Remarks</label>
                    <textarea name="helper_remarks" 
                              class="form-control" 
                              rows="2"
                              placeholder="Helper remarks">{{ old('helper_remarks', $helperRemarks ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incident Information -->
<div class="row mt-4">
    <div class="col-12">
        <h4 class="mb-3">Incident Information</h4>
    </div>

    <div class="col-md-12" id="incidentReportField">
        <div class="form-group">
            <label>Incident Report, If exist<span class="text-danger"></span></label>
            <textarea name="incident_report" 
                      id="incidentReport"
                      class="form-control" 
                      rows="4"
                      placeholder="Please describe the incident in detail">{{ old('incident_report', $moment->incident_report ?? $booking->incident_report ?? '') }}</textarea>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Incident Image</label>
            <div class="custom-file">
                <input type="file" name="incident_image" class="custom-file-input" id="incident_image">
                <label class="custom-file-label" for="incident_image">Choose file</label>
            </div>
           @if(isset($moment) && $moment->incident_image)
            <img src="{{ asset($moment->incident_image) }}" width="120" class="mt-2">
            @endif
        </div>
    </div>
</div>

</div>

<div class="card-footer text-right">
    <a href="{{ route('admin.vehicle_moments.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>

    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i>
        {{ isset($moment) ? 'Update Movement' : 'Add Movement' }}
    </button>
</div>

</form>
</div>
</div>
</section>


{{-- @push('styles') --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .custom-file-label::after {
        content: "Browse";
    }
    .bg-light {
        background-color: #f8f9fa !important;
    }
    h4 {
        color: #007bff;
        border-bottom: 2px solid #dee2e6;
        padding-bottom: 8px;
    }
    .text-danger {
        font-weight: bold;
    }
    .d-block {
        display: block;
    }
    .card-info .card-header {
        background-color: #17a2b8;
        color: white;
    }
    .card-success .card-header {
        background-color: #28a745;
        color: white;
    }
</style>
{{-- @endpush --}}

{{-- @push('scripts') --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- 2. Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<script>
    $(document).ready(function() {
        // Initialize Select2
        // $('.select2').select2({
        //     placeholder: 'Select an option',
        //     width: '100%'
        // });

        // Custom file input label update
        $('.custom-file-input').on('change', function () {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label')
                .addClass("selected")
                .html(fileName);
        });

        $('#trip_category_id').on('change', function () {
    console.log('🔥 Category changed!', $(this).val());
});

        // Trip Category Change - Load Routes via AJAX
      $('#trip_category_id').change(function() {
        var category_id = $(this).val();
        $('#trip_route_id').html('<option value="">Loading...</option>');

        if (category_id) {
            $.ajax({
                url: '/dashboard/get-trip-routes/' + category_id,
                type: 'GET',
                success: function(routes) {
                    var options = '<option value="">Select Route</option>';
                    
                    $.each(routes, function(index, route) {
                        options += '<option value="' + route.id + '" ' +
                            'data-car="' + (route.car_price || 0) + '" ' +
                            'data-hiace="' + (route.hiace_price || 0) + '" ' +
                            'data-coaster="' + (route.coaster_price || 0) + '" ' +
                            'data-bus="' + (route.bus_price || 0) + '">' +
                            route.title +
                            '</option>';
                    });

                    $('#trip_route_id').html(options);
                    
                    // If editing, set the previously selected route
                    @if(isset($booking) && $booking->trip_route_id)
                        $('#trip_route_id').val('{{ $booking->trip_route_id }}');
                    @endif
                },
                error: function() {
                    $('#trip_route_id').html('<option value="">Error loading routes</option>');
                }
            });
        } else {
            $('#trip_route_id').html('<option value="">Select Route</option>');
        }
    });
        // Route selection - Update any related fields (like rate if needed)
        $('#trip_route_id').change(function() {
            var selectedRoute = $(this).find(':selected');
            var routeId = selectedRoute.val();
            
            if (routeId) {
                console.log('Route selected:', {
                    id: routeId,
                    title: selectedRoute.text(),
                    car_price: selectedRoute.data('car'),
                    hiace_price: selectedRoute.data('hiace'),
                    coaster_price: selectedRoute.data('coaster'),
                    bus_price: selectedRoute.data('bus')
                });
                
                // You can add additional logic here if needed
                // For example, auto-populate rate based on vehicle type
                var vehicleSelect = $('select[name="vehicle_no"]');
                var vehicleOption = vehicleSelect.find(':selected');
                var vehicleType = vehicleOption.data('vehicle-type');
                
                if (vehicleType && routeId) {
                    var rate = selectedRoute.data(vehicleType.toLowerCase());
                    if (rate && rate > 0) {
                        // If you have a rate field, you can set it here
                        // $('input[name="rate"]').val(rate);
                        console.log('Suggested rate for ' + vehicleType + ': ' + rate);
                    }
                }
            }
        });

        // Vehicle change - Update rate if route is selected
        $('select[name="vehicle_no"]').change(function() {
            if ($('#trip_route_id').val()) {
                $('#trip_route_id').trigger('change');
            }
        });

        // Trigger category change on page load to load routes for existing selection
        @if(isset($moment) && $moment->trip_category_id)
            $('#trip_category_id').trigger('change');
        @elseif(isset($booking) && $booking->trip_category_id)
            $('#trip_category_id').trigger('change');
        @endif

        // Form validation for yes/no radio buttons
        $('form').on('submit', function (e) {
            let isValid = true;
            let errorMessage = 'Please answer all required questions.';
            
            // Check each questionnaire item
            $('.questionnaire-item').each(function() {
                let questionId = $(this).data('question-id');
                let isRequired = $(this).find('input[type="radio"]').first().prop('required');
                
                // If it's a yes/no question and required
                if (isRequired) {
                    let radioName = `answers[${questionId}]`;
                    let isChecked = $(this).find(`input[name="${radioName}"]:checked`).length > 0;
                    
                    if (!isChecked) {
                        isValid = false;
                        $(this).addClass('has-error');
                    } else {
                        $(this).removeClass('has-error');
                    }
                }
                
                // Check required textareas
                let requiredTextarea = $(this).find('textarea[required]');
                if (requiredTextarea.length > 0 && requiredTextarea.val().trim() === '') {
                    isValid = false;
                    $(this).addClass('has-error');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert(errorMessage);
            }
        });
    });
</script>
{{-- @endpush --}}
@endsection