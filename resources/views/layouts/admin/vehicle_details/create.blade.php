@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($vehicle_detail) ? 'Edit Vehicle Detail' : 'Add Vehicle Detail' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<form action="{{ isset($vehicle_detail) ? route('admin.vehicle_details.update',$vehicle_detail->id) : route('admin.vehicle_details.store') }}"
      method="POST">
@csrf
@if(isset($vehicle_detail)) @method('PUT') @endif

<div class="card-body">
@include('layouts.admin_theme.alert')

<div class="row">
    

<div class="col-md-6">
    <input type="hidden" name="vehicle_id" value="{{ $vehicle_id ?? '' }}">
<div class="form-group">
    <label>Vehicle *</label>
    <select name="vehicle_id" class="form-control" required>
        <option value="">Select Vehicle</option>
        @foreach($vehicles as $vehicle)
            <option value="{{ $vehicle->id }}"
                {{ (old('vehicle_id', $vehicle_detail->vehicle_id ?? $vehicle_id ?? '') == $vehicle->id) ? 'selected' : '' }}>
                {{ $vehicle->vehicle_name }}
            </option>
        @endforeach
    </select>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Blue Book Number</label>
<input type="text" name="blue_book_number" class="form-control"
value="{{ old('blue_book_number',$vehicle_detail->blue_book_number ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Insurance Number</label>
<input type="text" name="insurance_number" class="form-control"
value="{{ old('insurance_number',$vehicle_detail->insurance_number ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Insurance Expiry</label>
<input type="date" name="insurance_expiry" class="form-control"
value="{{ old('insurance_expiry',$vehicle_detail->insurance_expiry ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Permit Number</label>
<input type="text" name="permit_number" class="form-control"
value="{{ old('permit_number',$vehicle_detail->permit_number ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Permit Expiry</label>
<input type="date" name="permit_expiry" class="form-control"
value="{{ old('permit_expiry',$vehicle_detail->permit_expiry ?? '') }}">
</div>
</div>

</div>
</div>

<div class="card-footer text-right">
    <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back
    </a>

    <button type="submit" class="btn btn-primary">
        {{ isset($vehicle_detail) ? 'Update Vehicle Detail' : 'Add Vehicle Detail' }}
    </button>
</div>

</form>
</div>
</div>
</section>
@endsection