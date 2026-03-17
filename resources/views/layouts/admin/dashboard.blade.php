@extends('layouts.admin_theme.container')

@section('dynamicdata')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-3 align-items-center">
            <div class="col-sm-12">
                <div class="d-flex align-items-center p-3 rounded shadow-sm bg-white">
                    <div class="mr-3">
                        <i class="fas fa-tachometer-alt text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h5 class="m-0 font-weight-bold text-dark">
                            Welcome to <span class="text-primary">Vehicle Rental Pvt Ltd</span>
                        </h5>
                        <small class="text-muted" style="font-size: 0.8rem;">
                            Dashboard overview & system insights
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Summary Section -->
        <div class="row">
            <div class="col-12">
                <h6 class="mb-3 font-weight-bold text-uppercase text-muted">
                    <i class="fas fa-chart-pie mr-2" style="font-size: 0.9rem;"></i>Summary
                </h6>
            </div>
        </div>

        <!-- First Row - Vehicles & Customers -->
        <div class="row">
            <!-- Total Vehicles -->
            @if($currentUserIsCustomer == 'N')
            <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                <a href="{{ route('admin.vehicles.index') }}" style="text-decoration: none;">
                    <div class="small-box bg-info" style="border-radius: 8px; min-height: 100px;">
                        <div class="inner">
                            <h4 class="mb-2 font-weight-bold">{{ $totalVehicles }}</h4>
                            <p class="mb-1" style="font-size: 0.85rem; font-weight: 500;">Total Vehicles</p>
                            <small style="font-size: 0.7rem;">
                                <span class="text-white">{{ $availableVehicles }}</span> Available | 
                                <span class="text-white">{{ $unavailableVehicles }}</span> Unavailable
                            </small>
                        </div>
                        <div class="icon">
                            <i class="fas fa-truck" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </a>
            </div>
            @endif

            @if($currentUserIsCustomer == 'N')
            <!-- Total Customers -->
            <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                <a href="{{ route('admin.customers.index') }}" style="text-decoration: none;">
                    <div class="small-box bg-success" style="border-radius: 8px; min-height: 100px;">
                        <div class="inner">
                            <h4 class="mb-2 font-weight-bold">{{ $totalCustomers }}</h4>
                            <p class="mb-1" style="font-size: 0.85rem; font-weight: 500;">Total Customers</p>
                            <small style="font-size: 0.7rem;">Registered Customers</small>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-tie" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </a>
            </div>
            @endif

            <!-- Total Bookings -->
            <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                <a href="{{ route('admin.vehicle_bookings.index') }}" style="text-decoration: none;">
                    <div class="small-box bg-warning" style="border-radius: 8px; min-height: 100px;">
                        <div class="inner">
                            <h4 class="mb-2 font-weight-bold">{{ $totalBookings }}</h4>
                            <p class="mb-1" style="font-size: 0.85rem; font-weight: 500;">Total Bookings</p>
                            <small style="font-size: 0.7rem;">
                                <span class="text-white">{{ $activeBookings }}</span> Active | 
                                <span class="text-white">{{ $pendingBookings }}</span> Pending
                            </small>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-check" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </a>
            </div>

            @if($currentUserIsCustomer == 'N')
            <!-- Total Petrol Pumps -->
            <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                <a href="{{ route('admin.petrol_pumps.index') }}" style="text-decoration: none;">
                    <div class="small-box bg-danger" style="border-radius: 8px; min-height: 100px;">
                        <div class="inner">
                            <h4 class="mb-2 font-weight-bold">{{ $totalPetrolPumps }}</h4>
                            <p class="mb-1" style="font-size: 0.85rem; font-weight: 500;">Petrol Pumps</p>
                            <small style="font-size: 0.7rem;">Registered Petrol Pumps</small>
                        </div>
                        <div class="icon">
                            <i class="fas fa-gas-pump" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </a>
            </div>
            @endif
        </div>

        @if($currentUserIsCustomer == 'N')
        <!-- Second Row - Crew Members -->
        <div class="row mt-2">
            <div class="col-12">
                <h6 class="mb-3 font-weight-bold text-uppercase text-muted">
                    <i class="fas fa-users mr-2" style="font-size: 0.9rem;"></i>Crew Management
                </h6>
            </div>

            <!-- Total Crew -->
            <div class="col-lg-4 col-md-4 col-sm-6 col-12 mb-3">
                <a href="{{ route('admin.crew_profiles.index') }}" style="text-decoration: none;">
                    <div class="small-box bg-secondary" style="border-radius: 8px; min-height: 100px;">
                        <div class="inner">
                            <h4 class="mb-2 font-weight-bold">{{ $totalCrew }}</h4>
                            <p class="mb-1" style="font-size: 0.85rem; font-weight: 500;">Total Crew</p>
                            <small style="font-size: 0.7rem;">All Staff Members</small>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users-cog" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Total Drivers -->
            <div class="col-lg-4 col-md-4 col-sm-6 col-12 mb-3">
                <a href="{{ route('admin.crew_profiles.index', ['role' => 'driver']) }}" style="text-decoration: none;">
                    <div class="small-box bg-primary" style="border-radius: 8px; min-height: 100px;">
                        <div class="inner">
                            <h4 class="mb-2 font-weight-bold">{{ $totalDrivers }}</h4>
                            <p class="mb-1" style="font-size: 0.85rem; font-weight: 500;">Total Drivers</p>
                            <small style="font-size: 0.7rem;">Licensed Drivers</small>
                        </div>
                        <div class="icon">
                            <i class="fas fa-id-card" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Total Helpers -->
            <div class="col-lg-4 col-md-4 col-sm-6 col-12 mb-3">
                <a href="{{ route('admin.crew_profiles.index', ['role' => 'helper']) }}" style="text-decoration: none;">
                    <div class="small-box" style="border-radius: 8px; min-height: 100px;">
                        <div class="inner">
                            <h4 class="mb-2 font-weight-bold">{{ $totalHelpers }}</h4>
                            <p class="mb-1" style="font-size: 0.85rem; font-weight: 500;">Total Helpers</p>
                            <small style="font-size: 0.7rem;">Support Staff</small>
                        </div>
                        <div class="icon">
                            <i class="fas fa-hard-hat" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        @endif

        <!-- Recent Bookings Section -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h6 class="card-title font-weight-bold">
                            <i class="fas fa-clock mr-1" style="font-size: 0.9rem;"></i>Recent Bookings
                        </h6>
                        <div class="card-tools">
                            <span class="badge badge-primary">{{ $recentBookings ? $recentBookings->count() : 0 }} Recent</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-sm mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="font-size: 0.75rem; font-weight: 600;">#</th>
                                        <th style="font-size: 0.75rem; font-weight: 600;">Vehicle</th>
                                        <th style="font-size: 0.75rem; font-weight: 600;">Customer</th>
                                        <th style="font-size: 0.75rem; font-weight: 600;">From</th>
                                        <th style="font-size: 0.75rem; font-weight: 600;">To</th>
                                        <th style="font-size: 0.75rem; font-weight: 600;">Start Date</th>
                                        <th style="font-size: 0.75rem; font-weight: 600;">End Date</th>
                                        <th style="font-size: 0.75rem; font-weight: 600;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(!empty($recentBookings))
                                    @foreach($recentBookings as $index => $booking)
                                        @php
                                            $statusColor = $booking->status == 'confirmed' ? 'success' : 
                                                          ($booking->status == 'pending' ? 'warning' : 'danger');
                                        @endphp
                                        <tr>
                                            <td style="font-size: 0.75rem;">{{ $index + 1 }}</td>
                                            <td style="font-size: 0.75rem;">{{ $booking->vehicle->vehicle_name ?? 'N/A' }}</td>
                                            <td style="font-size: 0.75rem;">{{ $booking->customer->name ?? 'N/A' }}</td>
                                            <td style="font-size: 0.75rem;">{{ $booking->from_destination ?? '-' }}</td>
                                            <td style="font-size: 0.75rem;">{{ $booking->to_destination ?? '-' }}</td>
                                            <td style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($booking->start_date)->format('M d, Y') }}</td>
                                            <td style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($booking->end_date)->format('M d, Y') }}</td>
                                            <td>
                                                <span class="badge badge-{{ $statusColor }}" style="font-size: 0.65rem; padding: 3px 6px;">
                                                    {{ ucfirst($booking->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @else
                                        <tr>
                                            <td colspan="8" class="text-center py-2" style="font-size: 0.75rem;">
                                                <i class="fas fa-info-circle mr-1"></i>No recent bookings found
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-right p-2">
                        <a href="{{ route('admin.vehicle_bookings.index') }}" class="btn btn-sm btn-primary" style="font-size: 0.7rem; padding: 3px 8px;">
                            View All Bookings <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-2">
            <div class="col-12">
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h6 class="card-title font-weight-bold">
                            <i class="fas fa-bolt mr-1" style="font-size: 0.9rem;"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body p-2">
                        <div class="row">
                            @if($currentUserIsCustomer == 'N')
                            <div class="col-md-3 col-sm-6 mb-1">
                                <a href="{{ route('admin.vehicles.create') }}" class="btn btn-outline-primary btn-block btn-sm" style="font-size: 0.7rem; padding: 4px;">
                                    <i class="fas fa-plus-circle mr-1" style="font-size: 0.7rem;"></i>Add Vehicle
                                </a>
                            </div>
                            @endif
                            @if($currentUserIsCustomer == 'N')
                            <div class="col-md-3 col-sm-6 mb-1">
                                <a href="{{ route('admin.customers.create') }}" class="btn btn-outline-success btn-block btn-sm" style="font-size: 0.7rem; padding: 4px;">
                                    <i class="fas fa-user-plus mr-1" style="font-size: 0.7rem;"></i>Add Customer
                                </a>
                            </div>
                            @endif
                            @if($currentUserIsCustomer == 'N')
                            <div class="col-md-3 col-sm-6 mb-1">
                                <a href="{{ route('admin.crew_profiles.create') }}" class="btn btn-outline-info btn-block btn-sm" style="font-size: 0.7rem; padding: 4px;">
                                    <i class="fas fa-user-plus mr-1" style="font-size: 0.7rem;"></i>Add Crew
                                </a>
                            </div>
                            @endif
                            <div class="col-md-3 col-sm-6 mb-1">
                                <a href="{{ route('admin.vehicle_bookings.create') }}" class="btn btn-outline-warning btn-block btn-sm" style="font-size: 0.7rem; padding: 4px;">
                                    <i class="fas fa-calendar-plus mr-1" style="font-size: 0.7rem;"></i>New Booking
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

@endsection

@section('styles')
<style>
    .small-box {
        border-radius: 8px !important;
        margin-bottom: 15px;
        position: relative;
        display: block;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: all 0.3s;
        cursor: pointer;
    }
    
    .small-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .small-box > .inner {
        padding: 12px;
    }
    
    .small-box h4 {
        font-size: 1.5rem;
        margin: 0 0 5px 0;
        white-space: nowrap;
        padding: 0;
        font-weight: 600;
    }
    
    .small-box p {
        font-size: 0.85rem;
        margin-bottom: 5px;
    }
    
    .small-box .icon {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 0;
    }
    
    .small-box .icon > i {
        font-size: 2.5rem;
    }
    
    /* Remove the footer since we're not using it */
    .small-box .small-box-footer {
        display: none;
    }
    
    /* Make the entire card clickable */
    a .small-box {
        color: white;
    }
    
    a:hover {
        text-decoration: none;
    }
    
    .bg-orange {
        background-color: #fd7e14 !important;
        color: #fff;
    }
    
    .card-header {
        padding: 8px 12px;
    }
    
    .card-title {
        font-size: 0.9rem;
        margin-bottom: 0;
    }
    
    .table th, .table td {
        padding: 6px 8px;
    }
    
    .badge {
        font-weight: 500;
    }
    
    .content-header h5 {
        font-size: 1.1rem;
    }
    
    .btn-sm {
        font-size: 0.7rem;
    }
</style>
@endsection