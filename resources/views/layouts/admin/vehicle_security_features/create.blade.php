@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>
            {{ isset($feature) ? 'Edit Security Features' : 'Add Security Features' }}
        </h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<div class="card card-primary card-outline">

<form action="{{ isset($feature)
        ? route('admin.vehicle-security-features.update',$feature->id)
        : route('admin.vehicle-security-features.store') }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf
    @if(isset($feature))
        @method('PUT')
    @endif

<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="row">

<div class="col-md-6">
    <div class="form-group">
        <label>Vehicle *</label>
        <select name="vehicle_id" class="form-control" required>
            <option value="">Select Vehicle</option>

            @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}"
                    {{ old('vehicle_id',$feature->vehicle_id ?? '') == $vehicle->id ? 'selected' : '' }}>
                    {{ $vehicle->vehicle_name }}
                </option>
            @endforeach

        </select>
    </div>
</div>

</div>

<hr>

@php
$fields = [
    'dash_cam' => 'Dash Cam',
    'ebs' => 'EBS',
    'air_conditioning' => 'Air Conditioning',
    'reverse_camera' => 'Reverse Camera',
    'camera_360' => '360 Camera',
    'emergency_braking_system' => 'Emergency Braking System',
    'hillside_braking_system' => 'Hillside Braking System',
    'hill_descent_control' => 'Hill Descent Control',
];
@endphp

<div class="row">

@foreach($fields as $field => $label)

<div class="col-md-6 mb-4">

    <div class="card border">

        <div class="card-header">
            <strong>{{ $label }}</strong>
        </div>

        <div class="card-body">

            <div class="form-group">

                <label>Status</label>

                <select name="{{ $field }}" class="form-control">
                    <option value="1"
                        {{ old($field,$feature->$field ?? 0) == 1 ? 'selected' : '' }}>
                        Yes
                    </option>

                    <option value="0"
                        {{ old($field,$feature->$field ?? 0) == 0 ? 'selected' : '' }}>
                        No
                    </option>
                </select>

            </div>

            <div class="form-group">

                <label>Image</label>

                <input type="file"
                       name="{{ $field }}_image"
                       class="form-control">

            </div>

            @if(isset($feature) && !empty($feature->{$field.'_image'}))

                <img
                    src="{{ asset('uploads/vehicle-security-features/'.$feature->{$field.'_image'}) }}"
                    width="120"
                    class="mt-2">

            @endif

        </div>

    </div>

</div>

@endforeach

</div>

</div>

<div class="card-footer text-right">

    <button type="submit" class="btn btn-primary">

        {{ isset($feature)
            ? 'Update Security Features'
            : 'Add Security Features' }}

    </button>

</div>

</form>

</div>
</div>
</section>

@endsection