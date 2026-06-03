@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1>Vehicle Security Features</h1>

        <a href="{{ route('admin.vehicle-security-features.index') }}"
           class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<div class="row">

    <!-- Vehicle Information -->
    <div class="col-md-4">
        <div class="card card-primary card-outline">

            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-car"></i> Vehicle Information
                </h3>
            </div>

            <div class="card-body">

                <table class="table table-borderless">

                    <tr>
                        <th>Vehicle</th>
                        <td>
                            {{ $feature->vehicle->vehicle_name ?? 'N/A' }}
                        </td>
                    </tr>

                    <tr>
                        <th>Created</th>
                        <td>
                            {{ $feature->created_at?->format('d M Y h:i A') }}
                        </td>
                    </tr>

                    <tr>
                        <th>Updated</th>
                        <td>
                            {{ $feature->updated_at?->format('d M Y h:i A') }}
                        </td>
                    </tr>

                </table>

            </div>

        </div>
    </div>

    <!-- Summary -->
    <div class="col-md-8">
        <div class="card card-success card-outline">

            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shield-alt"></i> Features Summary
                </h3>
            </div>

            <div class="card-body">

                <div class="row">

                    @php
                    $featuresList = [
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

                    @foreach($featuresList as $field => $label)

                    <div class="col-md-6 mb-3">

                        <strong>{{ $label }}</strong>

                        <br>

                        @if($feature->$field)
                            <span class="badge bg-success">
                                Enabled
                            </span>
                        @else
                            <span class="badge bg-danger">
                                Disabled
                            </span>
                        @endif

                    </div>

                    @endforeach

                </div>

            </div>

        </div>
    </div>

</div>

<!-- Images Section -->

<div class="card card-info card-outline">

    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-images"></i> Feature Images
        </h3>
    </div>

    <div class="card-body">

        <div class="row">

            @foreach($featuresList as $field => $label)

                @php
                    $imageField = $field.'_image';
                @endphp

                <div class="col-md-3 mb-4">

                    <div class="card">

                        <div class="card-header text-center">
                            <strong>{{ $label }}</strong>
                        </div>

                        <div class="card-body text-center">

                            @if(!empty($feature->$imageField))

                                <img
                                    src="{{ asset('uploads/vehicle-security-features/'.$feature->$imageField) }}"
                                    class="img-fluid img-thumbnail"
                                    style="height:180px; object-fit:cover;">

                            @else

                                <div class="text-muted p-5">
                                    No Image
                                </div>

                            @endif

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    </div>

</div>

<div class="text-right mb-3">

    <a href="{{ route('admin.vehicle-security-features.edit',$feature->id) }}"
       class="btn btn-primary">

        <i class="fas fa-edit"></i>
        Edit Features

    </a>

</div>

</div>
</section>

@endsection