@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">

<div class="container-fluid d-flex justify-content-between">

<h1>Fuel Purchase Details</h1>

<a href="{{ route('admin.fuel_purchased.index') }}"
class="btn btn-secondary">

Back

</a>

</div>

</div>

<section class="content">
<div class="container-fluid">

<div class="card">

<div class="card-body">

<div class="row">

<div class="col-md-4">

<strong>Vehicle</strong>
<p>{{ $fuel->vehicle->vehicle_name ?? '-' }}</p>

</div>

<div class="col-md-4">

<strong>Driver</strong>
<p>{{ optional(optional($fuel->driver)->user)->name }}</p>

</div>

<div class="col-md-4">

<strong>Pump</strong>
<p>{{ $fuel->petrolPump->name ?? '-' }}</p>

</div>

</div>


<div class="row">

<div class="col-md-4">
<strong>Liters</strong>
<p>{{ $fuel->liters }}</p>
</div>

<div class="col-md-4">
<strong>Rate</strong>
<p>{{ $fuel->rate }}</p>
</div>

<div class="col-md-4">
<strong>Amount</strong>
<p>{{ $fuel->amount }}</p>
</div>

</div>

<hr>

<div class="row">

<div class="col-md-3">
<strong>Pump Before</strong>
<img src="{{ asset($fuel->pump_before) }}" class="img-fluid">
</div>

<div class="col-md-3">
<strong>Pump After</strong>
<img src="{{ asset($fuel->pump_after) }}" class="img-fluid">
</div>

<div class="col-md-3">
<strong>Tank Before</strong>
<img src="{{ asset($fuel->tank_before) }}" class="img-fluid">
</div>

<div class="col-md-3">
<strong>Tank After</strong>
<img src="{{ asset($fuel->tank_after) }}" class="img-fluid">
</div>

</div>

</div>

</div>

</div>
</section>

@endsection