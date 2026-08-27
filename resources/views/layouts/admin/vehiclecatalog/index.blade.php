@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>VehicleCatalog Lists</h1>
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
                        @if(auth()->user()->can('create_vehicles_vehicle_catalog'))
                         <div class="d-flex justify-content-between align-items-center mb-3">
                            <a href="{{ route('admin.vehiclecatalog.create') }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-plus"></i> Add New Vehicle Catalog
                            </a>
                            
                        </div>
                        @endif

                        <table id="dataTable" class="table table-bordered table-striped show-search-bar">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    {{-- <th>Image</th> --}}
                                    <th>Brand</th>
                                    <th>Model</th>
                                    <th>Seater</th>
                                    {{-- <th>Year</th> --}}
                                    {{-- <th>Rent/Day</th> --}}
                                    <th>Fuel</th>
                                    <th>Transmission</th>
                                    {{-- <th>Status</th> --}}
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tablebody">
                                @foreach($vehiclecatalogs as $vehiclecatalog)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    {{-- <td>
                                        @if($vehicle->image)
                                            <img src="{{ asset($vehicle->image) }}"
                                                 width="60"
                                                 height="40"
                                                 style="object-fit:cover;">
                                        @else
                                            N/A
                                        @endif
                                    </td> --}}
                                    <td>{{ $vehiclecatalog->brand }}</td>
                                    <td>{{ $vehiclecatalog->model }}</td>
                                    <td>{{ $vehiclecatalog->seater }}</td>
                                    {{-- <td>{{ $vehiclecatalog->year }}</td> --}}
                                    {{-- <td>Rs {{ number_format($vehiclecatalog->rent_price_per_day,2) }}</td> --}}
                                    <td>{{ $vehiclecatalog->fuel_type }}</td>
                                    <td>{{ $vehiclecatalog->transmission }}</td>

                                    {{-- <td>
                                        @if($vehiclecatalog->status)
                                            <span class="badge badge-success">Available</span>
                                        @else
                                            <span class="badge badge-danger">Not Available</span>
                                        @endif
                                    </td> --}}

                                    <td>
                                        @if(auth()->user()->can('update_vehicles_vehicle_catalog'))
                                        <a href="{{ route('admin.vehiclecatalog.edit', $vehiclecatalog->id) }}"
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif

                                        {{-- <!-- Add Vehicle Details -->
                                        <a href="{{ route('admin.vehicle_details.create', ['vehicle_id' => $vehicle->id]) }}"
                                        class="btn btn-info btn-sm" title="Add Vehicle Details">
                                            <i class="fas fa-plus-circle"></i>
                                        </a> --}}

                                        <!-- Show Vehicle Details -->
                                        @if(auth()->user()->can('read_vehicles_vehicle_catalog'))
                                            <a href="{{ route('admin.vehiclecatalog.show', $vehiclecatalog->id) }}"
                                            class="btn btn-success btn-sm" title="View Vehicle Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                         @endif
                                        {{-- @endif --}}
                                         @if(auth()->user()->can('delete_vehicles_vehicle_catalog'))       
                                        <form action="{{ route('admin.vehiclecatalog.destroy', $vehiclecatalog->id) }}"
                                            method="POST"
                                            style="display:inline-block;"
                                            onsubmit="return confirm('Are you sure you want to delete this vehicle?');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm bg-red">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
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