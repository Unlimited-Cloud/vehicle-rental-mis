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
      method="POST"
      enctype="multipart/form-data">

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


<!-- Driver Selection -->
<div class="col-md-6">
<div class="form-group">
<label>Select Driver *</label>
<select name="driver_id" class="form-control" required>

<option value="">-- Select Driver --</option>

@foreach($drivers as $driver)
<option value="{{ $driver->id }}"
    {{ 
        old('driver_id', 
            $vehicleRepair->driver_id ?? request('driver_id')
        ) == $driver->id ? 'selected' : '' 
    }}>
    {{ $driver->user->name }}
</option>
@endforeach

</select>
</div>
</div>



<!-- vendor Selection -->
<div class="col-md-6">
<div class="form-group">
<label>Select Vendors *</label>
<select name="vendor_id" class="form-control" required>

<option value="">-- Select Vendors --</option>

@foreach($vendors as $vendor)
<option value="{{ $vendor->id }}"
    {{ 
        old('driver_id', 
            $vehicleRepair->vendor_id ?? request('vendor_id')
        ) == $vendor->id ? 'selected' : '' 
    }}>
    {{ $vendor->company_name }}
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

{{-- <!-- Valid Till -->
<div class="col-md-6">
<div class="form-group">
<label>Repair Valid Till</label>
<input type="date" 
       name="repair_valid_till" 
       class="form-control"
       value="{{ old('repair_valid_till',$vehicleRepair->repair_valid_till ?? '') }}">
</div>
</div> --}}

<!-- Bill Upload -->
<div class="col-md-6">
<div class="form-group">
<label>Bill (PDF/Image)</label>
<input type="file" name="bill" class="form-control">

@if(isset($vehicleRepair) && $vehicleRepair->bill)
<br>
<a href="{{asset($vehicleRepair->bill) }}" 
   target="_blank" 
   class="btn btn-sm btn-info">
   View Existing Bill
</a>
@endif

</div>
</div>


<div class="col-md-6">
<div class="form-group">
<label>Insurance Claim *</label>
<select name="claim_insurance" class="form-control" required>
    <option value="">-- Select  --</option>

    <option value="1"
        {{ old('claim_insurance', $vehicleRepair->claim_insurance ?? '') == '1' ? 'selected' : '' }}>
        Yes
    </option>

    <option value="0"
        {{ old('claim_insurance', $vehicleRepair->claim_insurance ?? '') == '0' ? 'selected' : '' }}>
       No
    </option>
</select>
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