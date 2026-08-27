@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1 class="mb-3">
            {{ isset($vehiclecatalog) ? 'Edit Vehicle Catalog' : 'Create Vehicle Catalog' }}
        </h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<form action="{{ isset($vehiclecatalog) ? route('admin.vehiclecatalog.update',$vehiclecatalog->id) : route('admin.vehiclecatalog.store') }}"
      method="POST" enctype="multipart/form-data">

@csrf
@if(isset($vehiclecatalog)) @method('PUT') @endif

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
                    <label>Brand *</label>
                    <select name="brand" class="form-control" required>
                        <option value="">Select Brand</option>

                        @foreach($brands as $b)
                            <option value="{{ $b->name }}"
                                {{ old('brand', $vehiclecatalog->brand ?? '') == $b->name ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach

                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Seater *</label>
                    <select name="seater" class="form-control" required>
                        <option value="">Select Seater</option>

                        @foreach($seaters as $b)
                            <option value="{{ $b->name }}"
                                {{ old('seater', $vehiclecatalog->seater ?? '') == $b->name ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach

                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Fuel Type *</label>
                    <select name="fuel_type" class="form-control" required>
                        <option value="">Select Fuel Type</option>

                        @foreach($fuel_type as $ft)
                            <option value="{{ $ft->name }}"
                                {{ old('fuel_type', $vehiclecatalog->fuel_type ?? '') == $ft->name ? 'selected' : '' }}>
                                {{ $ft->name }}
                            </option>
                        @endforeach

                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Model *</label>
                    <input type="text" name="model" id="model" class="form-control"
                           value="{{ old('model',$vehicle->model ?? '') }}" required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Transmission *</label>
                    <select name="transmission" class="form-control">
                        <option value="Manual" {{ old('transmission',$vehiclecatalog->transmission ?? '')=='Manual'?'selected':'' }}>Manual</option>
                        <option value="Automatic" {{ old('transmission',$vehiclecatalog->transmission ?? '')=='Automatic'?'selected':'' }}>Automatic</option>
                    </select>
                </div>
            </div>

            {{-- <div class="col-md-6">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="1" {{ old('status',$vehiclecatalog->status ?? 1)==1?'selected':'' }}>Available</option>
                        <option value="0" {{ old('status',$vehiclecatalog->status ?? 1)==0?'selected':'' }}>Not Available</option>
                    </select>
                </div>
            </div> --}}

            <div class="col-md-12">
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4" class="form-control ckeditor"
                            placeholder="Enter vehicle details...">{{ old('description', $vehiclecatalog->description ?? '') }}</textarea>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Vehicle Logo</label>
                    <input type="file" name="image" class="form-control">
                    @if(isset($vehiclecatalog) && $vehiclecatalog->image)
                        <br>
                        <img src="{{ asset($vehiclecatalog->image) }}" width="120" class="img-thumbnail">
                    @endif
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label>Vehicle Gallery Images</label>
                    <input type="file" name="car_images[]" id="carImagesInput" 
                           class="form-control" multiple accept="image/*">

                    {{-- Preview for newly selected images --}}
                    <div id="newImagePreview" class="d-flex flex-wrap gap-2 mt-2"></div>

                    {{-- Preview existing images (edit mode) --}}
                    @php
                        $carImages = $vehiclecatalog->car_images ?? null;
                        if (is_string($carImages)) {
                            $carImages = json_decode($carImages, true);
                        }
                        if (!is_array($carImages)) {
                            $carImages = [];
                        }
                    @endphp

                    @if(isset($vehiclecatalog) && !empty($carImages))
                        <div class="mt-2">
                            <small class="text-muted">Current Images:</small>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                @foreach($carImages as $img)
                                    <img src="{{ asset($img) }}" width="100"
                                         class="img-thumbnail" style="height:80px;object-fit:cover;">
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ================= REGISTRATION ================= -->
{{-- <div class="card card-info card-outline mb-4">
    <div class="card-header bg-info">
        <h3 class="card-title text-white">
            <i class="fas fa-id-card"></i> Registration Details
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mt-3">
                <label>Number Plate Color</label>
                <select name="number_plate_color" class="form-control">
                    <option value="">Select</option>
                    <option value="RED" {{ old('number_plate_color',$vehiclecatalog->number_plate_color ?? '')=='RED'?'selected':'' }}>RED</option>
                    <option value="BLACK" {{ old('number_plate_color',$vehiclecatalog->number_plate_color ?? '')=='BLACK'?'selected':'' }}>BLACK</option>
                    <option value="GREEN" {{ old('number_plate_color',$vehiclecatalog->number_plate_color ?? '')=='GREEN'?'selected':'' }}>GREEN</option>
                </select>
            </div>
        </div>
    </div>
</div> --}}

<!-- ================= INSURANCE ================= -->
{{-- <div class="card card-success card-outline mb-4">
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
                       value="{{ old('insurance_policy_no',$vehiclecatalog->insurance_policy_no ?? '') }}">
            </div>

            <div class="col-md-6">
                <label>Insurance Company</label>
                <input type="text" name="insurance_company" class="form-control"
                       value="{{ old('insurance_company',$vehiclecatalog->insurance_company ?? '') }}">
            </div>

            <div class="col-md-6 mt-3">
                <label>Insurance Type</label>
                <input type="text" name="insurance_type" class="form-control"
                       value="{{ old('insurance_type',$vehiclecatalog->insurance_type ?? '') }}">
            </div>

            <div class="col-md-6 mt-3">
                <label>Insurance Valid Till</label>
                <input type="date" name="insurance_till" class="form-control"
                       value="{{ old('insurance_till',$vehiclecatalog->insurance_till ?? '') }}">
            </div>

            <div class="col-md-6 mt-3">
                <label>Insurance Cost Per Annum</label>
                <input type="number" step="0.01" name="insurance_cost_per_annum" class="form-control"
                    value="{{ old('insurance_cost_per_annum',$vehiclecatalog->insurance_cost_per_annum ?? '') }}">
            </div>

            <div class="col-md-6 mt-3">
                <label>Insurance Policy Document (PDF/Image)</label>
                <input type="file" name="insurance_policy_document" class="form-control">

                @if(isset($vehiclecatalog) && $vehiclecatalog->insurance_policy_document)
                    <br>
                    <a href="{{ asset($vehiclecatalog->insurance_policy_document) }}" 
                    target="_blank" class="btn btn-sm btn-info">
                    View Document
                    </a>
                @endif
            </div>

        </div>
    </div>
</div> --}}

<!-- ================= PASSENGER INSURANCE ================= -->
{{-- <div class="card card-primary card-outline mb-4">
    <div class="card-header bg-primary">
        <h3 class="card-title text-white">
            <i class="fas fa-user-shield"></i> Passenger Insurance Details
        </h3>
    </div>

    <div class="card-body">
        <div class="row">

            <div class="col-md-4">
                <label class="form-label">Passenger Insured</label>

                <div class="form-check form-switch mt-2">
                    <input class="form-check-input"
                           type="checkbox"
                           id="passenger_insured"
                           name="passenger_insured"
                           value="1"
                           {{ old('passenger_insured', $vehiclecatalog->passenger_insured ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="passenger_insured">
                        Yes
                    </label>
                </div>
            </div>

            <div class="col-md-4">
                <label>Passenger Insured Amount</label>
                <div class="input-group">
                    <span class="input-group-text">Rs.</span>
                    <input type="number"
                           step="0.01"
                           min="0"
                           name="passenger_insured_amount"
                           class="form-control"
                           value="{{ old('passenger_insured_amount', $vehiclecatalog->passenger_insured_amount ?? '') }}"
                           placeholder="Enter insured amount">
                </div>
            </div>

            <div class="col-md-4">
                <label>Passenger Insurance Company</label>
                <input type="text"
                       name="passenger_insurance_company"
                       class="form-control"
                       value="{{ old('passenger_insurance_company', $vehiclecatalog->passenger_insurance_company ?? '') }}"
                       placeholder="Enter insurance company">
            </div>

        </div>
    </div>
</div> --}}

<!-- ================= SAFETY FEATURES ================= -->
{{-- <div class="card card-warning card-outline mb-4">
    <div class="card-header bg-warning">
        <h3 class="card-title text-dark">
            <i class="fas fa-shield-virus"></i> Safety Features
        </h3>
    </div>

    <div class="card-body">
        <div class="row"> --}}
            
            <!-- Dash Cam -->
            {{-- <div class="col-md-4">
                <div class="form-group">
                    <label>Dash Cam</label>
                    <select name="dash_cam" class="form-control">
                        <option value="0" {{ old('dash_cam', $vehiclecatalog->dash_cam ?? 0) == 0 ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('dash_cam', $vehiclecatalog->dash_cam ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>
            </div> --}}
            {{-- <div class="col-md-4">
                <div class="form-group">
                    <label>Dash Cam Image</label>
                    <input type="file" name="dash_cam_image" class="form-control">
                    @if(isset($vehiclecatalog) && $vehiclecatalog->dash_cam_image)
                        <br>
                        <img src="{{ asset('uploads/vehiclecatalog/security-features/' . $vehiclecatalog->dash_cam_image) }}" 
                             width="80" class="img-thumbnail">
                    @endif
                </div>
            </div> --}}

            <!-- EBS -->
            {{-- <div class="col-md-4">
                <div class="form-group">
                    <label>EBS</label>
                    <select name="ebs" class="form-control">
                        <option value="0" {{ old('ebs', $vehiclecatalog->ebs ?? 0) == 0 ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('ebs', $vehiclecatalog->ebs ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>
            </div> --}}
            {{-- <div class="col-md-4">
                <div class="form-group">
                    <label>EBS Image</label>
                    <input type="file" name="ebs_image" class="form-control">
                    @if(isset($vehiclecatalog) && $vehiclecatalog->ebs_image)
                        <br>
                        <img src="{{ asset('uploads/vehiclecatalog/security-features/' . $vehiclecatalog->ebs_image) }}" 
                             width="80" class="img-thumbnail">
                    @endif
                </div>
            </div> --}}

            <!-- Air Conditioning -->
            {{-- <div class="col-md-4">
                <div class="form-group">
                    <label>Air Conditioning</label>
                    <select name="air_conditioning" class="form-control">
                        <option value="0" {{ old('air_conditioning', $vehiclecatalog->air_conditioning ?? 0) == 0 ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('air_conditioning', $vehiclecatalog->air_conditioning ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>
            </div> --}}
            {{-- <div class="col-md-4">
                <div class="form-group">
                    <label>Air Conditioning Image</label>
                    <input type="file" name="air_conditioning_image" class="form-control">
                    @if(isset($vehiclecatalog) && $vehiclecatalog->air_conditioning_image)
                        <br>
                        <img src="{{ asset('uploads/vehiclecatalog/security-features/' . $vehiclecatalog->air_conditioning_image) }}" 
                             width="80" class="img-thumbnail">
                    @endif
                </div>
            </div> --}}

            <!-- Reverse Camera -->
            {{-- <div class="col-md-4">
                <div class="form-group">
                    <label>Reverse Camera</label>
                    <select name="reverse_camera" class="form-control">
                        <option value="0" {{ old('reverse_camera', $vehiclecatalog->reverse_camera ?? 0) == 0 ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('reverse_camera', $vehiclecatalog->reverse_camera ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>
            </div> --}}
            {{-- <div class="col-md-4">
                <div class="form-group">
                    <label>Reverse Camera Image</label>
                    <input type="file" name="reverse_camera_image" class="form-control">
                    @if(isset($vehiclecatalog) && $vehiclecatalog->reverse_camera_image)
                        <br>
                        <img src="{{ asset('uploads/vehiclecatalog/security-features/' . $vehiclecatalog->reverse_camera_image) }}" 
                             width="80" class="img-thumbnail">
                    @endif
                </div>
            </div> --}}

            <!-- Camera 360 -->
            {{-- <div class="col-md-4">
                <div class="form-group">
                    <label>Camera 360</label>
                    <select name="camera_360" class="form-control">
                        <option value="0" {{ old('camera_360', $vehiclecatalog->camera_360 ?? 0) == 0 ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('camera_360', $vehiclecatalog->camera_360 ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>
            </div> --}}
            {{-- <div class="col-md-4">
                <div class="form-group">
                    <label>Camera 360 Image</label>
                    <input type="file" name="camera_360_image" class="form-control">
                    @if(isset($vehiclecatalog) && $vehiclecatalog->camera_360_image)
                        <br>
                        <img src="{{ asset('uploads/vehiclecatalog/security-features/' . $vehiclecatalog->camera_360_image) }}" 
                             width="80" class="img-thumbnail">
                    @endif
                </div>
            </div> --}}

            <!-- Emergency Braking System -->
            {{-- <div class="col-md-4">
                <div class="form-group">
                    <label>Emergency Braking System</label>
                    <select name="emergency_braking_system" class="form-control">
                        <option value="0" {{ old('emergency_braking_system', $vehiclecatalog->emergency_braking_system ?? 0) == 0 ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('emergency_braking_system', $vehiclecatalog->emergency_braking_system ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>
            </div> --}}
            {{-- <div class="col-md-4">
                <div class="form-group">
                    <label>Emergency Braking System Image</label>
                    <input type="file" name="emergency_braking_system_image" class="form-control">
                    @if(isset($vehiclecatalog) && $vehiclecatalog->emergency_braking_system_image)
                        <br>
                        <img src="{{ asset('uploads/vehiclecatalog/security-features/' . $vehiclecatalog->emergency_braking_system_image) }}" 
                             width="80" class="img-thumbnail">
                    @endif
                </div>
            </div> --}}

            <!-- Hillside Braking System -->
            {{-- <div class="col-md-4">
                <div class="form-group">
                    <label>Hillside Braking System</label>
                    <select name="hillside_braking_system" class="form-control">
                        <option value="0" {{ old('hillside_braking_system', $vehiclecatalog->hillside_braking_system ?? 0) == 0 ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('hillside_braking_system', $vehiclecatalog->hillside_braking_system ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>
            </div> --}}
            {{-- <div class="col-md-4">
                <div class="form-group">
                    <label>Hillside Braking System Image</label>
                    <input type="file" name="hillside_braking_system_image" class="form-control">
                    @if(isset($vehiclecatalog) && $vehiclecatalog->hillside_braking_system_image)
                        <br>
                        <img src="{{ asset('uploads/vehiclecatalog/security-features/' . $vehiclecatalog->hillside_braking_system_image) }}" 
                             width="80" class="img-thumbnail">
                    @endif
                </div>
            </div> --}}

            <!-- Hill Descent Control -->
            {{-- <div class="col-md-4">
                <div class="form-group">
                    <label>Hill Descent Control</label>
                    <select name="hill_descent_control" class="form-control">
                        <option value="0" {{ old('hill_descent_control', $vehiclecatalog->hill_descent_control ?? 0) == 0 ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('hill_descent_control', $vehiclecatalog->hill_descent_control ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>
            </div> --}}
            {{-- <div class="col-md-4">
                <div class="form-group">
                    <label>Hill Descent Control Image</label>
                    <input type="file" name="hill_descent_control_image" class="form-control">
                    @if(isset($vehiclecatalog) && $vehiclecatalog->hill_descent_control_image)
                        <br>
                        <img src="{{ asset('uploads/vehiclecatalog/security-features/' . $vehiclecatalog->hill_descent_control_image) }}" 
                             width="80" class="img-thumbnail">
                    @endif
                </div>
            </div> --}}
{{-- 
        </div>
    </div>
</div> --}}



<!-- ================= CHARGES SECTION ================= -->
<!-- ================= CHARGES SECTION ================= -->
<div class="card card-success card-outline mb-4">
    <div class="card-header bg-success">
        <h3 class="card-title text-white">
            <i class="fas fa-money-bill-wave"></i> Vehicle Charges
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-sm btn-light" id="addChargeBtn">
                <i class="fas fa-plus"></i> Add Charge
            </button>
            <button type="button" class="btn btn-sm btn-info" id="autoFillChargesBtn">
                <i class="fas fa-magic"></i> Auto-fill from above
            </button>
        </div>
    </div>

    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Click <strong>"Auto-fill from above"</strong> to use the values selected in the basic information section.
        </div>

        <div id="chargesContainer">
            @if(isset($vehiclecatalog) && $vehiclecatalog->charges->count() > 0)
                @foreach($vehiclecatalog->charges as $index => $charge)
                    <div class="charge-item card card-outline card-secondary mb-3">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Charge #{{ $loop->iteration }}</h5>
                                <button type="button" class="btn btn-sm btn-danger remove-charge-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <input type="hidden" name="charges[{{ $index }}][id]" value="{{ $charge->id }}">
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Vehicle Type</label>
                                        <select name="charges[{{ $index }}][vehicle_type]" class="form-control charge-vehicle-type">
                                            <option value="">Select Vehicle Type</option>
                                            @foreach($fuel_type as $ft)
                                                <option value="{{ $ft->name }}"
                                                    {{ old("charges.$index.vehicle_type", $charge->vehicle_type) == $ft->name ? 'selected' : '' }}>
                                                    {{ $ft->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Brand</label>
                                        <select name="charges[{{ $index }}][brand]" class="form-control charge-brand" required>
                                            <option value="">Select Brand</option>
                                            @foreach($brands as $b)
                                                <option value="{{ $b->name }}"
                                                    {{ old("charges.$index.brand", $charge->brand) == $b->name ? 'selected' : '' }}>
                                                    {{ $b->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Seater</label>
                                        <select name="charges[{{ $index }}][seater]" class="form-control charge-seater" required>
                                            <option value="">Select Seater</option>
                                            @foreach($seaters as $s)
                                                <option value="{{ $s->name }}"
                                                    {{ old("charges.$index.seater", $charge->seater) == $s->name ? 'selected' : '' }}>
                                                    {{ $s->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Per KM (Rs)</label>
                                        <input type="number" step="0.01" min="0"
                                               name="charges[{{ $index }}][per_km]"
                                               class="form-control charge-per-km"
                                               placeholder="Per KM Rate"
                                               value="{{ old("charges.$index.per_km", $charge->per_km) }}">
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Per Hour (Rs)</label>
                                        <input type="number" step="0.01" min="0"
                                               name="charges[{{ $index }}][per_hour]"
                                               class="form-control charge-per-hour"
                                               placeholder="Per Hour Rate"
                                               value="{{ old("charges.$index.per_hour", $charge->per_hour) }}">
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Overnight Price (Rs)</label>
                                        <input type="number" step="0.01" min="0"
                                               name="charges[{{ $index }}][overnight_price]"
                                               class="form-control charge-overnight"
                                               placeholder="Overnight Price"
                                               value="{{ old("charges.$index.overnight_price", $charge->overnight_price) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <!-- Empty state -->
                <div class="text-center text-muted py-4" id="emptyChargesMessage">
                    <i class="fas fa-plus-circle fa-3x mb-2"></i>
                    <p>No charges added yet. Click "Add Charge" to set pricing or "Auto-fill from above" to populate with current values.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ================= SUBMIT ================= -->
<div class="card">
    <div class="card-footer text-right">
        <a href="{{ route('admin.vehiclecatalog.index') }}" class="btn btn-secondary">
            Back
        </a>

        <button type="submit" class="btn btn-primary">
            {{ isset($vehiclecatalog) ? 'Update Vehicle Catalog' : 'Create Vehicle Catalog' }}
        </button>
    </div>
</div>

</form>

</div>
</section>

@endsection

@section('scripts')

<script src="https://cdn.ckeditor.com/ckeditor5/23.0.0/classic/ckeditor.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll('.ckeditor').forEach(function (textarea) {

        ClassicEditor
            .create(textarea)
            .catch(error => {
                console.error(error);
            });

    });

});
// Gallery image live preview
document.getElementById('carImagesInput').addEventListener('change', function () {
    const preview = document.getElementById('newImagePreview');
    preview.innerHTML = '';

    Array.from(this.files).forEach(function (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const wrapper = document.createElement('div');
            wrapper.style.position = 'relative';

            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'img-thumbnail';
            img.style.cssText = 'width:100px;height:80px;object-fit:cover;';

            const label = document.createElement('small');
            label.className = 'd-block text-muted text-truncate';
            label.style.maxWidth = '100px';
            label.textContent = file.name;

            wrapper.appendChild(img);
            wrapper.appendChild(label);
            preview.appendChild(wrapper);
        };
        reader.readAsDataURL(file);
    });
});


document.addEventListener('DOMContentLoaded', function () {
    const insuredCheckbox = document.getElementById('passenger_insured');
    const amountField = document.querySelector('[name="passenger_insured_amount"]').closest('.col-md-4');
    const companyField = document.querySelector('[name="passenger_insurance_company"]').closest('.col-md-4');

    function toggleFields() {
        const show = insuredCheckbox.checked;
        amountField.style.display = show ? '' : 'none';
        companyField.style.display = show ? '' : 'none';
    }

    insuredCheckbox.addEventListener('change', toggleFields);
    toggleFields();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gallery image preview code...
    
    // Charge management
    let chargeIndex = {{ isset($vehiclecatalog) ? $vehiclecatalog->charges->count() : 0 }};
    const container = document.getElementById('chargesContainer');
    const emptyMessage = document.getElementById('emptyChargesMessage');
    
    // Function to get current values from main form
    function getMainFormValues() {
        const brand = document.querySelector('select[name="brand"]')?.value || '';
        const seater = document.querySelector('select[name="seater"]')?.value || '';
        const fuelType = document.querySelector('select[name="fuel_type"]')?.value || '';
        const rentPrice = document.querySelector('input[name="rent_price_per_day"]')?.value || '';
        
        return { brand, seater, fuelType, rentPrice };
    }
    
    function updateEmptyMessage() {
        const items = container.querySelectorAll('.charge-item');
        if (items.length === 0) {
            if (!document.getElementById('emptyChargesMessage')) {
                const msg = document.createElement('div');
                msg.id = 'emptyChargesMessage';
                msg.className = 'text-center text-muted py-4';
                msg.innerHTML = `
                    <i class="fas fa-plus-circle fa-3x mb-2"></i>
                    <p>No charges added yet. Click "Add Charge" to set pricing or "Auto-fill from above" to populate with current values.</p>
                `;
                container.appendChild(msg);
            }
        } else {
            const emptyMsg = document.getElementById('emptyChargesMessage');
            if (emptyMsg) emptyMsg.remove();
        }
    }
    
    function addCharge(chargeData = null) {
        const index = chargeIndex++;
        const div = document.createElement('div');
        div.className = 'charge-item card card-outline card-secondary mb-3';
        
        // Get current main form values if no data provided
        const mainValues = getMainFormValues();
        
        // Use provided data or auto-fill from main form
        const data = chargeData || {
            vehicle_type: mainValues.fuelType || '',
            brand: mainValues.brand || '',
            seater: mainValues.seater || '',
            per_km: '',
            per_hour: '',
            overnight_price: ''
        };
        
        // Build brand options
        let brandOptions = `<option value="">Select Brand</option>`;
        @foreach($brands as $b)
            brandOptions += `<option value="{{ $b->name }}" ${data.brand == '{{ $b->name }}' ? 'selected' : ''}>{{ $b->name }}</option>`;
        @endforeach
        
        // Build seater options
        let seaterOptions = `<option value="">Select Seater</option>`;
        @foreach($seaters as $s)
            seaterOptions += `<option value="{{ $s->name }}" ${data.seater == '{{ $s->name }}' ? 'selected' : ''}>{{ $s->name }}</option>`;
        @endforeach
        
        // Build fuel type options
        let fuelOptions = `<option value="">Select Vehicle Type</option>`;
        @foreach($fuel_type as $ft)
            fuelOptions += `<option value="{{ $ft->name }}" ${data.vehicle_type == '{{ $ft->name }}' ? 'selected' : ''}>{{ $ft->name }}</option>`;
        @endforeach
        
        div.innerHTML = `
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Charge #${index + 1}</h5>
                    <button type="button" class="btn btn-sm btn-danger remove-charge-btn">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Vehicle Type</label>
                            <select name="charges[${index}][vehicle_type]" class="form-control charge-vehicle-type">
                                ${fuelOptions}
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Brand</label>
                            <select name="charges[${index}][brand]" class="form-control charge-brand" required>
                                ${brandOptions}
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Seater</label>
                            <select name="charges[${index}][seater]" class="form-control charge-seater" required>
                                ${seaterOptions}
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Per KM (Rs)</label>
                            <input type="number" step="0.01" min="0"
                                   name="charges[${index}][per_km]"
                                   class="form-control charge-per-km"
                                   placeholder="Per KM Rate"
                                   value="${data.per_km || ''}">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Per Hour (Rs)</label>
                            <input type="number" step="0.01" min="0"
                                   name="charges[${index}][per_hour]"
                                   class="form-control charge-per-hour"
                                   placeholder="Per Hour Rate"
                                   value="${data.per_hour || ''}">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Overnight Price (Rs)</label>
                            <input type="number" step="0.01" min="0"
                                   name="charges[${index}][overnight_price]"
                                   class="form-control charge-overnight"
                                   placeholder="Overnight Price"
                                   value="${data.overnight_price || ''}">
                        </div>
                    </div>
                    
                    <div class="col-md-12 mt-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary auto-fill-single-btn">
                            <i class="fas fa-sync"></i> Auto-fill from above
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Add remove functionality
        const removeBtn = div.querySelector('.remove-charge-btn');
        removeBtn.addEventListener('click', function() {
            if (confirm('Remove this charge entry?')) {
                div.remove();
                updateEmptyMessage();
                reindexCharges();
            }
        });
        
        // Add auto-fill for individual charge
        const autoFillBtn = div.querySelector('.auto-fill-single-btn');
        autoFillBtn.addEventListener('click', function() {
            const mainValues = getMainFormValues();
            const vehicleTypeSelect = div.querySelector('.charge-vehicle-type');
            const brandSelect = div.querySelector('.charge-brand');
            const seaterSelect = div.querySelector('.charge-seater');
            
            if (mainValues.fuelType) {
                vehicleTypeSelect.value = mainValues.fuelType;
            }
            if (mainValues.brand) {
                brandSelect.value = mainValues.brand;
            }
            if (mainValues.seater) {
                seaterSelect.value = mainValues.seater;
            }
            
            // Show feedback
            this.innerHTML = '<i class="fas fa-check"></i> Auto-filled!';
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-sync"></i> Auto-fill from above';
            }, 2000);
        });
        
        container.appendChild(div);
        updateEmptyMessage();
    }
    
    function reindexCharges() {
        const items = container.querySelectorAll('.charge-item');
        items.forEach((item, idx) => {
            const inputs = item.querySelectorAll('[name^="charges["]');
            inputs.forEach(input => {
                const name = input.name.replace(/charges\[\d+\]/, `charges[${idx}]`);
                input.name = name;
            });
            const title = item.querySelector('.card-title');
            if (title) {
                title.textContent = `Charge #${idx + 1}`;
            }
        });
        // Reset chargeIndex to match
        chargeIndex = items.length;
    }
    
    // Auto-fill all charges from main form
    function autoFillAllCharges() {
        const mainValues = getMainFormValues();
        
        if (!mainValues.brand && !mainValues.seater && !mainValues.fuelType) {
            alert('Please select Brand, Seater, and Vehicle Type in the basic information section first.');
            return;
        }
        
        const chargeItems = container.querySelectorAll('.charge-item');
        
        if (chargeItems.length === 0) {
            // No charges exist, add one with auto-fill
            addCharge();
            // Then auto-fill the newly added charge
            setTimeout(() => {
                const newItem = container.querySelector('.charge-item:last-child');
                if (newItem) {
                    const vehicleTypeSelect = newItem.querySelector('.charge-vehicle-type');
                    const brandSelect = newItem.querySelector('.charge-brand');
                    const seaterSelect = newItem.querySelector('.charge-seater');
                    
                    if (mainValues.fuelType) vehicleTypeSelect.value = mainValues.fuelType;
                    if (mainValues.brand) brandSelect.value = mainValues.brand;
                    if (mainValues.seater) seaterSelect.value = mainValues.seater;
                }
            }, 100);
        } else {
            // Auto-fill all existing charges
            chargeItems.forEach(item => {
                const vehicleTypeSelect = item.querySelector('.charge-vehicle-type');
                const brandSelect = item.querySelector('.charge-brand');
                const seaterSelect = item.querySelector('.charge-seater');
                
                if (mainValues.fuelType) vehicleTypeSelect.value = mainValues.fuelType;
                if (mainValues.brand) brandSelect.value = mainValues.brand;
                if (mainValues.seater) seaterSelect.value = mainValues.seater;
            });
        }
        
        // Show feedback
        const btn = document.getElementById('autoFillChargesBtn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Auto-filled!';
        setTimeout(() => {
            btn.innerHTML = originalText;
        }, 2000);
    }
    
    // Add charge button
    document.getElementById('addChargeBtn').addEventListener('click', function() {
        addCharge();
    });
    
    // Auto-fill all charges button
    document.getElementById('autoFillChargesBtn').addEventListener('click', autoFillAllCharges);
    
    // Handle existing remove buttons
    document.querySelectorAll('.remove-charge-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Remove this charge entry?')) {
                this.closest('.charge-item').remove();
                updateEmptyMessage();
                reindexCharges();
            }
        });
    });
    
    // Auto-fill when main form fields change (optional)
    const mainFields = ['brand', 'seater', 'fuel_type', 'rent_price_per_day'];
    mainFields.forEach(field => {
        const element = document.querySelector(`[name="${field}"]`);
        if (element) {
            element.addEventListener('change', function() {
                // You can choose to auto-fill on change if desired
                // Uncomment the line below to enable auto-fill on change
                // autoFillAllCharges();
            });
        }
    });
    
    // Initialize: if there are existing charges, update empty message
    updateEmptyMessage();
});
</script>

@endsection