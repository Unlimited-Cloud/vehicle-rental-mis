@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Vehicle Detail</h1>
            <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">

    <div class="card card-primary card-outline">
        <div class="card-body">

            <div class="row">

                <!-- Left Column: Vehicle Image + Basic Specs -->
                <div class="col-md-5 text-center border-right">
                    <h4>{{ $vehicle_detail->vehicle->vehicle_name ?? 'N/A' }}</h4>

                    @if($vehicle_detail->vehicle && $vehicle_detail->vehicle->image)
                        <img src="{{ asset($vehicle_detail->vehicle->image) }}"
                             alt="Vehicle Image"
                             class="img-fluid rounded mb-3"
                             style="max-height: 200px; object-fit: cover;">
                    @else
                        <p>No Image Available</p>
                    @endif

                    <table class="table table-borderless text-left mt-3">
                        <tbody>
                            <tr>
                                <th>Brand:</th>
                                <td>{{ $vehicle_detail->vehicle->brand ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Model:</th>
                                <td>{{ $vehicle_detail->vehicle->model ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Year:</th>
                                <td>{{ $vehicle_detail->vehicle->year ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Rent/Day:</th>
                                <td>Rs {{ number_format($vehicle_detail->vehicle->rent_price_per_day ?? 0,2) }}</td>
                            </tr>
                            <tr>
                                <th>Fuel:</th>
                                <td>{{ $vehicle_detail->vehicle->fuel_type ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Transmission:</th>
                                <td>{{ $vehicle_detail->vehicle->transmission ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>Is Helper Needed:</th>
                                <td>
                                    @if($vehicle_detail->vehicle && $vehicle_detail->vehicle->is_helper_needed)
                                        <span class="badge badge-success">Yes</span>
                                    @else
                                        <span class="badge badge-danger">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($vehicle_detail->vehicle && $vehicle_detail->vehicle->status)
                                        <span class="badge badge-success">Available</span>
                                    @else
                                        <span class="badge badge-danger">Not Available</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Right Column: Vehicle Details (Blue Book / Insurance / Permit) -->
                <div class="col-md-7">
                   <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5>Vehicle Documentation</h5>

                        @if($vehicle_detail)
                            <a href="{{ route('admin.vehicle_details.edit', $vehicle_detail->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> Edit Details
                            </a>
                        @endif
                    </div>
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Blue Book Number</th>
                                <td>{{ $vehicle_detail->blue_book_number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Insurance Number</th>
                                <td>{{ $vehicle_detail->insurance_number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Insurance Expiry</th>
                                <td>{{ $vehicle_detail->insurance_expiry ? \Carbon\Carbon::parse($vehicle_detail->insurance_expiry)->format('d-M-Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Permit Number</th>
                                <td>{{ $vehicle_detail->permit_number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Permit Expiry</th>
                                <td>{{ $vehicle_detail->permit_expiry ? \Carbon\Carbon::parse($vehicle_detail->permit_expiry)->format('d-M-Y') : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>

                   
                </div>

            </div>
        </div>
    </div>

</div>
</section>

@endsection