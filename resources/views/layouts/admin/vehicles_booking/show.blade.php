@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Booking Details</h1>
        <a href="{{ route('admin.vehicle_bookings.index') }}" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<div class="card shadow-sm border-0">
<div class="card-body">

    {{-- STATUS --}}
    @php
        $statusColor = $vehicleBooking->status == 'confirmed' ? 'success' :
                       ($vehicleBooking->status == 'pending' ? 'warning' : 'danger');
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            {{ $vehicleBooking->vehicle->vehicle_name ?? 'Vehicle' }}
        </h4>
        <span class="badge badge-{{ $statusColor }} px-3 py-2">
            {{ ucfirst($vehicleBooking->status) }}
        </span>
    </div>

    <div class="row">

        {{-- CUSTOMER --}}
        <div class="col-md-6 mb-4">
            <div class="card border-left-primary shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-primary font-weight-bold mb-3">Customer Info</h6>
                    <p><strong>Name:</strong> {{ optional($vehicleBooking->customer)->name ?? '-' }}</p>
                    <p><strong>Email:</strong> {{ optional($vehicleBooking->customer)->email ?? '-' }}</p>
                    <p><strong>Phone:</strong> {{ optional($vehicleBooking->customer)->phone ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- VEHICLE --}}
        <div class="col-md-6 mb-4">
            <div class="card border-left-info shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-info font-weight-bold mb-3">Vehicle Info</h6>
                    <p><strong>Vehicle:</strong> {{ $vehicleBooking->vehicle->vehicle_name ?? '-' }}</p>
                    <p><strong>Helper Needed:</strong> {{ $vehicleBooking->vehicle->is_helper_needed ? 'Yes' : 'No' }}</p>
                    <p><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($vehicleBooking->start_date)->format('d M Y') }}</p>
                    <p><strong>End Date:</strong> {{ \Carbon\Carbon::parse($vehicleBooking->end_date)->format('d M Y') }}</p>
                </div>
            </div>
        </div>

        {{-- DRIVER --}}
        <div class="col-md-6 mb-4">
            <div class="card border-left-success shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-success font-weight-bold mb-3">Driver Info</h6>
                    <p><strong>Name:</strong> {{ optional(optional($vehicleBooking->driver)->user)->name ?? '-' }}</p>
                    <p><strong>Email:</strong> {{ optional(optional($vehicleBooking->driver)->user)->email ?? '-' }}</p>
                    <p><strong>Phone:</strong> {{ optional(optional($vehicleBooking->driver)->user)->phone ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- HELPER --}}
        <div class="col-md-6 mb-4">
            <div class="card border-left-dark shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-dark font-weight-bold mb-3">Helper Info</h6>
                    <p><strong>Name:</strong> {{ optional(optional($vehicleBooking->helper)->user)->name ?? '-' }}</p>
                    <p><strong>Email:</strong> {{ optional(optional($vehicleBooking->helper)->user)->email ?? '-' }}</p>
                    <p><strong>Phone:</strong> {{ optional(optional($vehicleBooking->helper)->user)->phone ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- TRIP DETAILS --}}
        <div class="col-md-12 mb-4">
            <div class="card border-left-warning shadow-sm">
                <div class="card-body">
                    <h6 class="text-warning font-weight-bold mb-3">Trip Details</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>From:</strong> {{ $vehicleBooking->from_destination ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>To:</strong> {{ $vehicleBooking->to_destination ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>No. of People:</strong> {{ $vehicleBooking->no_of_people ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KM & FUEL --}}
        <div class="col-md-12 mb-4">
            <div class="card border-left-secondary shadow-sm">
                <div class="card-body">
                    <h6 class="text-secondary font-weight-bold mb-3">KM & Fuel Details</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <p><strong>Start KM:</strong> {{ $vehicleBooking->start_km ?? '-' }}</p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>End KM:</strong> {{ $vehicleBooking->end_km ?? '-' }}</p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Total KM:</strong>
                                @if($vehicleBooking->start_km && $vehicleBooking->end_km)
                                    {{ $vehicleBooking->end_km - $vehicleBooking->start_km }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Fuel (Litre):</strong> {{ $vehicleBooking->approx_fuel_litre ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- NOTES --}}
        @if($vehicleBooking->notes)
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="font-weight-bold mb-2">Notes</h6>
                    <p class="mb-0">{{ $vehicleBooking->notes }}</p>
                </div>
            </div>
        </div>
        @endif

    </div>

</div>
</div>

</div>
</section>
@endsection