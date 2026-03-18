@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($vendor) ? 'Edit Repair Shop' : 'Add Repair Shop' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<form action="{{ isset($vendor) ? route('admin.vendors.update', $vendor->id) : route('admin.vendors.store') }}"
      method="POST">
@csrf
@if(isset($vendor)) @method('PUT') @endif

<div class="card-body">
@include('layouts.admin_theme.alert')

<div class="row">

    <div class="col-md-12">
        <div class="form-group">
            <label>Company Name *</label>
            <input type="text" name="company_name" class="form-control"
                   value="{{ old('company_name', $vendor->company_name ?? '') }}" required>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Contact Person</label>
            <input type="text" name="name" class="form-control"
                   value="{{ old('name', $vendor->name ?? '') }}">
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="contact" class="form-control"
                   value="{{ old('contact', $vendor->contact ?? '') }}">
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control"
                   value="{{ old('email', $vendor->email ?? '') }}">
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Address</label>
            <input type="text" name="address" class="form-control"
                   value="{{ old('address', $vendor->address ?? '') }}">
        </div>
    </div>

   

</div>
</div>

<div class="card-footer text-right">
    <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back
    </a>

    <button type="submit" class="btn btn-primary">
        {{ isset($vendor) ? 'Update Vendor' : 'Add Vendor' }}
    </button>
</div>

</form>
</div>
</div>
</section>
@endsection