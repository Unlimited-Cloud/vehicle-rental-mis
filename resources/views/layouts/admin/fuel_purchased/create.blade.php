@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
<div class="container-fluid">

<h1>
{{ isset($fuel) ? 'Edit Fuel Purchase' : 'Add Fuel Purchase' }}
</h1>

</div>
</div>

<section class="content">
<div class="container-fluid">

<div class="card card-primary card-outline">

<div class="card-header">
<h3 class="card-title">Fuel Purchase Details</h3>
</div>
<form method="POST"
    action="{{ isset($fuel_purchased)
        ? route('admin.fuel_purchased.update', $fuel_purchased->id)
        : route('admin.fuel_purchased.store') }}"
    enctype="multipart/form-data">

            @csrf
            @if(isset($fuel_purchased))
                @method('PUT')
            @endif



<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="row">

<div class="col-md-4">
<div class="form-group">
<label>Date & Time</label>

<input type="datetime-local"
name="date_time"
class="form-control"
value="{{ old('date_time',$fuel_purchased->date_time ?? '') }}"
required>

</div>
</div>

<div class="col-md-4">
<div class="form-group">

<label>Vehicle</label>

<select name="vehicle_id" class="form-control select2">

<option value="">Select Vehicle</option>

@foreach($vehicles as $vehicle)

<option value="{{ $vehicle->id }}"
{{ (old('vehicle_id',$fuel_purchased->vehicle_id ?? '') == $vehicle->id) ? 'selected':'' }}>

{{ $vehicle->vehicle_name }}

</option>

@endforeach

</select>

</div>
</div>

<div class="col-md-4">
<div class="form-group">

<label>Driver</label>

<select name="driver_id" class="form-control select2">

<option value="">Select Driver</option>

@foreach($drivers as $id=>$name)

<option value="{{ $id }}"
{{ (old('driver_id',$fuel_purchased->driver_id ?? '') == $id) ? 'selected':'' }}>

{{ $name }}

</option>

@endforeach

</select>

</div>
</div>

</div>


<div class="row">

<div class="col-md-4">
<div class="form-group">

<label>Petrol Pump</label>

<select name="petrol_pump_id" class="form-control select2">

<option value="">Select Pump</option>

@foreach($pumps as $pump)

<option value="{{ $pump->id }}"
{{ (old('petrol_pump_id',$fuel_purchased->petrol_pump_id ?? '') == $pump->id) ? 'selected':'' }}>

{{ $pump->name }}

</option>

@endforeach

</select>

</div>
</div>

<div class="col-md-4">

<div class="form-group">

<label>Liters</label>

<input type="number"
step="0.01"
name="liters"
id="liters"
class="form-control"
value="{{ old('liters',$fuel_purchased->liters ?? '') }}">

</div>

</div>

<div class="col-md-4">

<div class="form-group">

<label>Rate</label>

<input type="number"
step="0.01"
name="rate"
id="rate"
class="form-control"
value="{{ old('rate',$fuel_purchased->rate ?? '') }}">

</div>

</div>

</div>


<div class="row">

<div class="col-md-4">
<div class="form-group">

<label>Amount</label>

<input type="number"
step="0.01"
name="amount"
id="amount"
readonly
class="form-control"
value="{{ old('amount',$fuel_purchased->amount ?? '') }}">

</div>
</div>

</div>


<hr>

<h4>Images</h4>

<div class="row">

<div class="col-md-3">

<label>Pump Before</label>

<input type="file" name="pump_before" class="form-control">

@if(isset($fuel_purchased) && $fuel_purchased->pump_before)
<img src="{{ asset($fuel_purchased->pump_before) }}" width="100">
@endif

</div>


<div class="col-md-3">

<label>Pump After</label>

<input type="file" name="pump_after" class="form-control">

@if(isset($fuel_purchased) && $fuel_purchased->pump_after)
<img src="{{ asset($fuel_purchased->pump_after) }}" width="100">
@endif

</div>


<div class="col-md-3">

<label>Tank Before</label>

<input type="file" name="tank_before" class="form-control">

@if(isset($fuel_purchased) && $fuel_purchased->tank_before)
<img src="{{ asset($fuel_purchased->tank_before) }}" width="100">
@endif

</div>


<div class="col-md-3">

<label>Tank After</label>

<input type="file" name="tank_after" class="form-control">

@if(isset($fuel_purchased) && $fuel_purchased->tank_after)
<img src="{{ asset($fuel_purchased->tank_after) }}" width="100">
@endif

</div>

</div>

</div>

<div class="card-footer text-right">

<a href="{{ route('admin.fuel_purchased.index') }}"
class="btn btn-secondary">

Back

</a>

<button class="btn btn-primary">

{{ isset($fuel_purchased) ? 'Update' : 'Save' }}

</button>

</div>

</form>

</div>
</div>
</section>

@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    function calculateAmount() {
        let liters = parseFloat(document.getElementById('liters').value) || 0;
        let rate = parseFloat(document.getElementById('rate').value) || 0;
        let amount = liters * rate;
        document.getElementById('amount').value = amount.toFixed(2);
    }

    document.getElementById('liters').addEventListener('input', calculateAmount);
    document.getElementById('rate').addEventListener('input', calculateAmount);
});
</script>