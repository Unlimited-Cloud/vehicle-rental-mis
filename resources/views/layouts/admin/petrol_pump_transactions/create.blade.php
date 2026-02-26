{{-- resources/views/layouts/admin/petrol_pump_transactions/create.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($petrolPumpTransaction) ? 'Edit Transaction' : 'Add Transaction' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<form action="{{ isset($petrolPumpTransaction) ? route('admin.petrol_pump_transactions.update', $petrolPumpTransaction->id) : route('admin.petrol_pump_transactions.store') }}"
      method="POST">
@csrf
@if(isset($petrolPumpTransaction)) @method('PUT') @endif

<div class="card-body">
@include('layouts.admin_theme.alert')

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Petrol Pump *</label>
            <select name="petrol_pump_id" id="petrol_pump_id" class="form-control" required 
                    onchange="getPumpBalance(this.value)">
                <option value="">Select Petrol Pump</option>
                @foreach($petrolPumps as $pump)
                    <option value="{{ $pump->id }}"
                        data-balance="{{ $pump->current_balance }}"
                        data-balance-type="{{ $pump->balance_type }}"
                        {{ (old('petrol_pump_id', $petrolPumpTransaction->petrol_pump_id ?? $petrol_pump_id ?? '') == $pump->id) ? 'selected' : '' }}>
                        {{ $pump->name }} ({{ $pump->phone }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
    <div class="form-group">
        <label>Vehicle</label>
        <select name="vehicle_id" class="form-control">
            <option value="">Select Vehicle</option>
            @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}"
                    {{ (old('vehicle_id', $petrolPumpTransaction->vehicle_id ?? $vehicle_id ?? '') == $vehicle->id) ? 'selected' : '' }}>
                    {{ $vehicle->vehicle_name ?? $vehicle->brand }}
                </option>
            @endforeach
        </select>
    </div>
</div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Transaction Date *</label>
            <input type="date" name="transaction_date" class="form-control" required
                   value="{{ old('transaction_date', isset($petrolPumpTransaction) ? $petrolPumpTransaction->transaction_date->format('Y-m-d') : date('Y-m-d')) }}">
        </div>
    </div>
    <div class="col-md-4">
    <div class="form-group">
        <label>Odometer Reading</label>
        <input type="number" 
               name="odometer_reading" 
               class="form-control"
               value="{{ old('odometer_reading', $petrolPumpTransaction->odometer_reading ?? '') }}">
    </div>
</div>

    <div class="col-md-12">
        <div class="card bg-light mb-3" id="pump_balance_card" style="display: none;">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Current Balance:</strong> 
                        <span id="current_balance">₹ 0.00</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Balance Type:</strong> 
                        <span id="balance_type">-</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Credit Limit:</strong> 
                        <span id="credit_limit">₹ 0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Transaction Type *</label>
            <select name="transaction_type" id="transaction_type" class="form-control" required>
                <option value="credit" {{ (old('transaction_type', $petrolPumpTransaction->transaction_type ?? '') == 'credit') ? 'selected' : '' }}>Credit (Inbound)</option>
                <option value="debit" {{ (old('transaction_type', $petrolPumpTransaction->transaction_type ?? '') == 'debit') ? 'selected' : '' }}>Debit (Outbound)</option>
                {{-- <option value="payment" {{ (old('transaction_type', $petrolPumpTransaction->transaction_type ?? '') == 'payment') ? 'selected' : '' }}>Payment (We pay them)</option>
                <option value="payable" {{ (old('transaction_type', $petrolPumpTransaction->transaction_type ?? '') == 'payable') ? 'selected' : '' }}>Payable (Outstanding)</option> --}}
            </select>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Amount *</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required
                   value="{{ old('amount', $petrolPumpTransaction->amount ?? '') }}">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Paid Amount</label>
            <input type="number" step="0.01" name="paid_amount" id="paid_amount" class="form-control"
                   value="{{ old('paid_amount', $petrolPumpTransaction->paid_amount ?? 0) }}">
        </div>
    </div>

    {{-- <div class="col-md-4">
        <div class="form-group">
            <label>Balance</label>
            <input type="number" step="0.01" name="balance" id="balance" class="form-control" readonly
                   value="{{ old('balance', $petrolPumpTransaction->balance ?? 0) }}">
            <small class="text-muted">Auto-calculated (Amount - Paid)</small>
        </div>
    </div> --}}

    <div class="col-md-4">
        <div class="form-group">
            <label>Fuel Quantity (Liters)</label>
            <input type="number" step="0.01" name="fuel_quantity" class="form-control"
                   value="{{ old('fuel_quantity', $petrolPumpTransaction->fuel_quantity ?? '') }}">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Fuel Type</label>
            <select name="fuel_type" class="form-control">
                <option value="">Select Fuel Type</option>
                <option value="petrol" {{ (old('fuel_type', $petrolPumpTransaction->fuel_type ?? '') == 'petrol') ? 'selected' : '' }}>Petrol</option>
                <option value="diesel" {{ (old('fuel_type', $petrolPumpTransaction->fuel_type ?? '') == 'diesel') ? 'selected' : '' }}>Diesel</option>
                <option value="cng" {{ (old('fuel_type', $petrolPumpTransaction->fuel_type ?? '') == 'cng') ? 'selected' : '' }}>CNG</option>
                <option value="other" {{ (old('fuel_type', $petrolPumpTransaction->fuel_type ?? '') == 'other') ? 'selected' : '' }}>Other</option>
            </select>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Rate Per Liter</label>
            <input type="number" step="0.01" name="rate_per_liter" class="form-control"
                   value="{{ old('rate_per_liter', $petrolPumpTransaction->rate_per_liter ?? '') }}">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Payment Method</label>
            <select name="payment_method" class="form-control">
                <option value="">Select Payment Method</option>
                <option value="cash" {{ (old('payment_method', $petrolPumpTransaction->payment_method ?? '') == 'cash') ? 'selected' : '' }}>Cash</option>
                <option value="bank_transfer" {{ (old('payment_method', $petrolPumpTransaction->payment_method ?? '') == 'bank_transfer') ? 'selected' : '' }}>Bank Transfer</option>
                <option value="credit_transfer" {{ (old('payment_method', $petrolPumpTransaction->payment_method ?? '') == 'credit_transfer') ? 'selected' : '' }}>Credit Transfer</option>
                <option value="cheque" {{ (old('payment_method', $petrolPumpTransaction->payment_method ?? '') == 'cheque') ? 'selected' : '' }}>Cheque</option>
                <option value="card" {{ (old('payment_method', $petrolPumpTransaction->payment_method ?? '') == 'card') ? 'selected' : '' }}>Card</option>
                <option value="upi" {{ (old('payment_method', $petrolPumpTransaction->payment_method ?? '') == 'upi') ? 'selected' : '' }}>UPI</option>
            </select>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Reference Number</label>
            <input type="text" name="reference_number" class="form-control"
                   value="{{ old('reference_number', $petrolPumpTransaction->reference_number ?? '') }}">
            {{-- <small class="text-muted">Cheque/Transaction/UPI reference</small> --}}
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Status *</label>
            <select name="status" class="form-control" required>
                <option value="pending" {{ (old('status', $petrolPumpTransaction->status ?? '') == 'pending') ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ (old('status', $petrolPumpTransaction->status ?? '') == 'completed') ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ (old('status', $petrolPumpTransaction->status ?? '') == 'cancelled') ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label>Remarks</label>
            <textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $petrolPumpTransaction->remarks ?? '') }}</textarea>
        </div>
    </div>
</div>
</div>

<div class="card-footer text-right">
    <a href="{{ route('admin.petrol_pump_transactions.index') }}" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back
    </a>

    <button type="submit" class="btn btn-primary">
        {{ isset($petrolPumpTransaction) ? 'Update Transaction' : 'Add Transaction' }}
    </button>
</div>

</form>
</div>
</div>
</section>
@endsection

@push('scripts')
<script>
function getPumpBalance(pumpId) {
    if (!pumpId) {
        document.getElementById('pump_balance_card').style.display = 'none';
        return;
    }
    
    fetch(`/admin/petrol-pumps/${pumpId}/balance`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('current_balance').textContent = data.formatted_balance;
            document.getElementById('balance_type').innerHTML = data.balance_type == 'payable' ? 
                '<span class="badge badge-warning">Payable (We owe)</span>' : 
                '<span class="badge badge-info">Receivable (They owe)</span>';
            document.getElementById('credit_limit').textContent = '₹ ' + parseFloat(data.credit_limit).toFixed(2);
            document.getElementById('pump_balance_card').style.display = 'block';
            
            if (data.is_limit_exceeded) {
                document.getElementById('current_balance').innerHTML += ' <span class="badge badge-danger">Limit Exceeded</span>';
            }
        });
}

// Auto-calculate balance
document.getElementById('amount').addEventListener('input', calculateBalance);
document.getElementById('paid_amount').addEventListener('input', calculateBalance);

function calculateBalance() {
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const paid = parseFloat(document.getElementById('paid_amount').value) || 0;
    const balance = amount - paid;
    document.getElementById('balance').value = balance.toFixed(2);
}

// Trigger balance calculation on page load if values exist
document.addEventListener('DOMContentLoaded', function() {
    calculateBalance();
    
    // Get pump balance if already selected
    const pumpSelect = document.getElementById('petrol_pump_id');
    if (pumpSelect.value) {
        getPumpBalance(pumpSelect.value);
    }
});
</script>
@endpush