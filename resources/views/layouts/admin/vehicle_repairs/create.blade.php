@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>
            {{ isset($vehicleRepair) ? 'Edit Vehicle Repair' : 'Create Vehicle Repair' }}
        </h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

@include('layouts.admin_theme.alert')

<form action="{{ isset($vehicleRepair) 
        ? route('admin.vehicle-repairs.update',$vehicleRepair->id) 
        : route('admin.vehicle-repairs.store') }}"
      method="POST">

@csrf
@if(isset($vehicleRepair)) @method('PUT') @endif

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
            $vehicleRepair->vehicle_id ?? request('vehicle_id')
        ) == $vehicle->id ? 'selected' : '' 
    }}>
    {{ $vehicle->vehicle_name }} ({{ $vehicle->brand }} {{ $vehicle->model }})
</option>
@endforeach

</select>
</div>
</div>

<!-- Repair Date -->
<div class="col-md-6">
<div class="form-group">
<label>Repair Date *</label>
<input type="date" 
       name="repair_date" 
       class="form-control"
       value="{{ old('repair_date',$vehicleRepair->repair_date ?? '') }}"
       required>
</div>
</div>

<!-- Repair Vendor -->
<div class="col-md-6">
<div class="form-group">
<label>Repair Vendor</label>
<input type="text" 
       name="repair_vendor" 
       class="form-control"
       placeholder="Vendor / Workshop Name"
       value="{{ old('repair_vendor',$vehicleRepair->repair_vendor ?? '') }}">
</div>
</div>

<!-- Repair Amount -->
<div class="col-md-6">
<div class="form-group">
<label>Repair Amount</label>
<input type="number" 
       step="0.01"
       name="repair_amount" 
       class="form-control"
       value="{{ old('repair_amount',$vehicleRepair->repair_amount ?? '') }}">
</div>
</div>

<!-- Valid Till -->
<div class="col-md-6">
<div class="form-group">
<label>Repair Valid Till</label>
<input type="date" 
       name="repair_valid_till" 
       class="form-control"
       value="{{ old('repair_valid_till',$vehicleRepair->repair_valid_till ?? '') }}">
</div>
</div>

<!-- Repair Details -->
<div class="col-md-12">
<div class="form-group">
<label>Repair Details *</label>
<textarea name="repair_details" 
          class="form-control" 
          rows="3"
          placeholder="Describe repair work performed..."
          required>{{ old('repair_details',$vehicleRepair->repair_details ?? '') }}</textarea>
</div>
</div>

</div>
</div>

<div class="card-footer text-right">

<a href="{{ url()->previous() }}" class="btn btn-secondary">
Back
</a>

<button type="submit" class="btn btn-primary">
{{ isset($vehicleRepair) ? 'Update Repair' : 'Create Repair' }}
</button>

</div>

</div>

</form>

</div>
</section>

@endsection