@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($vehicle) ? 'Edit Vehicle' : 'Create Vehicle' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<form action="{{ isset($vehicle) ? route('admin.vehicles.update',$vehicle->id) : route('admin.vehicles.store') }}"
      method="POST" enctype="multipart/form-data">

@csrf
@if(isset($vehicle)) @method('PUT') @endif

<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="row">

<div class="col-md-6">
<div class="form-group">
<label>Vehicle Name *</label>
<input type="text" name="vehicle_name" class="form-control"
value="{{ old('vehicle_name',$vehicle->vehicle_name ?? '') }}" required>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Brand *</label>
<input type="text" name="brand" class="form-control"
value="{{ old('brand',$vehicle->brand ?? '') }}" required>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Model *</label>
<input type="text" name="model" class="form-control"
value="{{ old('model',$vehicle->model ?? '') }}" required>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Year *</label>
<input type="number" name="year" class="form-control"
value="{{ old('year',$vehicle->year ?? '') }}" required>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Rent Price Per Day *</label>
<input type="number" step="0.01" name="rent_price_per_day" class="form-control"
value="{{ old('rent_price_per_day',$vehicle->rent_price_per_day ?? '') }}" required>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Fuel Type *</label>
<select name="fuel_type" class="form-control">
<option value="Petrol" {{ old('fuel_type',$vehicle->fuel_type ?? '')=='Petrol'?'selected':'' }}>Petrol</option>
<option value="Diesel" {{ old('fuel_type',$vehicle->fuel_type ?? '')=='Diesel'?'selected':'' }}>Diesel</option>
<option value="Electric" {{ old('fuel_type',$vehicle->fuel_type ?? '')=='Electric'?'selected':'' }}>Electric</option>
</select>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Transmission *</label>
<select name="transmission" class="form-control">
<option value="Manual" {{ old('transmission',$vehicle->transmission ?? '')=='Manual'?'selected':'' }}>Manual</option>
<option value="Automatic" {{ old('transmission',$vehicle->transmission ?? '')=='Automatic'?'selected':'' }}>Automatic</option>
</select>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Status</label>
<select name="status" class="form-control">
<option value="1" {{ old('status',$vehicle->status ?? 1)==1?'selected':'' }}>Available</option>
<option value="0" {{ old('status',$vehicle->status ?? 1)==0?'selected':'' }}>Not Available</option>
</select>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Vehicle Image</label>
<input type="file" name="image" class="form-control">
@if(isset($vehicle) && $vehicle->image)
<br>
<img src="{{ asset('storage/'.$vehicle->image) }}" width="120">
@endif
</div>
</div>

</div>

</div>

<div class="card-footer text-right">
    <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back
    </a>

    <button type="submit" class="btn btn-primary">
        {{ isset($vehicle) ? 'Update Vehicle' : 'Create Vehicle' }}
    </button>
</div>

</form>
</div>
</div>
</section>

@endsection