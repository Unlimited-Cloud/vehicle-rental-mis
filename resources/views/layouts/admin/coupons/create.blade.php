@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1 class="mb-3">
            {{ isset($coupon) ? 'Edit Coupon' : 'Create Coupon' }}
        </h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<form action="{{ isset($coupon) ? route('admin.coupons.update',$coupon->id) : route('admin.coupons.store') }}"
      method="POST">

@csrf
@if(isset($coupon)) @method('PUT') @endif

@include('layouts.admin_theme.alert')

<!-- ================= COUPON INFO ================= -->
<div class="card card-primary card-outline mb-4">
    <div class="card-header bg-primary">
        <h3 class="card-title text-white">
            <i class="fas fa-ticket-alt"></i> Coupon Information
        </h3>
    </div>

    <div class="card-body">
        <div class="row">

            <!-- Coupon Number -->
            <div class="col-md-6">
                <div class="form-group">
                    <label>Coupon Number</label>
                    <input type="text" class="form-control"
                           value="{{ $coupon->coupon_number ?? 'Auto Generated (ASH00001)' }}"
                           readonly>
                </div>
            </div>

            <!-- Petrol Pump -->
            <div class="col-md-6">
                <div class="form-group">
                    <label>Petrol Pump *</label>
                    <select name="petrol_pump_id" class="form-control" required>
                        <option value="">Select Petrol Pump</option>
                        @foreach($petrolPumps as $pump)
                            <option value="{{ $pump->id }}"
                                {{ old('petrol_pump_id',$coupon->petrol_pump_id ?? '') == $pump->id ? 'selected' : '' }}>
                                {{ $pump->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Amount -->
            <div class="col-md-6 mt-3">
                <div class="form-group">
                    <label>Amount *</label>
                    <input type="number" step="0.01" name="amount" class="form-control"
                           placeholder="Enter amount"
                           value="{{ old('amount',$coupon->amount ?? '') }}" required>
                </div>
            </div>

            <!-- Booking -->
            <div class="col-md-6 mt-3">
                <div class="form-group">
                    <label>Booking (Optional)</label>
                    <select name="booking_id" class="form-control">
                        <option value="">Select Booking (Optional)</option>
                        @foreach($bookings as $booking)
                            <option value="{{ $booking->id }}"
                                {{ old('booking_id',$coupon->booking_id ?? '') == $booking->id ? 'selected' : '' }}>
                                Booking #{{ $booking->id }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ================= SUBMIT ================= -->
<div class="card">
    <div class="card-footer text-right">
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">
            Back
        </a>

        <button type="submit" class="btn btn-primary">
            {{ isset($coupon) ? 'Update Coupon' : 'Create Coupon' }}
        </button>
    </div>
</div>

</form>

</div>
</section>

@endsection