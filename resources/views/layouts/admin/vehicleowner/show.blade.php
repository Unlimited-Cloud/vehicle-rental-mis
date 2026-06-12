{{-- resources/views/layouts/admin/customers/show.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Vehicle Owner Details: {{ $vehicleowner->name }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-body">
                @include('layouts.admin_theme.alert')

                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">Company/Individual Name</th>
                                <td>{{ $vehicleowner->name }}</td>
                            </tr>
                            <tr>
                                <th>Full Name</th>
                                <td>{{ $vehicleowner->full_name }}</td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td>{{ $vehicleowner->phone }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $vehicleowner->email ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Address</th>
                                <td>{{ $vehicleowner->address ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">City</th>
                                <td>{{ $vehicleowner->city ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>State</th>
                                <td>{{ $vehicleowner->state ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>PAN Number</th>
                                <td>{{ $vehicleowner->pan_number ?? 'N/A' }}</td>
                            </tr>
                            {{-- <tr>
                                <th>License Number</th>
                                <td>{{ $customer->license_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>License Expiry</th>
                                <td>{{ $customer->license_expiry ? $customer->license_expiry->format('d-m-Y') : 'N/A' }}</td>
                            </tr> --}}
                            <tr>
                                <th>Status</th>
                                <td>{!! $vehicleowner->status_badge !!}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row mt-4">
    <div class="col-md-12">
        <h4>Associated Vehicles</h4>

        @if($vehicleowner->vehicles->count())
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Vehicle Name</th>
                        <th>Brand</th>
                        <th>Seater</th>
                        <th>Fuel Type</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($vehicleowner->vehicles as $vehicle)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $vehicle->vehicle_name ?? 'N/A' }}</td>
                            <td>{{ $vehicle->brand ?? 'N/A' }}</td>
                            <td>{{ $vehicle->seater ?? 'N/A' }}</td>
                            <td>{{ $vehicle->fuel_type ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-warning">
                No vehicles found for this owner.
            </div>
        @endif
    </div>
</div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <h4>Additional Information</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">Created At</th>
                                <td>{{ $vehicleowner->created_at ? $vehicleowner->created_at->format('d-m-Y H:i:s') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Last Updated</th>
                                <td>{{ $vehicleowner->updated_at ? $vehicleowner->updated_at->format('d-m-Y H:i:s') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                

                <div class="text-right mt-3">
                    <a href="{{ route('admin.vehicleowner.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('admin.vehicleowner.edit', $vehicleowner->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Vehicle Owner
                    </a>
                </div>
            </div>
        </div>
        
    </div>
    
</div>


</div>
</section>
@endsection