@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>Assign Vehicle</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    @include('layouts.admin_theme.alert')

    <div class="card card-primary card-outline">
        <form action="{{ isset($vehicle_assignment) ? route('admin.vehicle_assignments.update',$vehicle_assignment->id) : route('admin.vehicle_assignments.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Vehicle *</label>
                           <select name="vehicle_id" class="form-control" required>
                                <option value="">Select Vehicle</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" {{ (isset($vehicle_assignment) && $vehicle_assignment->vehicle_id == $vehicle->id) ? 'selected' : '' }}>
                                        {{ $vehicle->vehicle_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Driver *</label>
                            <select name="driver_id" class="form-control" required>
                                <option value="">Select Driver</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Helper</label>
                            <select name="helper_id" class="form-control">
                                <option value="">Select Helper</option>
                                @foreach($helpers as $helper)
                                    <option value="{{ $helper->id }}">{{ $helper->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Start Date *</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Shift</label>
                            <input type="text" name="shift" class="form-control" placeholder="e.g. Morning/Evening">
                        </div>
                    </div>

                </div>
            </div>

           <div class="card-footer text-right">
                <a href="{{ route('admin.vehicle_assignments.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back
                </a>

                <button type="submit" class="btn btn-primary">
                    {{ isset($vehicle_assignment) ? 'Update Vehicle Assignment' : 'Add Vehicle Assignment' }}
                </button>
            </div>
        </form>
    </div>
</div>
</section>

@endsection