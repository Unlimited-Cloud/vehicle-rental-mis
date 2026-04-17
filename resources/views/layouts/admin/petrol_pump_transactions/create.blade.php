{{-- resources/views/layouts/admin/petrol_pump_transactions/create.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="m-0">
                <i class="fas {{ isset($petrolPumpTransaction) ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                {{ isset($petrolPumpTransaction) ? 'Edit Transaction' : 'Add Transaction' }}
            </h1>
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.petrol_pump_transactions.index') }}">Transactions</a></li>
                <li class="breadcrumb-item active">{{ isset($petrolPumpTransaction) ? 'Edit' : 'Create' }}</li>
            </ol>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-gas-pump mr-2"></i>
                    Transaction Details
                </h3>
            </div>

            <form action="{{ isset($petrolPumpTransaction) ? route('admin.petrol_pump_transactions.update', $petrolPumpTransaction->id) : route('admin.petrol_pump_transactions.store') }}"
                  method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($petrolPumpTransaction)) @method('PUT') @endif

                <div class="card-body">
                    @include('layouts.admin_theme.alert')

                    {{-- Pump Selection & Balance Card --}}
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="required">Petrol Pump <span class="text-danger">*</span></label>
                                <select name="petrol_pump_id" id="petrol_pump_id" class="form-control select2" required 
                                        >
                                    <option value="">-- Select Petrol Pump --</option>
                                    @foreach($petrolPumps as $pump)
                                        <option value="{{ $pump->id }}"
                                            data-balance="{{ $pump->current_balance }}"
                                            data-balance-type="{{ $pump->balance_type }}"
                                            data-credit-limit="{{ $pump->credit_limit }}"
                                            {{ (old('petrol_pump_id', $petrolPumpTransaction->petrol_pump_id ?? $petrol_pump_id ?? '') == $pump->id) ? 'selected' : '' }}>
                                            {{ $pump->name }} ({{ $pump->phone }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Transaction Date <span class="text-danger">*</span></label>
                                <input type="date" name="transaction_date" class="form-control" required
                                       value="{{ old('transaction_date', isset($petrolPumpTransaction) ? $petrolPumpTransaction->transaction_date->format('Y-m-d') : date('Y-m-d')) }}">
                            </div>
                        </div>
                    </div>

                    {{-- Pump Balance Card --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="card bg-gradient-light mb-4" id="pump_balance_card" style="display: none;">
                                <div class="card-header border-0">
                                    <h3 class="card-title">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Pump Balance Information
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="info-box bg-white">
                                                <span class="info-box-icon bg-info">
                                                    <i class="fas fa-wallet"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Current Balance</span>
                                                    <span class="info-box-number" id="current_balance">₹ 0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-white">
                                                <span class="info-box-icon bg-warning">
                                                    <i class="fas fa-balance-scale"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Balance Type</span>
                                                    <span class="info-box-number" id="balance_type">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-white">
                                                <span class="info-box-icon bg-success">
                                                    <i class="fas fa-credit-card"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Credit Limit</span>
                                                    <span class="info-box-number" id="credit_limit">₹ 0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-white">
                                                <span class="info-box-icon bg-danger">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Available Credit</span>
                                                    <span class="info-box-number" id="available_credit">₹ 0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Main Form Row 1: Vehicle & Driver --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Vehicle</label>
                                <select name="vehicle_id" class="form-control select2">
                                    <option value="">-- Select Vehicle --</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}"
                                            {{ (old('vehicle_id', $petrolPumpTransaction->vehicle_id ?? $vehicle_id ?? '') == $vehicle->id) ? 'selected' : '' }}>
                                            {{ $vehicle->vehicle_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Driver</label>

                            <select name="driver_id"
                                    class="form-control select2"
                                    {{ $currentUserIsDriver == 'Y' ? 'readonly disabled' : '' }}>

                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}"
                                        {{ (old('driver_id', $petrolPumpTransaction->driver_id ?? ($currentUserIsDriver == 'Y' ? $drivers->first()->id : '')) == $driver->id) ? 'selected' : '' }}>
                                        
                                        {{ $driver->user->name }} - {{ $driver->license_number ?? '' }}
                                    </option>
                                @endforeach

                            </select>

                            {{-- hidden field to ensure value is submitted when disabled --}}
                            @if($currentUserIsDriver == 'Y')
                                <input type="hidden" name="driver_id" value="{{ $drivers->first()->id }}">
                            @endif

                        </div>
                    </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Odometer Reading (KM)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-tachometer-alt"></i>
                                        </span>
                                    </div>
                                    <input type="number" name="odometer_reading" class="form-control" 
                                           value="{{ old('odometer_reading', $petrolPumpTransaction->odometer_reading ?? '') }}"
                                           placeholder="Enter odometer reading">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Main Form Row 2: Fuel Details --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fuel Type</label>
                                <select name="fuel_type" class="form-control">
                                    <option value="">-- Select Fuel Type --</option>
                                    <option value="diesel" 
                                        {{ old('fuel_type', $petrolPumpTransaction->fuel_type ?? 'diesel') == 'diesel' ? 'selected' : '' }}>
                                        Diesel
                                    </option>

                                      <option value="petrol" 
                                        {{ old('fuel_type', $petrolPumpTransaction->fuel_type ?? 'diesel') == 'petrol' ? 'selected' : '' }}>
                                        Petrol
                                    </option>

                                    <option value="cng" 
                                        {{ old('fuel_type', $petrolPumpTransaction->fuel_type ?? 'diesel') == 'cng' ? 'selected' : '' }}>
                                        CNG
                                    </option>

                                    <option value="other" 
                                        {{ old('fuel_type', $petrolPumpTransaction->fuel_type ?? 'diesel') == 'other' ? 'selected' : '' }}>
                                        Other
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fuel Quantity (Liters)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-oil-can"></i>
                                        </span>
                                    </div>
                                    <input type="number" step="0.01" name="fuel_quantity" id="fuel_quantity" class="form-control"
                                           value="{{ old('fuel_quantity', $petrolPumpTransaction->fuel_quantity ?? '') }}"
                                           placeholder="Enter quantity in liters">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Rate Per Liter (₹)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">₹</span>
                                    </div>
                                    <input type="number" step="0.01" name="rate_per_liter" id="rate_per_liter" class="form-control"
                                           value="{{ old('rate_per_liter', $petrolPumpTransaction->rate_per_liter ?? '') }}"
                                           placeholder="Enter rate per liter">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Main Form Row 3: Transaction Details --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Transaction Type <span class="text-danger">*</span></label>
                                <select name="transaction_type" id="transaction_type" class="form-control" required>
                                    <option value="credit" {{ (old('transaction_type', $petrolPumpTransaction->transaction_type ?? '') == 'credit') ? 'selected' : '' }}>
                                        <i class="fas fa-arrow-down text-success"></i> Credit (Inbound)
                                    </option>
                                    <option value="debit" {{ (old('transaction_type', $petrolPumpTransaction->transaction_type ?? '') == 'debit') ? 'selected' : '' }}>
                                        <i class="fas fa-arrow-up text-danger"></i> Debit (Outbound)
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Amount (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">₹</span>
                                    </div>
                                    <input type="number" step="0.01" name="amount" id="amount" class="form-control" required
                                           value="{{ old('amount', $petrolPumpTransaction->amount ?? '') }}"
                                           placeholder="Auto-calculated">
                                </div>
                                {{-- <small class="text-muted">Auto-calculated from quantity × rate</small> --}}
                            </div>
                        </div>
                    </div>

                    {{-- Main Form Row 4: Balance Display --}}
                    <div class="row">
                       
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Payment Method</label>
                                <select name="payment_method" class="form-control">
                                    <option value="">-- Select Payment Method --</option>
                                    <option value="cash" {{ (old('payment_method', $petrolPumpTransaction->payment_method ?? '') == 'cash') ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ (old('payment_method', $petrolPumpTransaction->payment_method ?? '') == 'bank_transfer') ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="credit_transfer" {{ (old('payment_method', $petrolPumpTransaction->payment_method ?? '') == 'credit_transfer') ? 'selected' : '' }}>Credit Transfer</option>
                                    <option value="cheque" {{ (old('payment_method', $petrolPumpTransaction->payment_method ?? '') == 'cheque') ? 'selected' : '' }}>Cheque</option>
                                    <option value="card" {{ (old('payment_method', $petrolPumpTransaction->payment_method ?? '') == 'card') ? 'selected' : '' }}>Card</option>
                                    {{-- <option value="upi" {{ (old('payment_method', $petrolPumpTransaction->payment_method ?? '') == 'upi') ? 'selected' : '' }}>UPI</option> --}}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Reference Number</label>
                                <input type="text" name="reference_number" class="form-control"
                                       value="{{ old('reference_number', $petrolPumpTransaction->reference_number ?? '') }}"
                                       placeholder="Coupon Number">
                            </div>
                        </div>
                    </div>

                    {{-- Main Form Row 5: Status --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" required>
                                    <option value="completed" {{ (old('status', $petrolPumpTransaction->status ?? '') == 'completed') ? 'selected' : '' }}>Completed</option>
                                    <option value="pending" {{ (old('status', $petrolPumpTransaction->status ?? '') == 'pending') ? 'selected' : '' }}>Pending</option>
                                    <option value="cancelled" {{ (old('status', $petrolPumpTransaction->status ?? '') == 'cancelled') ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2" 
                                          placeholder="Enter any additional notes or remarks">{{ old('remarks', $petrolPumpTransaction->remarks ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Image Upload Section --}}
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card card-outline card-secondary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-images mr-2"></i>
                                        Supporting Images
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Pump Before Image</label>
                                                <div class="custom-file">
                                                    <input type="file" name="pump_before" class="custom-file-input" id="pump_before">
                                                    <label class="custom-file-label" for="pump_before">Choose file</label>
                                                </div>
                                                @if(isset($petrolPumpTransaction) && $petrolPumpTransaction->pump_before)
                                                    <small class="text-muted d-block mt-1">
                                                        <a href="{{ asset($petrolPumpTransaction->pump_before) }}" target="_blank" class="btn btn-sm btn-info mt-2">
                                                            <i class="fas fa-eye"></i> View Current
                                                        </a>
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Pump After Image</label>
                                                <div class="custom-file">
                                                    <input type="file" name="pump_after" class="custom-file-input" id="pump_after">
                                                    <label class="custom-file-label" for="pump_after">Choose file</label>
                                                </div>
                                                @if(isset($petrolPumpTransaction) && $petrolPumpTransaction->pump_after)
                                                    <small class="text-muted d-block mt-1">
                                                        <a href="{{ asset($petrolPumpTransaction->pump_after) }}" target="_blank" class="btn btn-sm btn-info mt-2">
                                                            <i class="fas fa-eye"></i> View Current
                                                        </a>
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Tank Before Image</label>
                                                <div class="custom-file">
                                                    <input type="file" name="tank_before" class="custom-file-input" id="tank_before">
                                                    <label class="custom-file-label" for="tank_before">Choose file</label>
                                                </div>
                                                @if(isset($petrolPumpTransaction) && $petrolPumpTransaction->tank_before)
                                                    <small class="text-muted d-block mt-1">
                                                        <a href="{{ asset($petrolPumpTransaction->tank_before) }}" target="_blank" class="btn btn-sm btn-info mt-2">
                                                            <i class="fas fa-eye"></i> View Current
                                                        </a>
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Tank After Image</label>
                                                <div class="custom-file">
                                                    <input type="file" name="tank_after" class="custom-file-input" id="tank_after">
                                                    <label class="custom-file-label" for="tank_after">Choose file</label>
                                                </div>
                                                @if(isset($petrolPumpTransaction) && $petrolPumpTransaction->tank_after)
                                                    <small class="text-muted d-block mt-1">
                                                        <a href="{{ asset($petrolPumpTransaction->tank_after) }}" target="_blank" class="btn btn-sm btn-info mt-2">
                                                            <i class="fas fa-eye"></i> View Current
                                                        </a>
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <a href="{{ route('admin.petrol_pump_transactions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                    <button type="reset" class="btn btn-warning">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>
                        {{ isset($petrolPumpTransaction) ? 'Update Transaction' : 'Save Transaction' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
$(document).ready(function () {

    function calculateAmount() {
        let liters = parseFloat($('#fuel_quantity').val()) || 0;
        let rate   = parseFloat($('#rate_per_liter').val()) || 0;
        let type   = $('#transaction_type').val();

        let amount = 0;

        if (type === 'credit' && liters > 0 && rate > 0) {
            amount = liters * rate;
            $('#amount').prop('readonly', true);
        } else {
            $('#amount').prop('readonly', false);
        }

        $('#amount').val(amount.toFixed(2));
    }

    // 🔥 IMPORTANT: use event delegation (works always)
    $(document).on('input', '#fuel_quantity, #rate_per_liter', calculateAmount);
    $(document).on('change', '#transaction_type', calculateAmount);

    // Run once
    calculateAmount();
});
</script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .required:after {
        content: " *";
        color: red;
    }
    .select2-container .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    .info-box {
        min-height: 100px;
        border-radius: 0.25rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .info-box-icon {
        border-radius: 0.25rem 0 0 0.25rem;
    }
    .custom-file-label::after {
        content: "Browse";
    }

</style>

@endsection