@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($agent) ? 'Edit Agent' : 'Add Agent' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<div class="card card-primary card-outline">

<form action="{{ isset($agent) ? route('admin.agents.update',$agent->id) : route('admin.agents.store') }}"
      method="POST"
      enctype="multipart/form-data">

@csrf

@if(isset($agent))
    @method('PUT')
@endif

<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="row">

{{-- USER INFO --}}

<div class="col-md-6">
<div class="form-group">
<label>Agent Name *</label>

<input type="text"
       name="agent_name"
       class="form-control"
       value="{{ old('agent_name',$agent->agent_name ?? '') }}"
       required>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Agent Email</label>

<input type="email"
       name="agent_email"
       class="form-control"
       value="{{ old('agent_email',$agent->agent_email ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Profile Image</label>

<input type="file"
       name="img"
       class="form-control">
</div>

@if(isset($agent) && $agent->user_img)
    <img src="{{ asset('uploads/users/'.$agent->user_img) }}"
         width="80"
         class="mt-2">
@endif
</div>

{{-- AGENT INFO --}}

<div class="col-md-6">
<div class="form-group">
<label>Role *</label>

<select name="role" class="form-control" required>
    <option value="">Select Role</option>

    <option value="agent"
        {{ old('role', $agent->role ?? 'agent') == 'agent' ? 'selected' : '' }}>
        Agent
    </option>

    <option value="sub-agent"
        {{ old('role', $agent->role ?? 'agent') == 'sub-agent' ? 'selected' : '' }}>
        Sub Agent
    </option>
</select>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Contact Number</label>

<input type="text"
       name="contact_number"
       class="form-control"
       value="{{ old('contact_number',$agent->contact_number ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Citizenship Document</label>

<input type="file"
       name="citizenship_doc"
       class="form-control">
</div>

@if(isset($agent) && $agent->citizenship_doc)
    <a href="{{ asset($agent->citizenship_doc) }}"
       target="_blank"
       class="btn btn-sm btn-info mt-2">

       View Document
    </a>
@endif
</div>

<div class="col-md-12">
<div class="form-group">
<label>Address</label>

<textarea name="address"
          class="form-control"
          rows="3">{{ old('address',$agent->address ?? '') }}</textarea>
</div>
</div>

{{-- BANK INFO --}}

<div class="col-md-12">
<hr>
<h5>Bank Information</h5>
</div>

<div class="col-md-4">
<div class="form-group">
<label>Bank Name</label>

<input type="text"
       name="bank_name"
       class="form-control"
       value="{{ old('bank_name',$agent->bank_name ?? '') }}">
</div>
</div>

<div class="col-md-4">
<div class="form-group">
<label>Account Name</label>

<input type="text"
       name="bank_account_name"
       class="form-control"
       value="{{ old('bank_account_name',$agent->bank_account_name ?? '') }}">
</div>
</div>

<div class="col-md-4">
<div class="form-group">
<label>Account Number</label>

<input type="text"
       name="bank_account_number"
       class="form-control"
       value="{{ old('bank_account_number',$agent->bank_account_number ?? '') }}">
</div>
</div>

{{-- WALLET INFO --}}

<div class="col-md-12">
<hr>
<h5>Wallet Information</h5>
</div>

<div class="col-md-6">
    <div class="form-group">
        <label>Wallet Name</label>

        <select name="wallet_name" class="form-control">
            <option value="">Select Wallet</option>
            <option value="eSewa"
                {{ old('wallet_name', $agent->wallet_name ?? '') == 'eSewa' ? 'selected' : '' }}>
                eSewa
            </option>
            <option value="Khalti"
                {{ old('wallet_name', $agent->wallet_name ?? '') == 'Khalti' ? 'selected' : '' }}>
                Khalti
            </option>
        </select>
    </div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Wallet Number</label>

<input type="text"
       name="wallet_number"
       class="form-control"
       value="{{ old('wallet_number',$agent->wallet_number ?? '') }}">
</div>
</div>

{{-- OTHER INFO --}}

<div class="col-md-6">
<div class="form-group">
<label>Commission Rate (%)</label>

<input type="number"
       step="0.01"
       name="commission_rate"
       class="form-control"
       value="{{ old('commission_rate',$agent->commission_rate ?? 0) }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Status</label>

<select name="status" class="form-control">

    <option value="1"
        {{ old('status',$agent->status ?? 1) == 1 ? 'selected' : '' }}>
        Active
    </option>

    <option value="0"
        {{ old('status',$agent->status ?? 1) == 0 ? 'selected' : '' }}>
        Inactive
    </option>

</select>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Verification</label>

<select name="is_verified" class="form-control">

    <option value="1"
        {{ old('is_verified',$agent->is_verified ?? 0) == 1 ? 'selected' : '' }}>
        Verified
    </option>

    <option value="0"
        {{ old('is_verified',$agent->is_verified ?? 0) == 0 ? 'selected' : '' }}>
        Not Verified
    </option>

</select>
</div>
</div>

<div class="col-md-12">
<div class="form-group">
<label>Remarks</label>

<textarea name="remarks"
          class="form-control"
          rows="3">{{ old('remarks',$agent->remarks ?? '') }}</textarea>
</div>
</div>

</div>
</div>

<div class="card-footer text-right">
<button type="submit" class="btn btn-primary">

{{ isset($agent) ? 'Update Agent' : 'Add Agent' }}

</button>
</div>

</form>

</div>
</div>
</section>

@endsection