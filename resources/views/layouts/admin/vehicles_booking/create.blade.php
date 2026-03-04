@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1>
            {{ isset($booking) ? 'Edit Booking' : 'Add Booking' }}
        </h1>

        <a href="{{ route('admin.vehicle_bookings.index') }}"
           class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<section class="content">
<div class="container-fluid">
@include('layouts.admin_theme.alert')

<div class="card card-primary card-outline">
<form method="POST"
      action="{{ isset($booking)
          ? route('admin.vehicle_bookings.update', $booking->id)
          : route('admin.vehicle_bookings.store') }}">

@csrf
@if(isset($booking))
    @method('PUT')
@endif

<div class="card-body">
<div class="row">

{{-- VEHICLE --}}
<div class="col-md-4">
    <div class="form-group">
        <label>Vehicle *</label>
        <select name="vehicle_id" class="form-control" required>
            <option value="">Select Vehicle</option>
            @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}"
                    {{ old('vehicle_id',
                        $booking->vehicle_id ?? '') == $vehicle->id ? 'selected' : '' }}>
                    {{ $vehicle->vehicle_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label>Driver</label>
        <select name="driver_id" class="form-control">
            <option value="">Select Driver</option>
            @foreach($drivers as $driver)
                <option value="{{ $driver->id }}"
                    {{ old('driver_id', $booking->driver_id ?? '') == $driver->id ? 'selected' : '' }}>
                    {{ $driver->user->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label>Helper</label>
       <select name="helper_id" class="form-control">
    <option value="">Select Helper</option>
    @foreach($helpers as $helper)
        <option value="{{ $helper->id }}"
            {{ old('helper_id', $booking->helper_id ?? '') == $helper->id ? 'selected' : '' }}>
            {{ $helper->user->name }}
        </option>
    @endforeach
</select>
    </div>
</div>
<div class="col-md-4">
<div class="form-group">
      <label>Customer</label>
<select name="customer_id" class="form-control">
    <option value="">Select Customer</option>
    @foreach($customers as $customer)
        <option value="{{ $customer->id }}"
            {{ old('customer_id', $booking->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
            {{ $customer->name }}
        </option>
    @endforeach
</select>
</div>
</div>







{{-- FROM --}}
<div class="col-md-4">
    <div class="form-group">
        <label>From Destination</label>
        <input type="text" name="from_destination"
               value="{{ old('from_destination', $booking->from_destination ?? '') }}"
               class="form-control">
    </div>
</div>

{{-- TO --}}
<div class="col-md-4">
    <div class="form-group">
        <label>To Destination</label>
        <input type="text" name="to_destination"
               value="{{ old('to_destination', $booking->to_destination ?? '') }}"
               class="form-control">
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <label>Start K/M</label>
        <input type="text" name="start_km"
               value="{{ old('start_km', $booking->start_km ?? '') }}"
               class="form-control">
    </div>
</div>

{{-- TO --}}
<div class="col-md-4">
    <div class="form-group">
        <label>End K/M</label>
        <input type="text" name="end_km"
               value="{{ old('end_km', $booking->end_km ?? '') }}"
               class="form-control">
    </div>
</div>

{{-- PEOPLE --}}
<div class="col-md-4">
    <div class="form-group">
        <label>No. of People</label>
        <input type="number" name="no_of_people"
               value="{{ old('no_of_people', $booking->no_of_people ?? '') }}"
               class="form-control">
    </div>
</div>

{{-- START DATE --}}
<div class="col-md-3">
    <div class="form-group">
        <label>Start Date *</label>
        <input type="date"
               name="start_date"
               value="{{ old('start_date',
                    $booking->start_date ?? $start ?? '') }}"
               class="form-control"
               required>
        
    </div>
</div>
<div class="col-md-2">
    <div class="form-group">
        <label>Start Time *</label>
        <input type="time"
               name="start_time"
               value="{{ old('start_time',
                    $booking->start_time ?? $start ?? '') }}"
               class="form-control"
               required>
    </div>
</div>

{{-- END DATE --}}
<div class="col-md-3">
    <div class="form-group">
        <label>End Date *</label>
        <input type="date"
               name="end_date"
               value="{{ old('end_date',
                    $booking->end_date ?? $end ?? '') }}"
               class="form-control"
               required>
    </div>
</div>
<div class="col-md-2">
    <div class="form-group">
        <label>END Time *</label>
        <input type="time"
               name="end_time"
               value="{{ old('end_time',
                    $booking->end_time ?? $start ?? '') }}"
               class="form-control"
               required>
    </div>
</div>

<div class="col-md-2">
    <div class="form-group">
        <label>No. of Hours</label>
        <input type="number" name="no_of_hours"
               value="{{ old('no_of_hours', $booking->no_of_hours ?? '') }}"
               class="form-control">
    </div>
</div>

{{-- STATUS --}}
<div class="col-md-4">
    <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="pending"
                {{ old('status', $booking->status ?? '') == 'pending' ? 'selected' : '' }}>
                Pending
            </option>
            <option value="confirmed"
                {{ old('status', $booking->status ?? '') == 'confirmed' ? 'selected' : '' }}>
                Confirmed
            </option>
            <option value="cancelled"
                {{ old('status', $booking->status ?? '') == 'cancelled' ? 'selected' : '' }}>
                Cancelled
            </option>
        </select>
    </div>
</div>

<div class="col-md-12">
    <div class="form-group">
        <label>Approx fuel Litre</label>
        <input name="approx_fuel_litre" type="number"
               value="{{ old('approx_fuel_litre', $booking->approx_fuel_litre ?? '') }}"
               class="form-control">
    </div>
</div>

{{-- NOTES --}}
<div class="col-md-12">
    <div class="form-group">
        <label>Notes</label>
        <textarea name="notes" rows="3"
                  class="form-control">{{ old('notes', $booking->notes ?? '') }}</textarea>
    </div>
</div>

</div>
</div>

<div class="card-footer text-right">
    <button type="submit" class="btn btn-primary">
        {{ isset($booking) ? 'Update Booking' : 'Add Booking' }}
    </button>
</div>

</form>
</div>
</div>
</section>

@endsection