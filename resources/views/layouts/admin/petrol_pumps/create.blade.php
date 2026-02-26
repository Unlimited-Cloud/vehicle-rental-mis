{{-- resources/views/layouts/admin/petrol_pumps/create.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($petrolPump) ? 'Edit Petrol Pump' : 'Add Petrol Pump' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<form action="{{ isset($petrolPump) ? route('admin.petrol_pumps.update', $petrolPump->id) : route('admin.petrol_pumps.store') }}"
      method="POST">
@csrf
@if(isset($petrolPump)) @method('PUT') @endif

<div class="card-body">
@include('layouts.admin_theme.alert')

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Pump Name *</label>
            <input type="text" name="name" class="form-control" 
                   placeholder="Enter petrol pump name"
                   value="{{ old('name', $petrolPump->name ?? '') }}" required>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Owner Name</label>
            <input type="text" name="owner_name" class="form-control" 
                   placeholder="Owner's full name"
                   value="{{ old('owner_name', $petrolPump->owner_name ?? '') }}">
        </div>
    </div>

    

    <div class="col-md-6">
        <div class="form-group">
            <label>Phone *</label>
            <input type="text" name="phone" class="form-control" 
                   placeholder="Contact number"
                   value="{{ old('phone', $petrolPump->phone ?? '') }}" required>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Alternate Phone</label>
            <input type="text" name="alternate_phone" class="form-control" 
                   placeholder="Alternate contact number"
                   value="{{ old('alternate_phone', $petrolPump->alternate_phone ?? '') }}">
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label>Address</label>
            <textarea name="address" class="form-control" rows="2" 
                      placeholder="Full address">{{ old('address', $petrolPump->address ?? '') }}</textarea>
        </div>
    </div>

    

    <div class="col-md-6">
        <div class="form-group">
            <label>PAN Number</label>
            <input type="text" name="pan_number" class="form-control" 
                   placeholder="PAN number"
                   value="{{ old('pan_number', $petrolPump->pan_number ?? '') }}">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Opening Balance</label>
            <input type="number" step="0.01" name="opening_balance" class="form-control" 
                   placeholder="0.00"
                   value="{{ old('opening_balance', $petrolPump->opening_balance ?? 0) }}">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Balance Type *</label>
            <select name="balance_type" class="form-control" required>
                <option value="payable" {{ (old('balance_type', $petrolPump->balance_type ?? '') == 'payable') ? 'selected' : '' }}>Payable (We owe them)</option>
                <option value="receivable" {{ (old('balance_type', $petrolPump->balance_type ?? '') == 'receivable') ? 'selected' : '' }}>Receivable (They owe us)</option>
            </select>
            <small class="text-muted">Select Payable if we have to pay the pump</small>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Credit Limit</label>
            <input type="number" step="0.01" name="credit_limit" class="form-control" 
                   placeholder="Credit limit"
                   value="{{ old('credit_limit', $petrolPump->credit_limit ?? 0) }}">
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Status *</label>
            <select name="status" class="form-control" required>
                <option value="active" {{ (old('status', $petrolPump->status ?? '') == 'active') ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ (old('status', $petrolPump->status ?? '') == 'inactive') ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label>Remarks</label>
            <textarea name="remarks" class="form-control" rows="3" 
                      placeholder="Any additional notes">{{ old('remarks', $petrolPump->remarks ?? '') }}</textarea>
        </div>
    </div>
</div>
</div>

<div class="card-footer text-right">
    <a href="{{ route('admin.petrol_pumps.index') }}" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back
    </a>

    <button type="submit" class="btn btn-primary">
        {{ isset($petrolPump) ? 'Update Petrol Pump' : 'Add Petrol Pump' }}
    </button>
</div>

</form>
</div>
</div>
</section>
@endsection