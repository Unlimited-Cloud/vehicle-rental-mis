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

<!-- Depot Departure -->
<div class="row mt-4">
    <div class="col-12">
        <h4 class="mb-3">Depot Departure</h4>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Depot Departure Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local"
                   name="depot_departure_datetime"
                   class="form-control"
                   value="{{ old('depot_departure_datetime', isset($moment) && $moment->depot_departure_datetime ? \Carbon\Carbon::parse($moment->depot_departure_datetime)->format('Y-m-d\TH:i') : '') }}"
                   required>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Depot Departure KM <span class="text-danger">*</span></label>
            <input type="number"
                   name="depot_departure_km"
                   class="form-control"
                   value="{{ old('depot_departure_km', $moment->depot_departure_km ?? '') }}"
                   step="0.01"
                   min="0"
                   placeholder="Enter depot departure kilometer"
                   required>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Depot Departure Image</label>
            <div class="custom-file">
                <input type="file" name="depot_departure_image" class="custom-file-input" id="depotDepartureImage">
                <label class="custom-file-label" for="depotDepartureImage">Choose file</label>
            </div>
            @if(isset($moment) && $moment->depot_departure_image)
            <img src="{{ asset($moment->depot_departure_image) }}" width="120" class="mt-2">
            @endif
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label>Depot Departure Comments</label>
            <textarea name="depot_departure_comments"
                      class="form-control"
                      rows="2"
                      placeholder="Any notes about departure from depot">{{ old('depot_departure_comments', $moment->depot_departure_comments ?? '') }}</textarea>
        </div>
    </div>
</div>

<!-- Pickup Arrival -->
<div class="row mt-4">
    <div class="col-12">
        <h4 class="mb-3">Pickup Arrival</h4>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Pickup Arrival Date & Time</label>
            <input type="datetime-local"
                   name="pickup_arrival_datetime"
                   class="form-control"
                   value="{{ old('pickup_arrival_datetime', isset($moment) && $moment->pickup_arrival_datetime ? \Carbon\Carbon::parse($moment->pickup_arrival_datetime)->format('Y-m-d\TH:i') : '') }}">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Pickup Arrival KM</label>
            <input type="number"
                   name="pickup_arrival_km"
                   class="form-control"
                   value="{{ old('pickup_arrival_km', $moment->pickup_arrival_km ?? '') }}"
                   step="0.01"
                   min="0"
                   placeholder="Enter pickup arrival kilometer">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Pickup Arrival Image</label>
            <div class="custom-file">
                <input type="file" name="pickup_arrival_image" class="custom-file-input" id="pickupArrivalImage">
                <label class="custom-file-label" for="pickupArrivalImage">Choose file</label>
            </div>
            @if(isset($moment) && $moment->pickup_arrival_image)
            <img src="{{ asset($moment->pickup_arrival_image) }}" width="120" class="mt-2">
            @endif
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label>Pickup Arrival Comments</label>
            <textarea name="pickup_arrival_comments"
                      class="form-control"
                      rows="2"
                      placeholder="Any notes about arrival at pickup point">{{ old('pickup_arrival_comments', $moment->pickup_arrival_comments ?? '') }}</textarea>
        </div>
    </div>
</div>

<!-- Drop Off -->
<div class="row mt-4">
    <div class="col-12">
        <h4 class="mb-3">Drop Off</h4>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Drop Off Date & Time</label>
            <input type="datetime-local"
                   name="dropoff_datetime"
                   class="form-control"
                   value="{{ old('dropoff_datetime', isset($moment) && $moment->dropoff_datetime ? \Carbon\Carbon::parse($moment->dropoff_datetime)->format('Y-m-d\TH:i') : '') }}">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Drop Off KM</label>
            <input type="number"
                   name="dropoff_km"
                   class="form-control"
                   value="{{ old('dropoff_km', $moment->dropoff_km ?? '') }}"
                   step="0.01"
                   min="0"
                   placeholder="Enter drop off kilometer">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Drop Off Image</label>
            <div class="custom-file">
                <input type="file" name="dropoff_image" class="custom-file-input" id="dropoffImage">
                <label class="custom-file-label" for="dropoffImage">Choose file</label>
            </div>
            @if(isset($moment) && $moment->dropoff_image)
            <img src="{{ asset($moment->dropoff_image) }}" width="120" class="mt-2">
            @endif
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label>Drop Off Comments</label>
            <textarea name="dropoff_comments"
                      class="form-control"
                      rows="2"
                      placeholder="Any notes about the drop off">{{ old('dropoff_comments', $moment->dropoff_comments ?? '') }}</textarea>
        </div>
    </div>
</div>

<!-- Estimated Return -->
<div class="row mt-4">
    <div class="col-12">
        <h4 class="mb-3">Estimated Return</h4>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>Est. Return to Depot (KM)</label>
            <input type="number"
                   name="estimated_return_to_depot_km"
                   class="form-control"
                   value="{{ old('estimated_return_to_depot_km', $moment->estimated_return_to_depot_km ?? '') }}"
                   step="0.01"
                   min="0"
                   placeholder="KM">
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>Est. Return to Depot (Minutes)</label>
            <input type="number"
                   name="estimated_return_to_depot_minutes"
                   class="form-control"
                   value="{{ old('estimated_return_to_depot_minutes', $moment->estimated_return_to_depot_minutes ?? '') }}"
                   step="1"
                   min="0"
                   placeholder="Minutes">
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>Est. Return to Pickup (KM)</label>
            <input type="number"
                   name="estimated_return_to_pickup_km"
                   class="form-control"
                   value="{{ old('estimated_return_to_pickup_km', $moment->estimated_return_to_pickup_km ?? '') }}"
                   step="0.01"
                   min="0"
                   placeholder="KM">
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>Est. Return to Pickup (Minutes)</label>
            <input type="number"
                   name="estimated_return_to_pickup_minutes"
                   class="form-control"
                   value="{{ old('estimated_return_to_pickup_minutes', $moment->estimated_return_to_pickup_minutes ?? '') }}"
                   step="1"
                   min="0"
                   placeholder="Minutes">
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
                            <span class="input-group-text">Nrs</span>
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
                            <span class="input-group-text">Nrs</span>
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

        // Form validation for yes/no radio buttons
        $('form').on('submit', function (e) {
            let isValid = true;
            let errorMessage = 'Please answer all required questions.';

            $('.questionnaire-item').each(function() {
                let questionId = $(this).data('question-id');
                let isRequired = $(this).find('input[type="radio"]').first().prop('required');

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