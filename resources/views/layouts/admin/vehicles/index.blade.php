@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Vehicle Lists</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline card-tabs">
                    <div class="card-body">

                        @include('layouts.admin_theme.alert')
                         <div class="d-flex justify-content-between align-items-center mb-3">
                            <a href="{{ route('admin.vehicles.create') }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-plus"></i> Add New Vehicle
                            </a>
                            
                        </div>

                        <table id="dataTable" class="table table-bordered table-striped show-search-bar">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>Image</th>
                                    <th>Vehicle Name</th>
                                    <th>Brand</th>
                                    <th>Model</th>
                                    <th>Year</th>
                                    <th>Rent/Day</th>
                                    <th>Fuel</th>
                                    <th>Transmission</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tablebody">
                                @foreach($vehicles as $vehicle)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        @if($vehicle->image)
                                            <img src="{{ asset($vehicle->image) }}"
                                                 width="60"
                                                 height="40"
                                                 style="object-fit:cover;">
                                        @else
                                            N/A
                                        @endif
                                    </td>

                                    <td>{{ $vehicle->vehicle_name }}</td>
                                    <td>{{ $vehicle->brand }}</td>
                                    <td>{{ $vehicle->model }}</td>
                                    <td>{{ $vehicle->year }}</td>
                                    <td>Rs {{ number_format($vehicle->rent_price_per_day,2) }}</td>
                                    <td>{{ $vehicle->fuel_type }}</td>
                                    <td>{{ $vehicle->transmission }}</td>

                                    <td>
                                        @if($vehicle->status)
                                            <span class="badge badge-success">Available</span>
                                        @else
                                            <span class="badge badge-danger">Not Available</span>
                                        @endif
                                    </td>

                                    <td>
                                        <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}"
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        {{-- <!-- Add Vehicle Details -->
                                        <a href="{{ route('admin.vehicle_details.create', ['vehicle_id' => $vehicle->id]) }}"
                                        class="btn btn-info btn-sm" title="Add Vehicle Details">
                                            <i class="fas fa-plus-circle"></i>
                                        </a> --}}

                                        <!-- Show Vehicle Details -->
                                        {{-- @if($vehicle->vehicleDetail) --}}
                                            <a href="{{ route('admin.vehicles.show', $vehicle->id) }}"
                                            class="btn btn-success btn-sm" title="View Vehicle Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        {{-- @endif --}}

                                        <form action="{{ route('admin.vehicles.destroy', $vehicle->id) }}"
                                            method="POST"
                                            style="display:inline-block;"
                                            onsubmit="return confirm('Are you sure you want to delete this vehicle?');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm bg-red">
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