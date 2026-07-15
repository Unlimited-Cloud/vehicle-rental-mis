@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">
                <i class="fas fa-calendar-check mr-2"></i>Booking #{{ $vehicleBooking->file_no ?? 'N/A' }}
            </h1>
            <ol class="breadcrumb mt-2 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.vehicle_bookings.index') }}">Vehicle Bookings</a></li>
                <li class="breadcrumb-item active">Booking Details</li>
            </ol>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.vehicle_bookings.edit', $vehicleBooking->id) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit mr-1"></i> Edit Booking
            </a>
            <a href="{{ route('admin.vehicle_bookings.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Back to List
            </a>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @php
            $statusColor = $vehicleBooking->status == 'confirmed' ? 'success' :
                          ($vehicleBooking->status == 'pending' ? 'warning' : 
                          ($vehicleBooking->status == 'cancelled' ? 'danger' : 'secondary'));
            $statusIcon = $vehicleBooking->status == 'confirmed' ? 'fa-check-circle' :
                         ($vehicleBooking->status == 'pending' ? 'fa-clock' : 
                         ($vehicleBooking->status == 'cancelled' ? 'fa-times-circle' : 'fa-question-circle'));
        @endphp

        {{-- Booking Status Alert --}}
        <div class="alert alert-{{ $statusColor }} alert-dismissible fade show" role="alert">
            <i class="fas {{ $statusIcon }} mr-2"></i>
            <strong>Booking Status:</strong> {{ ucfirst($vehicleBooking->status) }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="row">
            {{-- Main Booking Card --}}
            <div class="col-md-8">
                {{-- Customer Information Card --}}
                <div class="card card-primary card-outline mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user mr-2"></i>Customer Information
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="40%" class="text-muted">File No.:</th>
                                        <td class="font-weight-bold">{{ $vehicleBooking->file_no ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Full Name:</th>
                                        <td>{{ optional($vehicleBooking->customer)->name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Email Address:</th>
                                        <td>
                                            @if(optional($vehicleBooking->customer)->email)
                                                <a href="mailto:{{ $vehicleBooking->customer->email }}">
                                                    {{ $vehicleBooking->customer->email }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Phone Number:</th>
                                        <td>
                                            @if(optional($vehicleBooking->customer)->phone)
                                                <a href="tel:{{ $vehicleBooking->customer->phone }}">
                                                    {{ $vehicleBooking->customer->phone }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="40%" class="text-muted">No of People:</th>
                                        <td>
                                            <span class="badge badge-info px-3 py-2">
                                                <i class="fas fa-users mr-1"></i>
                                                {{ $vehicleBooking->no_of_people ?? '0' }} Persons
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Passenger Name:</th>
                                        <td>{{ $vehicleBooking->passenger ?? '-' }}</td>
                                    </tr>
        
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Trip Details Card --}}
                <div class="card card-info card-outline mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-route mr-2"></i>Trip Details
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box bg-light p-3 mb-3">
                                    <span class="info-box-icon bg-info elevation-1">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Pickup Location</span>
                                        <span class="info-box-number font-weight-bold">
                                            {{ $vehicleBooking->from_destination ?? 'Not specified' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box bg-light p-3 mb-3">
                                    <span class="info-box-icon bg-success elevation-1">
                                        <i class="fas fa-flag-checkered"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Drop-off Location</span>
                                        <span class="info-box-number font-weight-bold">
                                            {{ $vehicleBooking->to_destination ?? 'Not specified' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                        <div class="col-md-6">
                            @if($vehicleBooking->pickup_latitude && $vehicleBooking->pickup_longitude)
                                    <small class="d-block text-muted mt-1">
                                        <i class="fas fa-globe-asia mr-1"></i>
                                        {{ $vehicleBooking->pickup_latitude }},
                                        {{ $vehicleBooking->pickup_longitude }}
                                    </small>

                                    <a href="https://www.google.com/maps?q={{ $vehicleBooking->pickup_latitude }},{{ $vehicleBooking->pickup_longitude }}"
                                    target="_blank"
                                    class="btn btn-xs btn-outline-primary mt-2">
                                        <i class="fas fa-map"></i> Open in Map
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Start Date & Time</small>
                                <strong>
                                    <i class="far fa-calendar-alt mr-1"></i>
                                    {{ \Carbon\Carbon::parse($vehicleBooking->start_date)->format('d M Y') }}
                                    <br>
                                    <i class="far fa-clock mr-1"></i>
                                    {{ \Carbon\Carbon::parse($vehicleBooking->start_date)->format('h:i A') }}
                                </strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">End Date & Time</small>
                                <strong>
                                    <i class="far fa-calendar-alt mr-1"></i>
                                    {{ \Carbon\Carbon::parse($vehicleBooking->end_date)->format('d M Y') }}
                                    <br>
                                    <i class="far fa-clock mr-1"></i>
                                    {{ \Carbon\Carbon::parse($vehicleBooking->end_date)->format('h:i A') }}
                                </strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Duration</small>
                                <strong>
                                    <i class="fas fa-hourglass-half mr-1"></i>
                                    @php
                                        $start = \Carbon\Carbon::parse($vehicleBooking->start_date);
                                        $end = \Carbon\Carbon::parse($vehicleBooking->end_date);
                                        $days = $start->diffInDays($end);
                                        $hours = $start->diffInHours($end) % 24;
                                    @endphp
                                    {{ $days }} Days {{ $hours }} Hours
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- Itinerary Card --}}
                @if($vehicleBooking->itineraries && $vehicleBooking->itineraries->count())
                <div class="card card-warning card-outline mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-map-signs mr-2"></i>Itinerary
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Day</th>
                                    <th>Date</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th class="text-right">Est. KM</th>
                                    <th class="text-right">Est. Hours</th>
                                    <th class="text-center">Overnight</th>
                                    <th class="text-right">Est. Price</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vehicleBooking->itineraries as $itinerary)
                                <tr>
                                    <td>{{ $itinerary->day_number }}</td>
                                    <td>{{ $itinerary->itinerary_date ? \Carbon\Carbon::parse($itinerary->itinerary_date)->format('d M Y') : '-' }}</td>
                                    <td>{{ $itinerary->from_destination ?? '-' }}</td>
                                    <td>{{ $itinerary->to_destination ?? '-' }}</td>
                                    <td class="text-right">{{ number_format($itinerary->est_km, 1) }}</td>
                                    <td class="text-right">{{ number_format($itinerary->est_hours, 1) }}</td>
                                    <td class="text-center">
                                        @if($itinerary->is_overnight)
                                            <span class="badge badge-info">Yes</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                    <td class="text-right">Rs. {{ number_format($itinerary->est_price, 2) }}</td>
                                    <td>{{ $itinerary->notes ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="font-weight-bold">
                                    <td colspan="4" class="text-right">Totals:</td>
                                    <td class="text-right">{{ number_format($vehicleBooking->itineraries->sum('est_km'), 1) }}</td>
                                    <td class="text-right">{{ number_format($vehicleBooking->itineraries->sum('est_hours'), 1) }}</td>
                                    <td></td>
                                    <td class="text-right">Rs. {{ number_format($vehicleBooking->itineraries->sum('est_price'), 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                @endif

                {{-- Staff Assignment Card --}}
                <div class="card card-secondary card-outline mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users-cog mr-2"></i>Staff Assignment
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-outline card-warning mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-user-tie mr-2"></i>Driver Information
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        @if($vehicleBooking->driver && optional($vehicleBooking->driver)->user)
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th width="40%">Name:</th>
                                                    <td>{{ $vehicleBooking->driver->user->name }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Email.:</th>
                                                    <td>{{ $vehicleBooking->driver->user->email ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Contact:</th>
                                                    <td>
                                                        <a href="tel:{{ $vehicleBooking->driver->contact_number }}">
                                                            {{ $vehicleBooking->driver->contact_number }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        @else
                                            <p class="text-muted mb-0">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                No driver assigned yet
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card card-outline card-success mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-user-cog mr-2"></i>Helper Information
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        @if($vehicleBooking->vehicle->is_helper_needed)
                                            @if($vehicleBooking->helper && optional($vehicleBooking->helper)->user)
                                                <table class="table table-sm table-borderless">
                                                    <tr>
                                                        <th width="40%">Name:</th>
                                                        <td>{{ $vehicleBooking->helper->user->name }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Email:</th>
                                                        <td>{{ $vehicleBooking->helper->user->email ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Contact:</th>
                                                        <td>
                                                            <a href="tel:{{ $vehicleBooking->helper->contact_number }}">
                                                                {{ $vehicleBooking->helper->contact_number }}
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </table>
                                            @else
                                                <p class="text-muted mb-0">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    No helper assigned yet
                                                </p>
                                            @endif
                                        @else
                                            <p class="text-muted mb-0">
                                                <i class="fas fa-check-circle mr-1 text-success"></i>
                                                Helper not required for this vehicle
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Sidebar --}}
            <div class="col-md-4">
                {{-- Vehicle Summary Card --}}
                <div class="card card-dark card-outline mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-car mr-2"></i>Vehicle Summary
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <i class="fas fa-truck fa-3x text-muted"></i>
                        </div>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th class="text-muted">Vehicle:</th>
                                <td class="font-weight-bold">{{ $vehicleBooking->vehicle->vehicle_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Brand:</th>
                                <td>{{ $vehicleBooking->vehicle->brand ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Vehicle Type:</th>
                                <td>{{ $vehicleBooking->vehicle->vehicle_type ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Is helper Needed:</th>
                                <td>
                                    {{ isset($vehicleBooking->vehicle->is_helper_needed) 
                                        ? ($vehicleBooking->vehicle->is_helper_needed == 1 ? 'Yes' : 'No') 
                                        : '-' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- KM & Fuel Card --}}
                <div class="card card-dark card-outline mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-tachometer-alt mr-2"></i>KM & Fuel Details
                        </h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th class="text-muted">Start KM:</th>
                                <td>{{ $vehicleBooking->start_km ? number_format($vehicleBooking->start_km) : '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">End KM:</th>
                                <td>{{ $vehicleBooking->end_km ? number_format($vehicleBooking->end_km) : '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Total Distance:</th>
                                <td class="font-weight-bold">
                                    @if($vehicleBooking->start_km && $vehicleBooking->end_km)
                                        {{ number_format($vehicleBooking->end_km - $vehicleBooking->start_km) }} km
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Fuel (Liters):</th>
                                <td>{{ $vehicleBooking->approx_fuel_litre ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Payment Summary Card --}}
                @if($vehicleBooking->rate_per_day)
                <div class="card card-dark card-outline mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-credit-card mr-2"></i>Payment Summary
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            @if($vehicleBooking->payment_status == 1)
                                <span class="badge badge-success px-4 py-2">
                                    <i class="fas fa-check-circle mr-1"></i> Paid
                                </span>
                            @else
                                <span class="badge badge-warning px-4 py-2">
                                    <i class="fas fa-clock mr-1"></i> Unpaid
                                </span>
                            @endif
                        </div>

                        <table class="table table-sm table-borderless">
                            <tr>
                                <th class="text-muted">Rate/Day:</th>
                                <td class="text-right">Rs. {{ number_format($vehicleBooking->rate_per_day, 2) }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Sub Total:</th>
                                <td class="text-right">Rs. {{ number_format($vehicleBooking->sub_total, 2) }}</td>
                            </tr>
                            @if($vehicleBooking->tax > 0)
                            <tr>
                                <th class="text-muted">Tax ({{ $vehicleBooking->tax_amount_type }}):</th>
                                <td class="text-right">Rs. {{ number_format($vehicleBooking->tax, 2) }}</td>
                            </tr>
                            @endif
                            @if($vehicleBooking->discount > 0)
                            <tr>
                                <th class="text-muted">Discount ({{ $vehicleBooking->discount_amount_type }}):</th>
                                <td class="text-right text-success">- Rs. {{ number_format($vehicleBooking->discount, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="border-top">
                                <th class="text-danger">Total Amount:</th>
                                <td class="text-right font-weight-bold text-danger">
                                    Rs. {{ number_format($vehicleBooking->total_amount, 2) }}
                                </td>
                            </tr>
                        </table>

                        @if($vehicleBooking->payment)
                        <hr>
                        <h6 class="text-muted">Transaction Details</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th class="text-muted">Reference:</th>
                                <td>{{ $vehicleBooking->payment->transaction_reference ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Method:</th>
                                <td>{{ $vehicleBooking->payment->payment_method ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Date:</th>
                                <td>{{ $vehicleBooking->payment->payment_date ?? '-' }}</td>
                            </tr>
                        </table>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Notes Card --}}
                @if($vehicleBooking->notes)
                <div class="card card-dark card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-sticky-note mr-2"></i>Additional Notes
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $vehicleBooking->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .card-outline {
        border-top: 3px solid;
    }
    .card-primary.card-outline {
        border-top-color: #007bff;
    }
    .card-info.card-outline {
        border-top-color: #17a2b8;
    }
    .card-secondary.card-outline {
        border-top-color: #6c757d;
    }
    .card-dark.card-outline {
        border-top-color: #343a40;
    }
    .info-box {
        min-height: 100px;
        border-radius: 0.25rem;
    }
    .gap-2 {
        gap: 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
    $(function() {
        // Initialize card widgets
        $('[data-card-widget="collapse"]').click(function() {
            $(this).closest('.card').find('.card-body').slideToggle();
        });
    });
</script>
@endpush