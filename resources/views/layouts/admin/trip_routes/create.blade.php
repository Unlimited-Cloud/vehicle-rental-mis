@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
<div class="container-fluid">

<h1>
{{ isset($route) ? 'Edit Route' : 'Create Route' }}
</h1>

</div>
</div>

<section class="content">
<div class="container-fluid">

<form action="{{ isset($route) ? route('admin.trip-routes.update',$route->id) : route('admin.trip-routes.store') }}"
method="POST">

@csrf
@if(isset($route)) @method('PUT') @endif

@include('layouts.admin_theme.alert')

<div class="card">

<div class="card-body">

<div class="row">

<div class="col-md-6">

<label>Trip Category</label>

<select name="trip_category_id" class="form-control">

@foreach($categories as $id=>$name)

<option value="{{ $id }}"
{{ old('trip_category_id',$route->trip_category_id ?? '')==$id?'selected':'' }}>

{{ $name }}

</option>

@endforeach

</select>

</div>


<div class="col-md-6">

<label>Route Title</label>

<input type="text"
name="title"
class="form-control"
value="{{ old('title',$route->title ?? '') }}">

</div>


<div class="col-md-3 mt-3">

<label>KM</label>

<input type="number"
name="km"
class="form-control"
value="{{ old('km',$route->km ?? '') }}">

</div>


<div class="col-md-3 mt-3">

<label>Car Price</label>

<input type="number"
name="car_price"
class="form-control"
value="{{ old('car_price',$route->car_price ?? '') }}">

</div>


<div class="col-md-3 mt-3">

<label>Hiace Price</label>

<input type="number"
name="hiace_price"
class="form-control"
value="{{ old('hiace_price',$route->hiace_price ?? '') }}">

</div>


<div class="col-md-3 mt-3">

<label>Coaster Price</label>

<input type="number"
name="coaster_price"
class="form-control"
value="{{ old('coaster_price',$route->coaster_price ?? '') }}">

</div>


<div class="col-md-3 mt-3">

<label>Bus Price</label>

<input type="number"
name="bus_price"
class="form-control"
value="{{ old('bus_price',$route->bus_price ?? '') }}">

</div>

<div class="col-md-3 mt-3">

<label>Van Price</label>

<input type="number"
name="van_price"
class="form-control"
value="{{ old('van_price',$route->van_price ?? '') }}">

</div>




</div>

</div>

<div class="card-footer text-right">

<a href="{{ route('admin.trip-routes.index') }}"
class="btn btn-secondary">

Back

</a>

<button class="btn btn-primary">

{{ isset($route) ? 'Update Route' : 'Create Route' }}

</button>

</div>

</div>

</form>

</div>
</section>

@endsection