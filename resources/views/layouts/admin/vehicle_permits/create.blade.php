@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>
            {{ isset($vehiclePermit) ? 'Edit Vehicle Permit' : 'Create Vehicle Permit' }}
        </h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

@include('layouts.admin_theme.alert')

<form action="{{ isset($vehiclePermit) 
        ? route('admin.vehicle-permits.update',$vehiclePermit->id) 
        : route('admin.vehicle-permits.store') }}"
      method="POST" 
      enctype="multipart/form-data">

@csrf
@if(isset($vehiclePermit)) @method('PUT') @endif

<div class="card card-primary card-outline">

<div class="card-body">
<div class="row">

<!-- Vehicle Selection -->
<div class="col-md-6">
<div class="form-group">
<label>Select Vehicle *</label>
<select name="vehicle_id" class="form-control" required>

<option value="">-- Select Vehicle --</option>

@foreach($vehicles as $vehicle)
<option value="{{ $vehicle->id }}"
    {{ 
        old('vehicle_id', 
            $vehiclePermit->vehicle_id ?? request('vehicle_id')
        ) == $vehicle->id ? 'selected' : '' 
    }}>
    {{ $vehicle->vehicle_name }} ({{ $vehicle->brand }} {{ $vehicle->model }})
</option>
@endforeach

</select>
</div>
</div>

<!-- Permit Organization -->
<div class="col-md-6">
<div class="form-group">
<label>Permit From Organization *</label>
<input type="text" 
       name="permit_from_organization" 
       class="form-control"
       value="{{ old('permit_from_organization',$vehiclePermit->permit_from_organization ?? '') }}"
       required>
</div>
</div>

<!-- Expiry Date -->
<div class="col-md-6">
<div class="form-group">
<label>Permit Expiry Date *</label>
<input type="date" 
       name="permit_expiry_date" 
       class="form-control"
       value="{{ old('permit_expiry_date',$vehiclePermit->permit_expiry_date ?? '') }}"
       required>
</div>
</div>

<!-- Document Upload -->
<div class="col-md-6">
<div class="form-group">
<label>Permit Document (PDF/Image)</label>
<input type="file" name="permit_document" class="form-control">

@if(isset($vehiclePermit) && $vehiclePermit->permit_document)
<br>
<a href="{{ asset($vehiclePermit->permit_document) }}" 
   target="_blank" 
   class="btn btn-sm btn-info">
   View Existing Document
</a>
@endif

</div>
</div>

</div>
</div>

<div class="card-footer text-right">

<a href="{{ url()->previous() }}" class="btn btn-secondary">
Back
</a>

<button type="submit" class="btn btn-primary">
{{ isset($vehiclePermit) ? 'Update Permit' : 'Create Permit' }}
</button>

</div>

</div>

</form>

</div>
</section>

@endsection