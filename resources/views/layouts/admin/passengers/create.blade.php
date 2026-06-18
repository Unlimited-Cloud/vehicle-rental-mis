@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($passenger) ? 'Edit Passenger' : 'Add Passenger' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<form action="{{ isset($passenger) ? route('admin.passengers.update', $passenger->id) : route('admin.passengers.store') }}"
      method="POST">
@csrf
@if(isset($passenger)) @method('PUT') @endif

<div class="card-body">
@include('layouts.admin_theme.alert')

<div class="row">

<div class="col-md-6">
<div class="form-group">
<label>Contact Person *</label>
<input type="text" name="contact_person" class="form-control"
value="{{ old('contact_person', $passenger->contact_person ?? '') }}" required>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Contact Email *</label>
<input type="email" name="contact_email" class="form-control"
value="{{ old('contact_email', $passenger->contact_email ?? '') }}" required>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Contact Number *</label>
<input type="text" name="contact_number" class="form-control"
value="{{ old('contact_number', $passenger->contact_number ?? '') }}" required>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Contact Address *</label>
<input type="text" name="contact_address" class="form-control"
value="{{ old('contact_address', $passenger->contact_address ?? '') }}" required>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Customer</label>
<input type="number" name="customer_id" class="form-control"
value="{{ old('customer_id', $passenger->customer_id ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Booking ID *</label>
<input type="number" name="booking_id" class="form-control"
value="{{ old('booking_id', $passenger->booking_id ?? '') }}" required>
</div>
</div>

</div>
</div>

<div class="card-footer text-right">
<button type="submit" class="btn btn-primary">
{{ isset($passenger) ? 'Update Passenger' : 'Add Passenger' }}
</button>
</div>

</form>
</div>
</div>
</section>
@endsection