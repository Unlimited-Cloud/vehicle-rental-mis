@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($vehicle_assign) ? 'Edit Vehicle Assignment' : 'Add Vehicle Assignment' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<form action="{{ isset($vehicle_assign) ? route('admin.vehicle_assign.update',$vehicle_assign->id) : route('admin.vehicle_assign.store') }}"
      method="POST">
@csrf
@if(isset($vehicle_assign)) @method('PUT') @endif

<div class="card-body">
@include('layouts.admin_theme.alert')

<div class="row">

<div class="col-md-6">
<div class="form-group">
<label>Date *</label>
<input type="date" name="date" class="form-control"
value="{{ old('date',$vehicle_assign->date ?? '') }}" required>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Vehicle *</label>
<select name="vehicle_id" class="form-control" required>
    <option value="">Select Vehicle</option>
    @foreach($vehicles as $v)
        <option value="{{ $v->id }}"
        {{ old('vehicle_id',$vehicle_assign->vehicle_id ?? '')==$v->id?'selected':'' }}>
            {{ $v->vehicle_name }}
        </option>
    @endforeach
</select>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Driver *</label>
<select name="driver_id" class="form-control" required>
    <option value="">Select Driver</option>
    @foreach($drivers as $d)
        <option value="{{ $d->id }}"
        @if(old('driver_id',$vehicle_assign->driver_id ?? '') == $h->id) disabled @endif>
            {{ $d->user->name ?? 'N/A' }}
        </option>
    @endforeach
</select>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Helper</label>
<select name="helper_id" class="form-control">
    <option value="">Select Helper</option>
    @foreach($helpers as $h)
        <option value="{{ $h->id }}"
        {{ old('helper_id',$vehicle_assign->helper_id ?? '')==$h->id?'selected':'' }}>
            {{ $h->user->name ?? 'N/A' }}
        </option>
    @endforeach
</select>
</div>
</div>

<div class="col-md-12">
<div class="form-group">
<label>Remarks</label>
<textarea name="remarks" class="form-control">{{ old('remarks',$vehicle_assign->remarks ?? '') }}</textarea>
</div>
</div>

</div>
</div>

<div class="card-footer text-right">
<button type="submit" class="btn btn-primary">
{{ isset($vehicle_assign) ? 'Update Assignment' : 'Add Assignment' }}
</button>
</div>

</form>
</div>
</div>
</section>
@endsection