@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>
            {{ isset($vehicleTyreChange) ? 'Edit Tyre Change' : 'Add Tyre Change' }}
        </h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

@include('layouts.admin_theme.alert')

<form action="{{ isset($vehicleTyreChange) 
        ? route('admin.vehicle-tyre-changes.update',$vehicleTyreChange->id) 
        : route('admin.vehicle-tyre-changes.store') }}"
      method="POST"
      enctype="multipart/form-data">

@csrf
@if(isset($vehicleTyreChange)) @method('PUT') @endif

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
            $vehicleTyreChange->vehicle_id ?? request('vehicle_id')
        ) == $vehicle->id ? 'selected' : '' 
    }}>
    {{ $vehicle->vehicle_name }} ({{ $vehicle->brand }} {{ $vehicle->model }})
</option>
@endforeach

</select>
</div>
</div>

<!-- Change Date -->
<div class="col-md-6">
<div class="form-group">
<label>Change Date *</label>
<input type="date" 
       name="change_date" 
       class="form-control"
       value="{{ old('change_date',$vehicleTyreChange->change_date ?? '') }}"
       required>
</div>
</div>

<!-- Tyre Position -->
<!-- Tyre Position -->
<div class="col-md-6">
<div class="form-group">
<label>Tyre Position *</label>
<select name="tyre_position" class="form-control" required>
    <option value="">-- Select Position --</option>

    <option value="FL"
        {{ old('tyre_position', $vehicleTyreChange->tyre_position ?? '') == 'FL' ? 'selected' : '' }}>
        Front Left
    </option>

    <option value="FR"
        {{ old('tyre_position', $vehicleTyreChange->tyre_position ?? '') == 'FR' ? 'selected' : '' }}>
        Front Right
    </option>

    <option value="BL"
        {{ old('tyre_position', $vehicleTyreChange->tyre_position ?? '') == 'BL' ? 'selected' : '' }}>
        Back Left
    </option>

    <option value="BR"
        {{ old('tyre_position', $vehicleTyreChange->tyre_position ?? '') == 'BR' ? 'selected' : '' }}>
        Back Right
    </option>

</select>
</div>
</div>

<!-- Tyre Manufacturer -->
<div class="col-md-6">
<div class="form-group">
<label>Tyre Manufacturer</label>
<input type="text" 
       name="tyre_manufacturer" 
       class="form-control"
       placeholder="MRF / Bridgestone / Michelin etc."
       value="{{ old('tyre_manufacturer',$vehicleTyreChange->tyre_manufacturer ?? '') }}">
</div>
</div>

<!-- Tyre Specifications -->
<div class="col-md-6">
<div class="form-group">
<label>Tyre Specifications</label>
<input type="text" 
       name="tyre_specifications" 
       class="form-control"
       placeholder="Example: 195/65 R15"
       value="{{ old('tyre_specifications',$vehicleTyreChange->tyre_specifications ?? '') }}">
</div>
</div>

<!-- Amount -->
<div class="col-md-6">
<div class="form-group">
<label>Amount</label>
<input type="number" 
       step="0.01"
       name="amount" 
       class="form-control"
       value="{{ old('amount',$vehicleTyreChange->amount ?? '') }}">
</div>
</div>

<!-- Invoice Upload -->
<div class="col-md-6">
<div class="form-group">
<label>Invoice Upload (PDF/Image)</label>
<input type="file" name="invoice_upload" class="form-control">

@if(isset($vehicleTyreChange) && $vehicleTyreChange->invoice_upload)
<br>
<a href="{{ asset($vehicleTyreChange->invoice_upload) }}" 
   target="_blank" 
   class="btn btn-sm btn-info">
   View Existing Invoice
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
{{ isset($vehicleTyreChange) ? 'Update Tyre Change' : 'Save Tyre Change' }}
</button>

</div>

</div>

</form>

</div>
</section>

@endsection