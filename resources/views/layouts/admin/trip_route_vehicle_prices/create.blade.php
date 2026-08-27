@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>
            {{ isset($price) ? 'Edit Vehicle Route Price' : 'Create Vehicle Route Price' }}
        </h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form
            action="{{ isset($price)
                ? route('admin.trip-routes-vehicle-prices.update', $price->id)
                : route('admin.trip-routes-vehicle-prices.store') }}"
            method="POST">

            @csrf
            @if(isset($price))
                @method('PUT')
            @endif

            @include('layouts.admin_theme.alert')

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <!-- Vehicle Selection -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vehicle <span class="text-danger">*</span></label>
                                <select name="vehicle_id" class="form-control" required>
                                    <option value="">Select Vehicle</option>
                                    @foreach($vehicles as $id => $name)
                                        <option value="{{ $id }}"
                                            {{ old('vehicle_id', $price->vehicle_id ?? '') == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('vehicle_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Per KM Price -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Price Per KM (Rs)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="per_km"
                                    class="form-control"
                                    placeholder="Enter price per kilometer"
                                    value="{{ old('per_km', $price->per_km ?? '') }}">
                                @error('per_km')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Per Hour Price -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Price Per Hour (Rs)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="per_hour"
                                    class="form-control"
                                    placeholder="Enter price per hour"
                                    value="{{ old('per_hour', $price->per_hour ?? '') }}">
                                @error('per_hour')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <!-- Overnight Stay -->

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Overnight Charge (Rs)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="price"
                                    class="form-control"
                                    placeholder="Enter overnight charge"
                                    value="{{ old('price', $price->price ?? '') }}">
                                @error('price')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Overnight Stay -->
                        {{-- <div class="col-md-6">
                            <div class="form-group">
                                <label>Overnight Stay Charge</label>
                                <div class="custom-control custom-switch mt-2">
                                    <input
                                        type="checkbox"
                                        name="overnight"
                                        class="custom-control-input"
                                        id="overnightSwitch"
                                        value="1"
                                        {{ old('overnight', $price->overnight ?? false) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="overnightSwitch">
                                        {{ old('overnight', $price->overnight ?? false) ? 'Yes' : 'No' }}
                                    </label>
                                </div>
                                <small class="form-text text-muted">Check if overnight stay charges apply</small>
                                @error('overnight')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div> --}}

                        <!-- Note: trip_route_id is null for global vehicle prices -->
                        <div class="col-md-12">
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle"></i>
                                <strong>Note:</strong> This price will be applied globally to all routes for the selected vehicle.
                                To set route-specific prices, please use the route-wise pricing section.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <a href="{{ route('admin.trip-routes-vehicle-prices.index') }}"
                       class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        {{ isset($price) ? 'Update Price' : 'Save Price' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle overnight switch label
    $('#overnightSwitch').change(function() {
        if ($(this).is(':checked')) {
            $(this).closest('.custom-control').find('.custom-control-label').text('Yes');
        } else {
            $(this).closest('.custom-control').find('.custom-control-label').text('No');
        }
    });
});
</script>
@endpush

@endsection