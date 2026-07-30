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
                    <label>Vehicle Owner</label>
                    <select name="vehicle_owner_id" class="form-control">
                        <option value="">Select Vehicle Owner</option>
                        @foreach($vehicle_owners as $owner)
                            <option value="{{ $owner->id }}" {{ old('vehicle_owner_id',$vehicle->vehicle_owner_id ?? '')==$owner->id?'selected':'' }}>{{ $owner->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Vehicle Catalog</label>
                    <select name="vehicle_catalog_id" id="vehicleCatalogSelect" class="form-control">
                        <option value="">Select Vehicle Catalog</option>
                        @foreach($vehicle_catalog as $catalogs)
                            <option value="{{ $catalogs->id }}" 
                                {{ old('vehicle_catalog_id', $vehicle->vehicle_catalog_id ?? '') == $catalogs->id ? 'selected' : '' }}
                                data-brand="{{ $catalogs->brand }}"
                                data-model="{{ $catalogs->model }}"
                                data-seater="{{ $catalogs->seater }}"
                                data-year="{{ $catalogs->year }}"
                                data-fuel-type="{{ $catalogs->fuel_type }}"
                                data-transmission="{{ $catalogs->transmission }}"
                                data-mileage="{{ $catalogs->mileage }}"
                                data-horsepower="{{ $catalogs->horsepower }}"
                                data-car-color="{{ $catalogs->car_color }}"
                                data-description="{{ $catalogs->description }}"
                                data-image="{{ $catalogs->image }}"
                                data-car-images="{{ json_encode($catalogs->car_images) }}"
                                data-registration-number="{{ $catalogs->registration_number }}"
                                data-registered-at="{{ $catalogs->registered_at }}"
                                data-number-plate-color="{{ $catalogs->number_plate_color }}"
                                data-registration-expiry="{{ $catalogs->registration_expiry }}"
                                data-bill-book-number="{{ $catalogs->bill_book_number }}"
                                data-bill-book-image="{{ $catalogs->bill_book_image }}"
                                data-insurance-policy-no="{{ $catalogs->insurance_policy_no }}"
                                data-insurance-company="{{ $catalogs->insurance_company }}"
                                data-insurance-type="{{ $catalogs->insurance_type }}"
                                data-insurance-till="{{ $catalogs->insurance_till }}"
                                data-insurance-cost-per-annum="{{ $catalogs->insurance_cost_per_annum }}"
                                data-insurance-policy-document="{{ $catalogs->insurance_policy_document }}"
                                data-passenger-insured="{{ $catalogs->passenger_insured }}"
                                data-passenger-insured-amount="{{ $catalogs->passenger_insured_amount }}"
                                data-passenger-insurance-company="{{ $catalogs->passenger_insurance_company }}">
                                {{ $catalogs->brand.' '.$catalogs->seater.' Seater' }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Select a catalog to auto-fill vehicle details</small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Vehicle Name *</label>
                    <input type="text" name="vehicle_name" id="vehicleName" class="form-control"
                           value="{{ old('vehicle_name',$vehicle->vehicle_name ?? '') }}" required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Vehicle Type *</label>
                    <select name="vehicle_type" id="vehicleType" class="form-control">
                        <option value="car" {{ old('vehicle_type',$vehicle->vehicle_type ?? '')=='car'?'selected':'' }}>Car</option>
                        <option value="van" {{ old('vehicle_type',$vehicle->vehicle_type ?? '')=='van'?'selected':'' }}>Van</option>
                        <option value="coaster" {{ old('vehicle_type',$vehicle->vehicle_type ?? '')=='coaster'?'selected':'' }}>Coaster</option>
                        <option value="bus" {{ old('vehicle_type',$vehicle->vehicle_type ?? '')=='bus'?'selected':'' }}>Bus</option>
                        <option value="other" {{ old('vehicle_type',$vehicle->vehicle_type ?? '')=='other'?'selected':'' }}>Other</option>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Brand *</label>
                    <select name="brand" id="brand" class="form-control" required>
                        <option value="">Select Brand</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->name }}"
                                {{ old('brand', $vehicle->brand ?? '') == $b->name ? 'selected' : '' }}>
                                {{ $b->name }}
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
                    <label>Seater *</label>
                    <select name="seater" id="seater" class="form-control" required>
                        <option value="">Select Seater</option>
                        @foreach($seaters as $b)
                            <option value="{{ $b->name }}"
                                {{ old('seater', $vehicle->seater ?? '') == $b->name ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Year *</label>
                    <input type="number" name="year" id="year" class="form-control"
                           value="{{ old('year',$vehicle->year ?? '') }}" required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Mileage (KM)</label>
                    <input type="number" name="mileage" id="mileage" class="form-control"
                        value="{{ old('mileage', $vehicle->mileage ?? '') }}">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Horsepower</label>
                    <input type="number" name="horsepower" id="horsepower" class="form-control"
                        value="{{ old('horsepower', $vehicle->horsepower ?? '') }}">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Car Color</label>
                    <input type="text" name="car_color" id="carColor" class="form-control"
                        value="{{ old('car_color', $vehicle->car_color ?? '') }}">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Fuel Type *</label>
                    <select name="fuel_type" id="fuelType" class="form-control" required>
                        <option value="">Select Fuel Type</option>
                        @foreach($fuel_type as $ft)
                            <option value="{{ $ft->name }}"
                                {{ old('fuel_type', $vehicle->fuel_type ?? '') == $ft->name ? 'selected' : '' }}>
                                {{ $ft->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Transmission *</label>
                    <select name="transmission" id="transmission" class="form-control">
                        <option value="Manual" {{ old('transmission',$vehicle->transmission ?? '')=='Manual'?'selected':'' }}>Manual</option>
                        <option value="Automatic" {{ old('transmission',$vehicle->transmission ?? '')=='Automatic'?'selected':'' }}>Automatic</option>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Helper Required</label>
                    <select name="is_helper_needed" id="isHelperNeeded" class="form-control">
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

            <div class="col-md-12">
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="description" rows="4" class="form-control ckeditor"
                            placeholder="Enter vehicle details...">{{ old('description', $vehicle->description ?? '') }}</textarea>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Vehicle Logo</label>
                    <input type="file" name="image" class="form-control">
                    @if(isset($vehicle) && $vehicle->image)
                        <br>
                        <img src="{{ asset($vehicle->image) }}" width="120" class="img-thumbnail">
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
                        $carImages = $vehicle->car_images ?? null;
                        if (is_string($carImages)) {
                            $carImages = json_decode($carImages, true);
                        }
                        if (!is_array($carImages)) {
                            $carImages = [];
                        }
                    @endphp

                    @if(isset($vehicle) && !empty($carImages))
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
                <input type="text" name="registration_number" id="registrationNumber" class="form-control"
                       value="{{ old('registration_number',$vehicle->registration_number ?? '') }}">
            </div>

            <div class="col-md-6">
                <label>Registered At</label>
                <input type="text" name="registered_at" id="registeredAt" class="form-control"
                       value="{{ old('registered_at',$vehicle->registered_at ?? '') }}">
            </div>

            <div class="col-md-6 mt-3">
                <label>Number Plate Color</label>
                <select name="number_plate_color" id="numberPlateColor" class="form-control">
                    <option value="">Select</option>
                    <option value="RED" {{ old('number_plate_color',$vehicle->number_plate_color ?? '')=='RED'?'selected':'' }}>RED</option>
                    <option value="BLACK" {{ old('number_plate_color',$vehicle->number_plate_color ?? '')=='BLACK'?'selected':'' }}>BLACK</option>
                    <option value="GREEN" {{ old('number_plate_color',$vehicle->number_plate_color ?? '')=='GREEN'?'selected':'' }}>GREEN</option>
                </select>
            </div>

            <div class="col-md-6 mt-3">
                <label>Registration Expiry</label>
                <input type="date" name="registration_expiry" id="registrationExpiry" class="form-control"
                       value="{{ old('registration_expiry',$vehicle->registration_expiry ?? '') }}">
            </div>

            <div class="col-md-6 mt-3">
                <label>Bill Book Number</label>
                <input type="text" name="bill_book_number" id="billBookNumber" class="form-control"
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
                <input type="text" name="insurance_policy_no" id="insurancePolicyNo" class="form-control"
                       value="{{ old('insurance_policy_no',$vehicle->insurance_policy_no ?? '') }}">
            </div>

            <div class="col-md-6">
                <label>Insurance Company</label>
                <input type="text" name="insurance_company" id="insuranceCompany" class="form-control"
                       value="{{ old('insurance_company',$vehicle->insurance_company ?? '') }}">
            </div>

            <div class="col-md-6 mt-3">
                <label>Insurance Type</label>
                <input type="text" name="insurance_type" id="insuranceType" class="form-control"
                       value="{{ old('insurance_type',$vehicle->insurance_type ?? '') }}">
            </div>

            <div class="col-md-6 mt-3">
                <label>Insurance Valid Till</label>
                <input type="date" name="insurance_till" id="insuranceTill" class="form-control"
                       value="{{ old('insurance_till',$vehicle->insurance_till ?? '') }}">
            </div>

            <div class="col-md-6 mt-3">
                <label>Insurance Cost Per Annum</label>
                <input type="number" step="0.01" name="insurance_cost_per_annum" id="insuranceCostPerAnnum" class="form-control"
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

<!-- Passenger Insurance Details -->
<div class="card card-primary card-outline mb-4">
    <div class="card-header bg-primary">
        <h3 class="card-title text-white">
            <i class="fas fa-user-shield"></i> Passenger Insurance Details
        </h3>
    </div>

    <div class="card-body">
        <div class="row">

            <!-- Passenger Insured -->
            <div class="col-md-4">
                <label class="form-label">Passenger Insured</label>

                <div class="form-check form-switch mt-2">
                    <input class="form-check-input"
                           type="checkbox"
                           id="passenger_insured"
                           name="passenger_insured"
                           value="1"
                           {{ old('passenger_insured', $vehicle->passenger_insured ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="passenger_insured">
                        Yes
                    </label>
                </div>
            </div>

            <!-- Passenger Insured Amount -->
            <div class="col-md-4">
                <label>Passenger Insured Amount</label>
                <div class="input-group">
                    <span class="input-group-text">Rs.</span>
                    <input type="number"
                           step="0.01"
                           min="0"
                           name="passenger_insured_amount"
                           id="passengerInsuredAmount"
                           class="form-control"
                           value="{{ old('passenger_insured_amount', $vehicle->passenger_insured_amount ?? '') }}"
                           placeholder="Enter insured amount">
                </div>
            </div>

            <!-- Passenger Insurance Company -->
            <div class="col-md-4">
                <label>Passenger Insurance Company</label>
                <input type="text"
                       name="passenger_insurance_company"
                       id="passengerInsuranceCompany"
                       class="form-control"
                       value="{{ old('passenger_insurance_company', $vehicle->passenger_insurance_company ?? '') }}"
                       placeholder="Enter insurance company">
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

    // Passenger Insurance toggle
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

    // =============================================
    // VEHICLE CATALOG AUTO-FILL FUNCTIONALITY
    // =============================================
    const catalogSelect = document.getElementById('vehicleCatalogSelect');

    if (catalogSelect) {
        catalogSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (!selectedOption || !selectedOption.value) {
                // Clear fields if no catalog selected
                clearFields();
                return;
            }

            // Get data attributes
            const data = {
                brand: selectedOption.dataset.brand || '',
                model: selectedOption.dataset.model || '',
                seater: selectedOption.dataset.seater || '',
                year: selectedOption.dataset.year || '',
                fuelType: selectedOption.dataset.fuelType || '',
                transmission: selectedOption.dataset.transmission || '',
                mileage: selectedOption.dataset.mileage || '',
                horsepower: selectedOption.dataset.horsepower || '',
                carColor: selectedOption.dataset.carColor || '',
                description: selectedOption.dataset.description || '',
                image: selectedOption.dataset.image || '',
                carImages: selectedOption.dataset.carImages || '[]',
                
                // Registration
                registrationNumber: selectedOption.dataset.registrationNumber || '',
                registeredAt: selectedOption.dataset.registeredAt || '',
                numberPlateColor: selectedOption.dataset.numberPlateColor || '',
                registrationExpiry: selectedOption.dataset.registrationExpiry || '',
                billBookNumber: selectedOption.dataset.billBookNumber || '',
                billBookImage: selectedOption.dataset.billBookImage || '',
                
                // Insurance
                insurancePolicyNo: selectedOption.dataset.insurancePolicyNo || '',
                insuranceCompany: selectedOption.dataset.insuranceCompany || '',
                insuranceType: selectedOption.dataset.insuranceType || '',
                insuranceTill: selectedOption.dataset.insuranceTill || '',
                insuranceCostPerAnnum: selectedOption.dataset.insuranceCostPerAnnum || '',
                insurancePolicyDocument: selectedOption.dataset.insurancePolicyDocument || '',
                
                // Passenger Insurance
                passengerInsured: selectedOption.dataset.passengerInsured || '',
                passengerInsuredAmount: selectedOption.dataset.passengerInsuredAmount || '',
                passengerInsuranceCompany: selectedOption.dataset.passengerInsuranceCompany || ''
            };

            // Auto-fill all fields
            autoFillFields(data);
            
            // Show success message
            showNotification('Vehicle catalog loaded successfully!', 'success');
        });

        // If there's already a selected catalog on page load (edit mode), trigger auto-fill
        if (catalogSelect.value) {
            catalogSelect.dispatchEvent(new Event('change'));
        }
    }

    function autoFillFields(data) {
        // Basic Information
        setSelectValue('brand', data.brand);
        document.getElementById('model').value = data.model;
        setSelectValue('seater', data.seater);
        document.getElementById('year').value = data.year;
        setSelectValue('fuelType', data.fuelType);
        setSelectValue('transmission', data.transmission);
        document.getElementById('mileage').value = data.mileage;
        document.getElementById('horsepower').value = data.horsepower;
        document.getElementById('carColor').value = data.carColor;
        
        // Description - handle CKEditor
        const descriptionField = document.getElementById('description');
        if (descriptionField) {
            descriptionField.value = data.description;
            // If CKEditor is initialized, update its content
            if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances['description']) {
                CKEDITOR.instances['description'].setData(data.description);
            }
        }

        // Auto-generate vehicle name from brand and model
        const vehicleName = data.brand + ' ' + data.model;
        // document.getElementById('vehicleName').value = vehicleName;

        // Registration
        document.getElementById('registrationNumber').value = data.registrationNumber;
        document.getElementById('registeredAt').value = data.registeredAt;
        setSelectValue('numberPlateColor', data.numberPlateColor);
        document.getElementById('registrationExpiry').value = data.registrationExpiry;
        document.getElementById('billBookNumber').value = data.billBookNumber;
        
        // Insurance
        document.getElementById('insurancePolicyNo').value = data.insurancePolicyNo;
        document.getElementById('insuranceCompany').value = data.insuranceCompany;
        document.getElementById('insuranceType').value = data.insuranceType;
        document.getElementById('insuranceTill').value = data.insuranceTill;
        document.getElementById('insuranceCostPerAnnum').value = data.insuranceCostPerAnnum;
        
        // Passenger Insurance
        if (data.passengerInsured && data.passengerInsured == '1') {
            document.getElementById('passenger_insured').checked = true;
        } else {
            document.getElementById('passenger_insured').checked = false;
        }
        document.getElementById('passengerInsuredAmount').value = data.passengerInsuredAmount;
        document.getElementById('passengerInsuranceCompany').value = data.passengerInsuranceCompany;
        
        // Toggle passenger insurance fields
        toggleFields();

        // Show catalog image preview if exists
        if (data.image) {
            // You can show the image preview here if needed
            const imagePreview = document.querySelector('.img-thumbnail');
            if (imagePreview) {
                imagePreview.src = data.image;
            }
        }

        // Show gallery images (car_images)
        try {
            const carImages = JSON.parse(data.carImages);
            if (Array.isArray(carImages) && carImages.length > 0) {
                // You can update gallery preview here if needed
                const galleryContainer = document.querySelector('.d-flex.flex-wrap.gap-2.mt-1');
                if (galleryContainer) {
                    galleryContainer.innerHTML = '';
                    carImages.forEach(img => {
                        const imgElement = document.createElement('img');
                        imgElement.src = img;
                        imgElement.width = 100;
                        imgElement.className = 'img-thumbnail';
                        imgElement.style.cssText = 'height:80px;object-fit:cover;';
                        galleryContainer.appendChild(imgElement);
                    });
                }
            }
        } catch (e) {
            console.log('No gallery images to display');
        }
    }

    function setSelectValue(elementId, value) {
        const select = document.getElementById(elementId);
        if (select) {
            const options = select.options;
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === value) {
                    select.selectedIndex = i;
                    break;
                }
            }
        }
    }

    function clearFields() {
        // Clear all fields
        const fields = [
            'brand', 'model', 'seater', 'year', 'fuelType', 'transmission',
            'mileage', 'horsepower', 'carColor', 'description', 'vehicleName',
            'registrationNumber', 'registeredAt', 'numberPlateColor',
            'registrationExpiry', 'billBookNumber',
            'insurancePolicyNo', 'insuranceCompany', 'insuranceType',
            'insuranceTill', 'insuranceCostPerAnnum',
            'passengerInsuredAmount', 'passengerInsuranceCompany'
        ];
        
        fields.forEach(fieldId => {
            const element = document.getElementById(fieldId);
            if (element) {
                if (element.tagName === 'SELECT') {
                    element.selectedIndex = 0;
                } else {
                    element.value = '';
                }
            }
        });
        
        // Uncheck passenger insured
        document.getElementById('passenger_insured').checked = false;
        toggleFields();
    }

    function showNotification(message, type = 'info') {
        // You can implement a toast notification here
        // For now, we'll use a simple alert
        console.log(message);
    }

});
</script>

<style>
/* Additional styling for better UX */
#vehicleCatalogSelect {
    border-color: #17a2b8;
    background-color: #f8f9fa;
}

#vehicleCatalogSelect option {
    padding: 5px;
}

.gap-2 {
    gap: 0.5rem;
}

/* Highlight auto-filled fields */
.auto-filled {
    border-left: 3px solid #28a745 !important;
    background-color: #f8fff8 !important;
    transition: all 0.3s ease;
}

/* Auto-fill animation */
@keyframes highlightField {
    0% { background-color: #d4edda; }
    100% { background-color: transparent; }
}

.auto-filled-animation {
    animation: highlightField 1.5s ease;
}
</style>

@endsection