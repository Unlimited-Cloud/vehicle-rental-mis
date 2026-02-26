{{-- resources/views/layouts/admin/customers/create.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($customer) ? 'Edit Customer' : 'Add Customer' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<form action="{{ isset($customer) ? route('admin.customers.update', $customer->id) : route('admin.customers.store') }}"
      method="POST">
@csrf
@if(isset($customer)) @method('PUT') @endif

<div class="card-body">
@include('layouts.admin_theme.alert')

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Company/Individual Name *</label>
            <input type="text" name="name" class="form-control" 
                   placeholder="Enter company or business name"
                   value="{{ old('name', $customer->name ?? '') }}" required>
            <small class="text-muted">This will be the primary name for billing</small>
        </div>
    </div>



    <div class="col-md-6">
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" class="form-control" 
                   placeholder="email@example.com"
                   value="{{ old('email', $customer->email ?? '') }}">
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Phone *</label>
            <input type="text" name="phone" class="form-control" 
                   placeholder="Contact number"
                   value="{{ old('phone', $customer->phone ?? '') }}" required>
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label>Address *</label>
            <textarea name="address" class="form-control" rows="2" 
                      placeholder="Full address">{{ old('address', $customer->address ?? '') }}</textarea>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>City</label>
            <input type="text" name="city" class="form-control" 
                   placeholder="City"
                   value="{{ old('city', $customer->city ?? '') }}">
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>State</label>
            <input type="text" name="state" class="form-control" 
                   placeholder="State"
                   value="{{ old('state', $customer->state ?? '') }}">
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>PAN Number</label>
            <input type="text" name="pan_number" class="form-control" 
                   placeholder="PAN card number"
                   value="{{ old('pan_number', $customer->pan_number ?? '') }}">
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>License Number</label>
            <input type="text" name="license_number" class="form-control" 
                   placeholder="Driving license number"
                   value="{{ old('license_number', $customer->license_number ?? '') }}">
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>License Expiry Date</label>
            <input type="date" name="license_expiry" class="form-control" 
                   value="{{ old('license_expiry', isset($customer) && $customer->license_expiry ? $customer->license_expiry->format('Y-m-d') : '') }}">
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Status *</label>
            <select name="status" class="form-control" required>
                <option value="active" {{ (old('status', $customer->status ?? '') == 'active') ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ (old('status', $customer->status ?? '') == 'inactive') ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
    </div>
</div>
</div>

<div class="card-footer text-right">
    <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back
    </a>

    <button type="submit" class="btn btn-primary">
        {{ isset($customer) ? 'Update Customer' : 'Add Customer' }}
    </button>
</div>

</form>
</div>
</div>
</section>
@endsection