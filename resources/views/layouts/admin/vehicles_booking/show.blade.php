@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Booking Details</h1>

        <a href="{{ route('admin.vehicle_bookings.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left mr-1"></i> Back
        </a>
    </div>
</div>

<section class="content">
<div class="container-fluid">

@php
$statusColor = $vehicleBooking->status == 'confirmed' ? 'success' :
               ($vehicleBooking->status == 'pending' ? 'warning' : 'danger');
@endphp


<div class="card shadow-sm border-0">
<div class="card-body">

{{-- BOOKING HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h4 class="mb-1">
{{ $vehicleBooking->vehicle->vehicle_name ?? 'Vehicle Booking' }}
</h4>

<small class="text-muted">
{{ \Carbon\Carbon::parse($vehicleBooking->start_date)->format('d M Y') }}
-
{{ \Carbon\Carbon::parse($vehicleBooking->end_date)->format('d M Y') }}
</small>

</div>

<span class="badge badge-{{ $statusColor }} px-3 py-2">
{{ ucfirst($vehicleBooking->status) }}
</span>

</div>


<div class="row">

{{-- CUSTOMER INFO --}}
<div class="col-md-6 mb-4">
<div class="card shadow-sm h-100">
<div class="card-header font-weight-bold">Customer Information</div>

<div class="card-body">

<div class="row mb-2">
<div class="col-5 text-muted">Name</div>
<div class="col-7">{{ optional($vehicleBooking->customer)->name ?? '-' }}</div>
</div>

<div class="row mb-2">
<div class="col-5 text-muted">Email</div>
<div class="col-7">{{ optional($vehicleBooking->customer)->email ?? '-' }}</div>
</div>

<div class="row">
<div class="col-5 text-muted">Phone</div>
<div class="col-7">{{ optional($vehicleBooking->customer)->phone ?? '-' }}</div>
</div>

</div>
</div>
</div>



{{-- VEHICLE INFO --}}
<div class="col-md-6 mb-4">
<div class="card shadow-sm h-100">
<div class="card-header font-weight-bold">Vehicle Information</div>

<div class="card-body">

<div class="row mb-2">
<div class="col-5 text-muted">Vehicle</div>
<div class="col-7">{{ $vehicleBooking->vehicle->vehicle_name ?? '-' }}</div>
</div>

<div class="row mb-2">
<div class="col-5 text-muted">Helper Needed</div>
<div class="col-7">
{{ $vehicleBooking->vehicle->is_helper_needed ? 'Yes' : 'No' }}
</div>
</div>

<div class="row">
<div class="col-5 text-muted">Passengers</div>
<div class="col-7">{{ $vehicleBooking->no_of_people ?? '-' }}</div>
</div>

</div>
</div>
</div>



{{-- DRIVER --}}
<div class="col-md-6 mb-4">
<div class="card shadow-sm h-100">
<div class="card-header font-weight-bold">Driver Information</div>

<div class="card-body">

<div class="row mb-2">
<div class="col-5 text-muted">Name</div>
<div class="col-7">
{{ optional(optional($vehicleBooking->driver)->user)->name ?? '-' }}
</div>
</div>

<div class="row mb-2">
<div class="col-5 text-muted">Email</div>
<div class="col-7">
{{ optional(optional($vehicleBooking->driver)->user)->email ?? '-' }}
</div>
</div>

<div class="row">
<div class="col-5 text-muted">Phone</div>
<div class="col-7">
{{ optional(optional($vehicleBooking->driver)->user)->phone ?? '-' }}
</div>
</div>

</div>
</div>
</div>



{{-- HELPER --}}
<div class="col-md-6 mb-4">
<div class="card shadow-sm h-100">
<div class="card-header font-weight-bold">Helper Information</div>

<div class="card-body">

<div class="row mb-2">
<div class="col-5 text-muted">Name</div>
<div class="col-7">
{{ optional(optional($vehicleBooking->helper)->user)->name ?? '-' }}
</div>
</div>

<div class="row mb-2">
<div class="col-5 text-muted">Email</div>
<div class="col-7">
{{ optional(optional($vehicleBooking->helper)->user)->email ?? '-' }}
</div>
</div>

<div class="row">
<div class="col-5 text-muted">Phone</div>
<div class="col-7">
{{ optional(optional($vehicleBooking->helper)->user)->phone ?? '-' }}
</div>
</div>

</div>
</div>
</div>



{{-- TRIP DETAILS --}}
<div class="col-md-12 mb-4">

<div class="card shadow-sm">
<div class="card-header font-weight-bold">Trip Details</div>

<div class="card-body">

<div class="row">

<div class="col-md-4">
<small class="text-muted">From Destination</small>
<div>{{ $vehicleBooking->from_destination ?? '-' }}</div>
</div>

<div class="col-md-4">
<small class="text-muted">To Destination</small>
<div>{{ $vehicleBooking->to_destination ?? '-' }}</div>
</div>

<div class="col-md-4">
<small class="text-muted">Passengers</small>
<div>{{ $vehicleBooking->no_of_people ?? '-' }}</div>
</div>

</div>

</div>
</div>

</div>



{{-- KM DETAILS --}}
<div class="col-md-12 mb-4">

<div class="card shadow-sm">
<div class="card-header font-weight-bold">KM & Fuel Details</div>

<div class="card-body">

<div class="row">

<div class="col-md-3">
<small class="text-muted">Start KM</small>
<div>{{ $vehicleBooking->start_km ?? '-' }}</div>
</div>

<div class="col-md-3">
<small class="text-muted">End KM</small>
<div>{{ $vehicleBooking->end_km ?? '-' }}</div>
</div>

<div class="col-md-3">
<small class="text-muted">Total KM</small>
<div>
@if($vehicleBooking->start_km && $vehicleBooking->end_km)
{{ $vehicleBooking->end_km - $vehicleBooking->start_km }}
@else
-
@endif
</div>
</div>

<div class="col-md-3">
<small class="text-muted">Fuel (Litre)</small>
<div>{{ $vehicleBooking->approx_fuel_litre ?? '-' }}</div>
</div>

</div>

</div>
</div>

</div>



{{-- PAYMENT DETAILS --}}
@if($vehicleBooking->rate_per_day)
<div class="col-md-12 mb-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light">
            <h5 class="mb-0 text-danger font-weight-bold">
                <i class="fas fa-receipt mr-2"></i> Payment Details
            </h5>
        </div>

        <div class="card-body">

            {{-- Transaction Info --}}
            <h6 class="text-muted mb-3">Transaction Information</h6>
            <div class="row mb-4">
                <div class="col-md-4">
                    <small class="text-muted">Transaction Reference</small>
                    <div class="font-weight-bold">
                        {{ $vehicleBooking->payment->transaction_reference ?? '-' }}
                    </div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted">Payment Method</small>
                    <div class="font-weight-bold">
                        {{ $vehicleBooking->payment->payment_method ?? '-' }}
                    </div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted">Payment Date</small>
                    <div class="font-weight-bold">
                        {{ $vehicleBooking->payment->payment_date ?? '-' }}
                    </div>
                </div>

                <div class="col-md-4 mt-3">
                    <small class="text-muted">Payment Status</small>
                    <div>
                        @if($vehicleBooking->payment_status == 1)
                            <span class="badge badge-success px-3 py-2">Paid</span>
                        @else
                            <span class="badge badge-warning px-3 py-2">Unpaid</span>
                        @endif
                    </div>
                </div>
            </div>


            {{-- Pricing Details --}}
            <h6 class="text-muted mb-3">Pricing Breakdown</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <tbody>
                        <tr>
                            <th width="40%">Rate Per Day</th>
                            <td>{{ $vehicleBooking->rate_per_day ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th>Sub Total</th>
                            <td>{{ $vehicleBooking->sub_total ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th>Tax ({{ $vehicleBooking->tax_amount_type ?? '-' }})</th>
                            <td>{{ $vehicleBooking->tax ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th>Discount ({{ $vehicleBooking->discount_amount_type ?? '-' }})</th>
                            <td>{{ $vehicleBooking->discount ?? '-' }}</td>
                        </tr>

                        <tr class="bg-light">
                            <th class="text-danger">Total Amount</th>
                            <td class="font-weight-bold text-danger">
                                {{ $vehicleBooking->total_amount ?? '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endif

{{-- NOTES --}}
@if($vehicleBooking->notes)

<div class="col-md-12">

<div class="card shadow-sm">
<div class="card-header font-weight-bold">Notes</div>

<div class="card-body">
{{ $vehicleBooking->notes }}
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