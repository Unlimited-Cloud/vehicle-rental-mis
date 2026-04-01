@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1 class="mb-3">
            {{ isset($vehicle) ? 'Edit Vehicle' : 'Create Vehicle' }}
        </h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<form action="{{ isset($vehicle) ? route('admin.vehicles.update',$vehicle->id) : route('admin.vehicles.store') }}"
      method="POST" enctype="multipart/form-data">

@csrf
@if(isset($vehicle)) @method('PUT') @endif

@include('layouts.admin_theme.alert')

<!-- ================= BASIC INFORMATION ================= -->
<div class="card card-primary card-outline mb-4">
    <div class="card-header bg-primary">
        <h3 class="card-title text-white">
            <i class="fas fa-car"></i> Basic Vehicle Information
        </h3>
    </div>

    <div class="card-body">
        <div class="row">

            <div class="col-md-6">
                <div class="form-group">
                    <label>Vehicle Name *</label>
                    <input type="text" name="vehicle_name" class="form-control"
                           value="{{ old('vehicle_name',$vehicle->vehicle_name ?? '') }}" required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Vehicle Type *</label>
                    <select name="vehicle_type" class="form-control">
                        <option value="car" {{ old('vehicle_type',$vehicle->vehicle_type ?? '')=='car'?'selected':'' }}>Car</option>
                        <option value="hiace" {{ old('vehicle_type',$vehicle->vehicle_type ?? '')=='hiace'?'selected':'' }}>Hiace</option>
                        <option value="coaster" {{ old('vehicle_type',$vehicle->vehicle_type ?? '')=='coaster'?'selected':'' }}>Coaster</option>
                        <option value="bus" {{ old('vehicle_type',$vehicle->vehicle_type ?? '')=='bus'?'selected':'' }}>Bus</option>
                        <option value="other" {{ old('vehicle_type',$vehicle->vehicle_type ?? '')=='other'?'selected':'' }}>Other</option>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Brand *</label>
                    <input type="text" name="brand" class="form-control"
                           value="{{ old('brand',$vehicle->brand ?? '') }}" required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Model *</label>
                    <input type="text" name="model" class="form-control"
                           value="{{ old('model',$vehicle->model ?? '') }}" required>
                </div>
            </div>

             <div class="col-md-6">
                <div class="form-group">
                    <label>Seater</label>
                    <input type="number" name="seater" class="form-control"
                           value="{{ old('seater',$vehicle->seater ?? '') }}" required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Year *</label>
                    <input type="number" name="year" class="form-control"
                           value="{{ old('year',$vehicle->year ?? '') }}" required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Rent Price Per Day *</label>
                    <input type="number" step="0.01" name="rent_price_per_day" class="form-control"
                           value="{{ old('rent_price_per_day',$vehicle->rent_price_per_day ?? '') }}" required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Fuel Type *</label>
                    <select name="fuel_type" class="form-control">
                        <option value="Petrol" {{ old('fuel_type',$vehicle->fuel_type ?? '')=='Petrol'?'selected':'' }}>Petrol</option>
                        <option value="Diesel" {{ old('fuel_type',$vehicle->fuel_type ?? '')=='Diesel'?'selected':'' }}>Diesel</option>
                        <option value="Electric" {{ old('fuel_type',$vehicle->fuel_type ?? '')=='Electric'?'selected':'' }}>Electric</option>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Transmission *</label>
                    <select name="transmission" class="form-control">
                        <option value="Manual" {{ old('transmission',$vehicle->transmission ?? '')=='Manual'?'selected':'' }}>Manual</option>
                        <option value="Automatic" {{ old('transmission',$vehicle->transmission ?? '')=='Automatic'?'selected':'' }}>Automatic</option>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Helper Required</label>
                    <select name="is_helper_needed" class="form-control">
                        <option value="1" {{ old('is_helper_needed',$vehicle->is_helper_needed ?? 1)==1?'selected':'' }}>Yes</option>
                        <option value="0" {{ old('is_helper_needed',$vehicle->is_helper_needed ?? 1)==0?'selected':'' }}>No</option>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="1" {{ old('status',$vehicle->status ?? 1)==1?'selected':'' }}>Available</option>
                        <option value="0" {{ old('status',$vehicle->status ?? 1)==0?'selected':'' }}>Not Available</option>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Vehicle Image</label>
                    <input type="file" name="image" class="form-control">
                    @if(isset($vehicle) && $vehicle->image)
                        <br>
                        <img src="{{ asset($vehicle->image) }}" width="120" class="img-thumbnail">
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ================= REGISTRATION ================= -->
<div class="card card-info card-outline mb-4">
    <div class="card-header bg-info">
        <h3 class="card-title text-white">
            <i class="fas fa-id-card"></i> Registration Details
        </h3>
    </div>

    <div class="card-body">
        <div class="row">

            <div class="col-md-6">
                <label>Registration Number</label>
                <input type="text" name="registration_number" class="form-control"
                       value="{{ old('registration_number',$vehicle->registration_number ?? '') }}">
            </div>

            <div class="col-md-6">
                <label>Registered At</label>
                <input type="text" name="registered_at" class="form-control"
                       value="{{ old('registered_at',$vehicle->registered_at ?? '') }}">
            </div>

            <div class="col-md-6 mt-3">
                <label>Number Plate Color</label>
                <select name="number_plate_color" class="form-control">
                    <option value="">Select</option>
                    <option value="RED" {{ old('number_plate_color',$vehicle->number_plate_color ?? '')=='RED'?'selected':'' }}>RED</option>
                    <option value="BLACK" {{ old('number_plate_color',$vehicle->number_plate_color ?? '')=='BLACK'?'selected':'' }}>BLACK</option>
                    <option value="GREEN" {{ old('number_plate_color',$vehicle->number_plate_color ?? '')=='GREEN'?'selected':'' }}>GREEN</option>
                </select>
            </div>

            <div class="col-md-6 mt-3">
                <label>Registration Expiry</label>
                <input type="date" name="registration_expiry" class="form-control"
                       value="{{ old('registration_expiry',$vehicle->registration_expiry ?? '') }}">
            </div>

            <div class="col-md-6 mt-3">
                <label>Bill Book Number</label>
                <input type="text" name="bill_book_number" class="form-control"
                    value="{{ old('bill_book_number',$vehicle->bill_book_number ?? '') }}">
            </div>

            <div class="col-md-6 mt-3">
                <label>Bill Book Image</label>
                <input type="file" name="bill_book_image" class="form-control">

                @if(isset($vehicle) && $vehicle->bill_book_image)
                    <br>
                    <img src="{{ asset($vehicle->bill_book_image) }}" 
                        width="120" class="img-thumbnail">
                @endif
            </div>

        </div>
    </div>
</div>

<!-- ================= INSURANCE ================= -->
<div class="card card-success card-outline mb-4">
    <div class="card-header bg-success">
        <h3 class="card-title text-white">
            <i class="fas fa-shield-alt"></i> Insurance Details
        </h3>
    </div>

    <div class="card-body">
        <div class="row">

            <div class="col-md-6">
                <label>Insurance Policy No</label>
                <input type="text" name="insurance_policy_no" class="form-control"
                       value="{{ old('insurance_policy_no',$vehicle->insurance_policy_no ?? '') }}">
            </div>

            <div class="col-md-6">
                <label>Insurance Company</label>
                <input type="text" name="insurance_company" class="form-control"
                       value="{{ old('insurance_company',$vehicle->insurance_company ?? '') }}">
            </div>

            <div class="col-md-6 mt-3">
                <label>Insurance Type</label>
                <input type="text" name="insurance_type" class="form-control"
                       value="{{ old('insurance_type',$vehicle->insurance_type ?? '') }}">
            </div>

            <div class="col-md-6 mt-3">
                <label>Insurance Valid Till</label>
                <input type="date" name="insurance_till" class="form-control"
                       value="{{ old('insurance_till',$vehicle->insurance_till ?? '') }}">
            </div>

            <div class="col-md-6 mt-3">
                <label>Insurance Cost Per Annum</label>
                <input type="number" step="0.01" name="insurance_cost_per_annum" class="form-control"
                    value="{{ old('insurance_cost_per_annum',$vehicle->insurance_cost_per_annum ?? '') }}">
            </div>

            <div class="col-md-6 mt-3">
                <label>Insurance Policy Document (PDF/Image)</label>
                <input type="file" name="insurance_policy_document" class="form-control">

                @if(isset($vehicle) && $vehicle->insurance_policy_document)
                    <br>
                    <a href="{{ asset($vehicle->insurance_policy_document) }}" 
                    target="_blank" class="btn btn-sm btn-info">
                    View Document
                    </a>
                @endif
            </div>

        </div>
    </div>
</div>

<!-- ================= SUBMIT ================= -->
<div class="card">
    <div class="card-footer text-right">
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">
            Back
        </a>

        <button type="submit" class="btn btn-primary">
            {{ isset($vehicle) ? 'Update Vehicle' : 'Create Vehicle' }}
        </button>
    </div>
</div>

</form>

</div>
</section>

@endsection