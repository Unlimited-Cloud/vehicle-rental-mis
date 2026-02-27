@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>
            {{ isset($vehicleService) ? 'Edit Vehicle Service' : 'Create Vehicle Service' }}
        </h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

@include('layouts.admin_theme.alert')

<form action="{{ isset($vehicleService) 
        ? route('admin.vehicle-services.update',$vehicleService->id) 
        : route('admin.vehicle-services.store') }}"
      method="POST" 
      enctype="multipart/form-data">

@csrf
@if(isset($vehicleService)) @method('PUT') @endif

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
            $vehicleService->vehicle_id ?? request('vehicle_id')
        ) == $vehicle->id ? 'selected' : '' 
    }}>
    {{ $vehicle->vehicle_name }} ({{ $vehicle->brand }} {{ $vehicle->model }})
</option>
@endforeach

</select>
</div>
</div>

<!-- Service Date -->
<div class="col-md-6">
<div class="form-group">
<label>Service Date *</label>
<input type="date" 
       name="service_date" 
       class="form-control"
       value="{{ old('service_date',$vehicleService->service_date ?? '') }}"
       required>
</div>
</div>

<!-- Service Done At -->
<div class="col-md-6">
<div class="form-group">
<label>Service Done At *</label>
<input type="text" 
       name="service_done_at" 
       class="form-control"
       placeholder="Workshop / Service Center Name"
       value="{{ old('service_done_at',$vehicleService->service_done_at ?? '') }}"
       required>
</div>
</div>

<!-- Service Amount -->
<div class="col-md-6">
<div class="form-group">
<label>Service Amount</label>
<input type="number" 
       step="0.01"
       name="service_amount" 
       class="form-control"
       value="{{ old('service_amount',$vehicleService->service_amount ?? '') }}">
</div>
</div>

<!-- Next Service KM -->
<div class="col-md-6">
<div class="form-group">
<label>Next Service KM</label>
<input type="number" 
       name="next_service_km" 
       class="form-control"
       value="{{ old('next_service_km',$vehicleService->next_service_km ?? '') }}">
</div>
</div>

<!-- Next Service Date -->
<div class="col-md-6">
<div class="form-group">
<label>Next Service Date</label>
<input type="date" 
       name="next_service_date" 
       class="form-control"
       value="{{ old('next_service_date',$vehicleService->next_service_date ?? '') }}">
</div>
</div>

<!-- Service Details -->
<div class="col-md-12">
<div class="form-group">
<label>Service Details</label>
<textarea name="service_details" 
          class="form-control" 
          rows="3"
          placeholder="Describe service work performed...">{{ old('service_details',$vehicleService->service_details ?? '') }}</textarea>
</div>
</div>

<!-- Bill Upload -->
<div class="col-md-6">
<div class="form-group">
<label>Service Bill Copy (PDF/Image)</label>
<input type="file" name="service_bill_copy" class="form-control">

@if(isset($vehicleService) && $vehicleService->service_bill_copy)
<br>
<a href="{{ asset($vehicleService->service_bill_copy) }}" 
   target="_blank" 
   class="btn btn-sm btn-info">
   View Existing Bill
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
{{ isset($vehicleService) ? 'Update Service' : 'Create Service' }}
</button>

</div>

</div>

</form>

</div>
</section>

@endsection