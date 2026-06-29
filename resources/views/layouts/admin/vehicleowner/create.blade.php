{{-- resources/views/layouts/admin/vehicleowner/create.blade.php --}}

@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($vehicleowner) ? 'Edit Vehicle Owner' : 'Add Vehicle Owner' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<form action="{{ isset($vehicleowner) ? route('admin.vehicleowner.update', $vehicleowner->id) : route('admin.vehicleowner.store') }}"
      method="POST">
@csrf
@if(isset($vehicleowner)) @method('PUT') @endif

<div class="card-body">
@include('layouts.admin_theme.alert')

{{-- Personal Information Section --}}
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user mr-2"></i>Personal Information</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Company/Individual Name *</label>
                            <input type="text" name="name" class="form-control" 
                                   placeholder="Enter company or business name"
                                   value="{{ old('name', $vehicleowner->name ?? '') }}" required>
                            <small class="text-muted">This will be the primary name for billing</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" class="form-control" 
                                   placeholder="email@example.com"
                                   value="{{ old('email', $vehicleowner->email ?? '') }}">
                            <small class="text-muted">Email address for communication</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Phone *</label>
                            <input type="text" name="phone" class="form-control" 
                                   placeholder="Contact number"
                                   value="{{ old('phone', $vehicleowner->phone ?? '') }}" required>
                            <small class="text-muted">Primary contact number</small>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Address *</label>
                            <textarea name="address" class="form-control" rows="2" 
                                      placeholder="Full address">{{ old('address', $vehicleowner->address ?? '') }}</textarea>
                            <small class="text-muted">Complete physical address</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="city" class="form-control" 
                                   placeholder="City"
                                   value="{{ old('city', $vehicleowner->city ?? '') }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>State</label>
                            <input type="text" name="state" class="form-control" 
                                   placeholder="State"
                                   value="{{ old('state', $vehicleowner->state ?? '') }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>PAN Number</label>
                            <input type="text" name="pan_number" class="form-control" 
                                   placeholder="PAN card number"
                                   value="{{ old('pan_number', $vehicleowner->pan_number ?? '') }}">
                            <small class="text-muted">Tax identification number</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status *</label>
                            <select name="status" class="form-control" required>
                                <option value="active" {{ (old('status', $vehicleowner->status ?? '') == 'active') ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ (old('status', $vehicleowner->status ?? '') == 'inactive') ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <small class="text-muted">Active owners will receive commissions</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Commission Settings Section --}}
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-percentage mr-2"></i>Commission Settings</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Commission Rate (%)</label>
                            <div class="input-group">
                                <input type="number" name="commission_rate" class="form-control" 
                                       placeholder="0.00" step="0.01" min="0" max="100"
                                       value="{{ old('commission_rate', $vehicleowner->commission_rate ?? '15') }}">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-percent"></i></span>
                                </div>
                            </div>
                            <small class="text-muted">Commission rate for this owner (0-100%)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Commission Type</label>
                            <div class="input-group">
                                <select name="commission_type" class="form-control">
                                    <option value="percentage" {{ old('commission_type', $vehicleowner->commission_type ?? 'percentage') == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                    {{-- <option value="fixed" {{ old('commission_type', $vehicleowner->commission_type ?? '') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option> --}}
                                </select>
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-cog"></i></span>
                                </div>
                            </div>
                            <small class="text-muted">How commission is calculated</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Bank Details Section --}}
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-university mr-2"></i>Bank Details</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            Bank details are required for automated owner payouts via bank transfer.
                            Select a bank from the list below.
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Select Bank *</label>
                            <select name="bank_name" id="bankSelect" class="form-control select2" required>
                                <option value="">-- Select Bank --</option>
                                @foreach($banks ?? [] as $bank)
                                    <option value="{{ $bank->bank_name }}" 
                                        data-bank-code="{{ $bank->swift_code }}"
                                        {{ (old('bank_name', $vehicleowner->bank_name ?? '') == $bank->bank_name) ? 'selected' : '' }}>
                                        {{ $bank->bank_name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select the bank for commission payouts</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Bank Code</label>
                            <input type="text" name="bank_code" id="bankCode" class="form-control" 
                                   placeholder="Auto-filled from selected bank"
                                   value="{{ old('bank_code', $vehicleowner->bank_code ?? '') }}" readonly>
                            <small class="text-muted">Bank SWIFT/BIC code (auto-filled)</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Account Holder Name *</label>
                            <input type="text" name="bank_account_name" class="form-control" 
                                   placeholder="Name as per bank account"
                                   value="{{ old('bank_account_name', $vehicleowner->bank_account_name ?? '') }}" required>
                            <small class="text-muted">Must match the bank account holder name</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Account Number *</label>
                            <input type="text" name="bank_account_number" class="form-control" 
                                   placeholder="Bank account number"
                                   value="{{ old('bank_account_number', $vehicleowner->bank_account_number ?? '') }}" required>
                            <small class="text-muted">Valid bank account number</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Wallet Details Section --}}
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-wallet mr-2"></i>Wallet Details</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-secondary">
                            <i class="fas fa-info-circle mr-2"></i>
                            Wallet details can be used for alternative payment methods.
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Wallet Name</label>
                            <select name="wallet_name" class="form-control">
                                <option value="">-- Select Wallet --</option>
                                <option value="eSewa" {{ old('wallet_name', $vehicleowner->wallet_name ?? '') == 'eSewa' ? 'selected' : '' }}>eSewa</option>
                                <option value="Khalti" {{ old('wallet_name', $vehicleowner->wallet_name ?? '') == 'Khalti' ? 'selected' : '' }}>Khalti</option>
                            </select>
                            <small class="text-muted">Digital wallet service provider</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Wallet Number</label>
                            <input type="text" name="wallet_number" class="form-control" 
                                   placeholder="Wallet account number"
                                   value="{{ old('wallet_number', $vehicleowner->wallet_number ?? '') }}">
                            <small class="text-muted">Wallet account ID or mobile number</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<div class="card-footer text-right">
    <a href="{{ route('admin.vehicleowner.index') }}" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back
    </a>

    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i>
        {{ isset($vehicleowner) ? 'Update Vehicle Owner' : 'Add Vehicle Owner' }}
    </button>
</div>

</form>

</div>
</div>
</section>

@endsection

@section('scripts')
<!-- Select2 CSS & JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 for better dropdown experience
    $('#bankSelect').select2({
        placeholder: '-- Select Bank --',
        allowClear: true,
        width: '100%'
    });

    // Auto-fill bank code when bank is selected
    $('#bankSelect').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var bankCode = selectedOption.data('bank-code');
        var bankName = selectedOption.val();
        
        if (bankCode) {
            $('#bankCode').val(bankCode);
        } else {
            $('#bankCode').val('');
        }

        // Toggle bank details required status
        if (bankName) {
            $('#bankSelect').closest('.form-group').find('label').append(' <span class="text-danger">*</span>');
            $('input[name="bank_account_name"]').attr('required', true);
            $('input[name="bank_account_number"]').attr('required', true);
            $('#bankSelect').attr('required', true);
        } else {
            $('#bankSelect').closest('.form-group').find('label .text-danger').remove();
            $('input[name="bank_account_name"]').removeAttr('required');
            $('input[name="bank_account_number"]').removeAttr('required');
            $('#bankSelect').removeAttr('required');
        }
    });

    // Trigger change event on load to set initial state
    @if(isset($vehicleowner) && $vehicleowner->bank_name)
        $('#bankSelect').trigger('change');
    @endif

    // Auto-capitalize first letter of input fields
    $('input[name="name"]').on('blur', function() {
        var val = $(this).val();
        if (val) {
            $(this).val(val.charAt(0).toUpperCase() + val.slice(1));
        }
    });

    // Validate bank account number (numbers only)
    $('input[name="bank_account_number"]').on('keypress', function(e) {
        var charCode = e.which ? e.which : e.keyCode;
        if (charCode !== 8 && charCode !== 46 && (charCode < 48 || charCode > 57)) {
            e.preventDefault();
        }
    });

    // Validate phone number
    $('input[name="phone"]').on('keypress', function(e) {
        var charCode = e.which ? e.which : e.keyCode;
        if (charCode !== 8 && charCode !== 43 && (charCode < 48 || charCode > 57)) {
            e.preventDefault();
        }
    });

    // Show/hide sections based on status
    $('select[name="status"]').on('change', function() {
        if ($(this).val() === 'inactive') {
            $('.card-outline.card-warning').addClass('border-danger');
            $('.card-outline.card-success').addClass('opacity-50');
            $('.card-outline.card-primary').addClass('opacity-50');
        } else {
            $('.card-outline.card-warning').removeClass('border-danger');
            $('.card-outline.card-success').removeClass('opacity-50');
            $('.card-outline.card-primary').removeClass('opacity-50');
        }
    }).trigger('change');

    // Dynamic wallet name field for "Other"
    $('select[name="wallet_name"]').on('change', function() {
        if ($(this).val() === 'Other') {
            // Create input field for custom wallet name
            var customField = $(this).closest('.form-group').find('.custom-wallet-input');
            if (customField.length === 0) {
                $(this).after(
                    '<input type="text" name="custom_wallet_name" class="form-control custom-wallet-input mt-2" ' +
                    'placeholder="Enter wallet name" value="{{ old('custom_wallet_name', '') }}">'
                );
            }
        } else {
            $(this).closest('.form-group').find('.custom-wallet-input').remove();
        }
    });

    // Initialize wallet "Other" field
    @if(old('wallet_name', $vehicleowner->wallet_name ?? '') === 'Other')
        $('select[name="wallet_name"]').trigger('change');
    @endif

    // Form validation - ensure bank account details are provided if bank selected
    $('form').on('submit', function(e) {
        var bankSelected = $('#bankSelect').val();
        var accountName = $('input[name="bank_account_name"]').val();
        var accountNumber = $('input[name="bank_account_number"]').val();

        if (bankSelected && (!accountName || !accountNumber)) {
            e.preventDefault();
            toastr.error('Please provide bank account holder name and account number');
            return false;
        }
        return true;
    });
});
</script>
@endsection