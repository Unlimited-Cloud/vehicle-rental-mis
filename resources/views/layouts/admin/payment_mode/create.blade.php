@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($paymentMode) ? 'Edit Payment Mode' : 'Add Payment Mode' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<form action="{{ isset($paymentMode) ? route('admin.payment-mode.update',$paymentMode->id) : route('admin.payment-mode.store') }}"
      method="POST" enctype="multipart/form-data">

@csrf
@if(isset($paymentMode))
    @method('PUT')
@endif

<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="row">

<div class="col-md-6">
<div class="form-group">
<label>Name *</label>
<input type="text"
       name="name"
       class="form-control"
       value="{{ old('name', $paymentMode->name ?? '') }}"
       required>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Logo</label>
<input type="file" name="logo" class="form-control">
</div>

@if(isset($paymentMode) && $paymentMode->logo)
    <img src="{{ asset('uploads/payment_modes/'.$paymentMode->logo) }}"
         width="80">
@endif
</div>

<div class="col-md-6">
<div class="form-group">
<label>Status *</label>

<select name="status" class="form-control" required>
    <option value="1"
        {{ old('status', $paymentMode->status ?? '') == 1 ? 'selected' : '' }}>
        Active
    </option>

    <option value="0"
        {{ old('status', $paymentMode->status ?? '') == 0 ? 'selected' : '' }}>
        Inactive
    </option>
</select>

</div>
</div>

</div>
</div>

<div class="card-footer text-right">
<button type="submit" class="btn btn-primary">
    {{ isset($paymentMode) ? 'Update Payment Mode' : 'Add Payment Mode' }}
</button>
</div>

</form>
</div>
</div>
</section>
@endsection