@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Reports Dashboard</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Reports Dashboard</h3>
                        <div class="card-tools">
                            <form method="GET" action="{{ route('admin.reports.index') }}" id="filterForm" class="form-inline">
                                <div class="form-group mr-2">
                                    <label for="date_range" class="mr-2">Date Range:</label>
                                    <select name="date_range" id="date_range" class="form-control form-control-sm">
                                        <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                                        <option value="yesterday" {{ $dateRange == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                                        <option value="this_week" {{ $dateRange == 'this_week' ? 'selected' : '' }}>This Week</option>
                                        <option value="last_week" {{ $dateRange == 'last_week' ? 'selected' : '' }}>Last Week</option>
                                        <option value="this_month" {{ $dateRange == 'this_month' ? 'selected' : '' }}>This Month</option>
                                        <option value="last_month" {{ $dateRange == 'last_month' ? 'selected' : '' }}>Last Month</option>
                                        <option value="this_quarter" {{ $dateRange == 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
                                        <option value="this_year" {{ $dateRange == 'this_year' ? 'selected' : '' }}>This Year</option>
                                        <option value="last_year" {{ $dateRange == 'last_year' ? 'selected' : '' }}>Last Year</option>
                                    </select>
                                </div>
                                <div class="form-group mr-2">
                                    <label for="vehicle_id" class="mr-2">Vehicle:</label>
                                    <select name="vehicle_id" id="vehicle_id" class="form-control form-control-sm">
                                        <option value="">All Vehicles</option>
                                        @foreach($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}" {{ $vehicleId == $vehicle->id ? 'selected' : '' }}>
                                                {{ $vehicle->vehicle_name }} ({{ $vehicle->vehicle_type }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm mr-2">Apply Filter</button>
                                <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary btn-sm mr-2">Reset</a>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-danger btn-sm dropdown-toggle" data-toggle="dropdown">
                                        Export
                                    </button>
                                     <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="{{ route('admin.reports.export-pdf', request()->all()) }}">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                        <a class="dropdown-item" href="{{ route('admin.reports.export-excel', request()->all()) }}">
                                            <i class="fas fa-file-excel"></i> Excel
                                        </a>
                                        <a class="dropdown-item" href="{{ route('admin.reports.export-client', request()->all()) }}">
                                            <i class="fas fa-file-excel"></i> Client Report
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Summary Cards - Visible on all tabs -->
                        <div class="row">
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3>{{ $summary['formatted_revenue'] }}</h3>
                                        <p>Total Revenue</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h3>{{ $summary['formatted_expenses'] }}</h3>
                                        <p>Total Expenses</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-wallet"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3>{{ $summary['formatted_profit'] }}</h3>
                                        <p>Net Profit</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-pie"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-warning text-white">
                                    <div class="inner">
                                        <h3 class="text-white">{{ $summary['total_bookings'] }}</h3>
                                        <p class="text-white">Confirmed Bookings</p>

                                    
                                    </div>

                                    <div class="icon">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Navigation -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card card-outline card-primary">
                                    <div class="card-header p-0 pt-1">
                                        <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="revenue-tab" data-toggle="pill" href="#revenue" role="tab" aria-controls="revenue" aria-selected="false">
                                                    <i class="fas fa-dollar-sign"></i> Revenue
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="profitability-tab" data-toggle="pill" href="#profitability" role="tab" aria-controls="profitability" aria-selected="true">
                                                    <i class="fas fa-chart-line"></i> Profitability
                                                </a>
                                            </li>
                                            
                                            <li class="nav-item">
                                                <a class="nav-link" id="expenses-tab" data-toggle="pill" href="#expenses" role="tab" aria-controls="expenses" aria-selected="false">
                                                    <i class="fas fa-gas-pump"></i> Fuel & Maintenance
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="discount-tab" data-toggle="pill" href="#discount" role="tab" aria-controls="discount" aria-selected="false">
                                                    <i class="fas fa-tags"></i> Discount Analysis
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="clients-tab" data-toggle="pill" href="#clients" role="tab" aria-controls="clients" aria-selected="false">
                                                    <i class="fas fa-users"></i> Client Usage
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="movement-receipt-tab" data-toggle="pill" href="#movement-receipt" role="tab" aria-controls="movement-receipt" aria-selected="false">
                                                    <i class="fas fa-exchange-alt"></i> Movement & Receipt
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="card-body">
                                        <div class="tab-content" id="reportTabsContent">
                                            <!-- Tab 1: Profitability per Vehicle -->
                                            <div class="tab-pane fade " id="profitability" role="tabpanel" aria-labelledby="profitability-tab">
                                                <div class="table-responsive">
                                                    <table class="table table-hover table-striped table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Vehicle</th>
                                                                <th>Type</th>
                                                                <th>Revenue</th>
                                                                <th>Fuel Cost</th>
                                                                <th>Maintenance</th>
                                                                <th>Crew Salary</th>
                                                                <th>Total Cost</th>
                                                                <th>Net Profit</th>
                                                                {{-- <th>Profit Margin</th> --}}
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($profitabilityReport as $report)
                                                                <tr>
                                                                    <td><strong>{{ $report['vehicle_name'] }}</strong></td>
                                                                    <td><span class="badge badge-info">{{ ucfirst($report['vehicle_type']) }}</span></td>
                                                                    <td>{{ $report['formatted_revenue'] }}</td>
                                                                    <td>{{ $report['formatted_fuel_cost'] }}</td>
                                                                    <td>{{ $report['formatted_maintenance_cost'] }}</td>
                                                                    <td>{{ $report['formatted_crew_salary'] }}</td>
                                                                    <td>{{ $report['formatted_total_cost'] }}</td>
                                                                    <td class="{{ $report['profit_class'] }}">
                                                                        <strong>{{ $report['formatted_net_profit'] }}</strong>
                                                                    </td>
                                                                    {{-- <td>
                                                                        <div class="progress">
                                                                            <div class="progress-bar bg-{{ $report['net_profit'] >= 0 ? 'success' : 'danger' }}" 
                                                                                 style="width: {{ max(0, $report['profit_margin']) }}%">
                                                                                {{ $report['profit_margin'] }}%
                                                                            </div>
                                                                        </div>
                                                                    </td> --}}
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="9" class="text-center">No data available</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- Tab 2: Revenue Report -->
                                            <div class="tab-pane fade show active" id="revenue" role="tabpanel" aria-labelledby="revenue-tab">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="info-box">
                                                            <span class="info-box-icon bg-info"><i class="fas fa-chart-line"></i></span>
                                                            <div class="info-box-content">
                                                                <span class="info-box-text">Total Revenue</span>
                                                                <span class="info-box-number">{{ $revenueReport['formatted_total_revenue'] }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                   <div class="col-md-6">
                                                        <div class="info-box d-flex justify-content-between align-items-center">
                                                            
                                                            <!-- Left Side -->
                                                            <div class="d-flex">
                                                                <span class="info-box-icon bg-success">
                                                                    <i class="fas fa-shopping-cart"></i>
                                                                </span>
                                                                <div class="info-box-content">
                                                                    <span class="info-box-text">Total Bookings</span>
                                                                    <span class="info-box-number">
                                                                        {{ $revenueReport['total_bookings'] }}
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            <!-- Right Side (Small Stats) -->
                                                            <div class="text-right pr-2" style="font-size: 13px;">
                                                                <div>
                                                                    <span class="text-success">●</span> Confirmed: 
                                                                    {{ $revenueReport['confirmed_bookings'] }}
                                                                </div>
                                                                <div>
                                                                    <span class="text-warning">●</span> Pending: 
                                                                    {{ $revenueReport['pending_bookings'] }}
                                                                </div>
                                                                <div>
                                                                    <span class="text-danger">●</span> Cancelled: 
                                                                    {{ $revenueReport['cancelled_bookings'] }}
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                    
                                                </div>

                                                <h5 class="mt-4">Revenue by Vehicle Type</h5>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Vehicle Type</th>
                                                                <th>Bookings</th>
                                                                <th>Revenue</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @php
                                                                $totalRevenue = $revenueReport['total_revenue'];
                                                            @endphp
                                                            @foreach($revenueReport['revenue_by_type'] as $type => $data)
                                                                <tr>
                                                                    <td>{{ ucfirst($type) }}</td>
                                                                    <td>{{ $data['count'] }}</td>
                                                                    <td>₹ {{ number_format($data['total'], 2) }}</td>
                                                                  
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- Tab 3: Fuel & Maintenance Expenses -->
<div class="tab-pane fade" id="expenses" role="tabpanel" aria-labelledby="expenses-tab">
    <!-- Existing summary boxes -->
    <div class="row">
        <div class="col-md-6">
            <div class="info-box bg-danger">
                <span class="info-box-icon"><i class="fas fa-gas-pump"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Fuel Cost</span>
                    <span class="info-box-number">{{ $fuelMaintenanceReport['fuel']['formatted_cost'] }}</span>
                    <small>Total Quantity: {{ number_format($fuelMaintenanceReport['fuel']['total_quantity'], 2) }} L</small>
                    <br>
                    <small>Avg Price: ₹ {{ number_format($fuelMaintenanceReport['fuel']['avg_price_per_liter'], 2) }}/L</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-tools"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Maintenance Cost</span>
                    <span class="info-box-number">{{ $fuelMaintenanceReport['maintenance']['formatted_total'] }}</span>
                </div>
            </div>
        </div>
    </div>


    <!-- Fuel by Pump Table -->
    @if(count($fuelAnalytics['fuel_by_pump']) > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-gas-pump"></i> Fuel Usage by Petrol Pump
                    </h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>Pump Name</th>
                                <th class="text-right">Total Quantity (L)</th>
                                <th class="text-right">Total Amount</th>
                                <th class="text-center">Transactions</th>
                                <th class="text-right">Avg Price/L</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fuelAnalytics['fuel_by_pump'] as $pump)
                            <tr>
                                <td><strong>{{ $pump['pump_name'] }}</strong></td>
                                <td class="text-right">{{ number_format($pump['total_quantity'], 2) }} L</td>
                                <td class="text-right">{{ $pump['formatted_amount'] }}</td>
                                <td class="text-center">{{ $pump['transaction_count'] }}</td>
                                <td class="text-right">₹ {{ number_format($pump['avg_price'], 2) }}/L</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Fuel by Vehicle Table -->
    @if(count($fuelAnalytics['fuel_by_vehicle']) > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-outline-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-truck"></i> Fuel Usage by Vehicle
                    </h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>Vehicle Name</th>
                                <th>Type</th>
                                <th class="text-right">Total Quantity (L)</th>
                                <th class="text-right">Total Amount</th>
                                <th class="text-center">Transactions</th>
                                <th class="text-right">Avg Price/L</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fuelAnalytics['fuel_by_vehicle'] as $vehicle)
                            <tr>
                                <td><strong>{{ $vehicle['vehicle_name'] }}</strong></td>
                                <td><span class="badge badge-info">{{ ucfirst($vehicle['vehicle_type']) }}</span></td>
                                <td class="text-right">{{ number_format($vehicle['total_quantity'], 2) }} L</td>
                                <td class="text-right">{{ $vehicle['formatted_amount'] }}</td>
                                <td class="text-center">{{ $vehicle['transaction_count'] }}</td>
                                <td class="text-right">₹ {{ number_format($vehicle['avg_price'], 2) }}/L</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Maintenance Breakdown (existing content) -->
    <h5 class="mt-4">Maintenance Breakdown</h5>
    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $fuelMaintenanceReport['maintenance']['formatted_service_cost'] }}</h3>
                    <p>Service Cost</p>
                </div>
                <div class="icon">
                    <i class="fas fa-oil-can"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $fuelMaintenanceReport['maintenance']['formatted_repair_cost'] }}</h3>
                    <p>Repair Cost</p>
                </div>
                <div class="icon">
                    <i class="fas fa-wrench"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $fuelMaintenanceReport['maintenance']['formatted_tyre_cost'] }}</h3>
                    <p>Tyre Change Cost</p>
                </div>
                <div class="icon">
                    <i class="fas fa-car"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="progress-group mt-3">
        <span class="progress-text">Service</span>
        <span class="float-right"><b>{{ $fuelMaintenanceReport['maintenance']['formatted_service_cost'] }}</b></span>
        <div class="progress progress-sm">
            <div class="progress-bar bg-primary" style="width: {{ ($fuelMaintenanceReport['maintenance']['service_cost'] / max($fuelMaintenanceReport['maintenance']['total'], 1)) * 100 }}%"></div>
        </div>
    </div>
    <div class="progress-group">
        <span class="progress-text">Repair</span>
        <span class="float-right"><b>{{ $fuelMaintenanceReport['maintenance']['formatted_repair_cost'] }}</b></span>
        <div class="progress progress-sm">
            <div class="progress-bar bg-success" style="width: {{ ($fuelMaintenanceReport['maintenance']['repair_cost'] / max($fuelMaintenanceReport['maintenance']['total'], 1)) * 100 }}%"></div>
        </div>
    </div>
    <div class="progress-group">
        <span class="progress-text">Tyre Change</span>
        <span class="float-right"><b>{{ $fuelMaintenanceReport['maintenance']['formatted_tyre_cost'] }}</b></span>
        <div class="progress progress-sm">
            <div class="progress-bar bg-info" style="width: {{ ($fuelMaintenanceReport['maintenance']['tyre_cost'] / max($fuelMaintenanceReport['maintenance']['total'], 1)) * 100 }}%"></div>
        </div>
    </div>
</div>

                                            <!-- Tab 4: Discount Analysis -->
                                            <div class="tab-pane fade" id="discount" role="tabpanel" aria-labelledby="discount-tab">
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <div class="info-box bg-warning">
                                                            <span class="info-box-icon"><i class="fas fa-tags"></i></span>
                                                            <div class="info-box-content">
                                                                <span class="info-box-text">Total Discount Given</span>
                                                                <span class="info-box-number">{{ $discountAnalysis['formatted_total_discount'] }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {{-- <div class="col-md-6">
                                                        <div class="info-box bg-info">
                                                            <span class="info-box-icon"><i class="fas fa-percent"></i></span>
                                                            <div class="info-box-content">
                                                                <span class="info-box-text">Bookings with Discount</span>
                                                                <span class="info-box-number">{{ $discountAnalysis['total_bookings_with_discount'] }}</span>
                                                            </div>
                                                        </div>
                                                    </div> --}}
                                                </div>

                                                <div class="table-responsive">
                                                   <table id="dataTable" class="table table-bordered table-striped show-search-bar">
                                                        <thead>
                                                            <tr>
                                                                <th>Booking ID</th>
                                                                <th>Vehicle</th>
                                                                <th>Customer</th>
                                                                <th>Date</th>
                                                                <th>Trip Route</th>
                                                                <th>Expected Amount</th>
                                                                <th>Actual Amount</th>
                                                                <th>Discount Given</th>
                                                                <th>Discount %</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($discountAnalysis['bookings'] as $booking)
                                                                <tr>
                                                                    <td>#{{ $booking['booking_id'] }}</td>
                                                                    <td>{{ $booking['vehicle_name'] }}</td>
                                                                    <td>{{ $booking['customer_name'] }}</td>
                                                                    <td>{{ \Carbon\Carbon::parse($booking['start_date'])->format('Y-m-d') }}</td>
                                                                    <td>{{ $booking['trip_route'] }}</td>
                                                                    <td>₹ {{ number_format($booking['expected_amount'], 2) }}</td>
                                                                    <td>₹ {{ number_format($booking['actual_amount'], 2) }}</td>
                                                                    <td class="text-danger">-₹ {{ number_format($booking['discount_given'], 2) }}</td>
                                                                    <td>
                                                                        <span class="badge badge-warning">{{ $booking['discount_percentage'] }}%</span>
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="9" class="text-center">No discount data available</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- Tab 5: Client Usage Report -->
                                            <div class="tab-pane fade" id="clients" role="tabpanel" aria-labelledby="clients-tab">
                                                <div class="row mb-3">
                                                    <div class="col-md-4">
                                                        <div class="info-box bg-primary">
                                                            <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                                            <div class="info-box-content">
                                                                <span class="info-box-text">Total Clients</span>
                                                                <span class="info-box-number">{{ $clientUsageReport['total_clients'] }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="info-box bg-success">
                                                            <span class="info-box-icon"><i class="fas fa-money-bill"></i></span>
                                                            <div class="info-box-content">
                                                                <span class="info-box-text">Total Spent</span>
                                                                <span class="info-box-number">{{ $clientUsageReport['formatted_total_spent'] }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="info-box bg-info">
                                                            <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                                            <div class="info-box-content">
                                                                <span class="info-box-text">Average per Client</span>
                                                                <span class="info-box-number">{{ $clientUsageReport['formatted_average_per_client'] }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="table-responsive">
                                                    <table id="dataTable" class="table table-bordered table-striped show-search-bar">
                                                        <thead>
                                                            <tr>
                                                                <th>Client Name</th>
                                                                <th>Email</th>
                                                                <th>Phone</th>
                                                                <th>Total Bookings</th>
                                                                <th>Total Spent</th>
                                                                <th>Average Booking</th>
                                                                <th>Vehicle Types Used</th>
                                                                <th>Last Booking</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($clientUsageReport['clients'] as $client)
                                                                <tr>
                                                                    <td><strong>{{ $client['client_name'] }}</strong></td>
                                                                    <td>{{ $client['client_email'] }}</td>
                                                                    <td>{{ $client['client_phone'] }}</td>
                                                                    <td><span class="badge badge-info">{{ $client['total_bookings'] }}</span></td>
                                                                    <td>{{ $client['formatted_total_spent'] }}</td>
                                                                    <td>{{ $client['formatted_average'] }}</td>
                                                                    <td>
                                                                        @foreach(explode(', ', $client['vehicle_types_used']) as $type)
                                                                            <span class="badge badge-secondary">{{ $type }}</span>
                                                                        @endforeach
                                                                    </td>
                                                                    <td>
                                                                        {{ $client['last_booking_date'] 
                                                                            ? \Carbon\Carbon::parse($client['last_booking_date'])->format('Y-m-d') 
                                                                            : 'N/A' }}
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="8" class="text-center">No client data available</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <!-- Tab 6: Movement & Receipt Reports -->
<div class="tab-pane fade" id="movement-receipt" role="tabpanel" aria-labelledby="movement-receipt-tab">
    
    <!-- MOVEMENT Summary Cards -->
    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-truck-moving"></i> MOVEMENT Summary
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Movements</span>
                                    <span class="info-box-number">{{ $movementReport['total_movements'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-money-bill"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Movement Amount</span>
                                    <span class="info-box-number">{{ $movementReport['formatted_amount'] ?? '₹ 0' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- @if(!empty($movementReport['movements_by_status']))
                    <h6 class="mt-3">Movements by Status</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th class="text-center">Count</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($movementReport['movements_by_status'] as $status => $data)
                                <tr>
                                    <td><span class="badge badge-{{ $status == 'completed' ? 'success' : 'warning' }}">{{ ucfirst($status) }}</span></td>
                                    <td class="text-center">{{ $data['count'] }}</td>
                                    <td class="text-right">₹ {{ number_format($data['amount'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif --}}
                </div>
            </div>
        </div>
        
        <!-- RECEIPT Summary Cards -->
        <div class="col-md-6">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-receipt"></i> RECEIPT Summary
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-file-invoice"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Receipts</span>
                                    <span class="info-box-number">{{ $receiptReport['total_receipts'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Booking Amount</span>
                                    <span class="info-box-number">{{ $receiptReport['formatted_booking_amount'] ?? '₹ 0' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Paid Amount</span>
                                    <span class="info-box-number">{{ $receiptReport['formatted_paid_amount'] ?? '₹ 0' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Pending Amount</span>
                                    <span class="info-box-number">{{ $receiptReport['formatted_pending_amount'] ?? '₹ 0' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if(!empty($receiptReport['receipts_by_payment_method']))
                    <h6 class="mt-3">Receipts by Payment Method</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Payment Method</th>
                                    <th class="text-center">Count</th>
                                    <th class="text-right">Paid Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($receiptReport['receipts_by_payment_method'] as $method => $data)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $method)) }}</td>
                                    <td class="text-center">{{ $data['count'] }}</td>
                                    <td class="text-right">₹ {{ number_format($data['paid_amount'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Incomplete Processes Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle"></i> Incomplete Processes - Action Required
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Bookings without MOVEMENT -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $bookingsWithoutMovement['count'] ?? 0 }}</h3>
                                    <p>Bookings Without MOVEMENT</p>
                                    <p class="mb-0"><small>Amount: {{ $bookingsWithoutMovement['formatted_amount'] ?? '₹ 0' }}</small></p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar-times"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $movementsWithoutReceipt['count'] ?? 0 }}</h3>
                                    <p>MOVEMENTS Without RECEIPT</p>
                                    <p class="mb-0"><small>Amount: {{ $movementsWithoutReceipt['formatted_amount'] ?? '₹ 0' }}</small></p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $receiptsWithoutFullPayment['count'] ?? 0 }}</h3>
                                    <p>RECEIPTS Without Full Payment</p>
                                    <p class="mb-0"><small>Pending: {{ $receiptsWithoutFullPayment['formatted_pending_amount'] ?? '₹ 0' }}</small></p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bookings without MOVEMENT Details Table -->
                    @if(($bookingsWithoutMovement['count'] ?? 0) > 0)
                    <div class="mt-4">
                        <h5><i class="fas fa-calendar-times"></i> Bookings Without MOVEMENT</h5>
                        <div class="table-responsive">
                             <table id="dataTable" class="table table-bordered table-striped show-search-bar">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Customer</th>
                                        <th>Vehicle</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th class="text-right">Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookingsWithoutMovement['bookings'] ?? [] as $booking)
                                    <tr>
                                        <td>#{{ $booking->id }}</td>
                                        <td>{{ $booking->customer->name ?? 'N/A' }}</td>
                                        <td>{{ $booking->vehicle->vehicle_name ?? 'N/A' }}</td>
                                        <td>{{ Carbon\Carbon::parse($booking->start_date)->format('Y-m-d') }}</td>
                                        <td>{{ Carbon\Carbon::parse($booking->end_date)->format('Y-m-d') }}</td>
                                        <td class="text-right">₹ {{ number_format($booking->total_amount, 2) }}</td>
                                        <td><span class="badge badge-warning">Pending MOVEMENT</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                    
                    <!-- MOVEMENTS without RECEIPT Details Table -->
                    @if(($movementsWithoutReceipt['count'] ?? 0) > 0)
                    <div class="mt-4">
                        <h5><i class="fas fa-file-invoice"></i> MOVEMENTS Without RECEIPT</h5>
                        <div class="table-responsive">
                             <table id="dataTable" class="table table-bordered table-striped show-search-bar">
                                <thead>
                                    <tr>
                                        <th>Movement ID</th>
                                        <th>Booking ID</th>
                                        <th>Movement Date</th>
                                        <th class="text-right">Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($movementsWithoutReceipt['movements'] ?? [] as $movement)
                                    <tr>
                                        <td>#{{ $movement->id }}</td>
                                        <td>#{{ $movement->booking->id ?? 'N/A' }}</td>
                                        <td>{{ Carbon\Carbon::parse($movement->created_at)->format('Y-m-d') }}</td>
                                        <td class="text-right">₹ {{ number_format($movement->booking->total_amount, 2) }}</td>
                                        <td><span class="badge badge-info">No RECEIPT</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                    
                    <!-- RECEIPTS without Full Payment Details Table -->
                    @if(($receiptsWithoutFullPayment['count'] ?? 0) > 0)
                    <div class="mt-4">
                        <h5><i class="fas fa-credit-card"></i> RECEIPTS Without Full Payment</h5>
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-bordered table-striped show-search-bar">
                                <thead>
                                    <tr>
                                        <th>Receipt ID</th>
                                        <th>Customer</th>
                                        <th>Receipt Date</th>
                                        <th class="text-right">Total Amount</th>
                                        <th class="text-right">Paid Amount</th>
                                        <th class="text-right">Pending Amount</th>
                                        <th>Payment Method</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($receiptsWithoutFullPayment['receipts'] ?? [] as $receipt)
                                    <tr>
                                        <td>#{{ $receipt->id }}</td>
                                        <td>{{ $receipt->customer->name ?? 'N/A' }}</td>
                                        <td>{{ Carbon\Carbon::parse($receipt->created_at)->format('Y-m-d') }}</td>
                                        <td class="text-right">₹ {{ number_format($receipt->total_amount, 2) }}</td>
                                        <td class="text-right text-success">₹ {{ number_format($receipt->amount, 2) }}</td>
                                        <td class="text-right text-danger">₹ {{ number_format($receipt->total_amount - $receipt->amount, 2) }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $receipt->payment_method ?? 'N/A')) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="text-muted">
                            <i class="fas fa-calendar-alt"></i> Report generated on {{ now()->format('F d, Y h:i A') }} | 
                            <i class="fas fa-clock"></i> Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .small-box {
        border-radius: 0.25rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        display: block;
        margin-bottom: 20px;
        position: relative;
    }
    .small-box .inner {
        padding: 10px;
    }
    .small-box .icon {
        color: rgba(0,0,0,0.15);
        position: absolute;
        right: 10px;
        top: 15px;
        transition: all .3s linear;
    }
    .small-box .icon > i {
        font-size: 70px;
    }
    .small-box h3 {
        font-size: 2rem;
        font-weight: bold;
        margin: 0 0 10px 0;
        white-space: nowrap;
        padding: 0;
    }
    .info-box {
        display: flex;
        min-height: 90px;
        background: #fff;
        width: 100%;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border-radius: 0.25rem;
        margin-bottom: 1rem;
    }
    .info-box .info-box-icon {
        border-radius: 0.25rem 0 0 0.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 70px;
        font-size: 2rem;
    }
    .info-box .info-box-content {
        padding: 5px 10px;
        flex: 1;
    }
    .info-box .info-box-number {
        font-weight: 700;
        font-size: 1.25rem;
    }
    .progress-group {
        margin-bottom: 0.5rem;
    }
    .progress-group .progress-text {
        font-weight: 600;
    }
    .table td, .table th {
        vertical-align: middle;
    }
    .nav-tabs .nav-link {
        padding: 10px 20px;
        font-weight: 500;
    }
    .nav-tabs .nav-link i {
        margin-right: 5px;
    }
    .mt-4 {
        margin-top: 1.5rem !important;
    }
    .mb-3 {
        margin-bottom: 1rem !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Preserve active tab after form submit
    var activeTab = localStorage.getItem('activeReportTab');
    if (activeTab) {
        $('#reportTabs a[href="' + activeTab + '"]').tab('show');
    }
    
    // Store active tab in localStorage when clicked
    $('#reportTabs a').on('shown.bs.tab', function (e) {
        localStorage.setItem('activeReportTab', $(e.target).attr('href'));
    });
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Add responsive table wrapper for better mobile view
    $('.table-responsive').each(function() {
        if (!$(this).parent().hasClass('table-responsive-wrapper')) {
            $(this).wrapInner('<div class="table-responsive-wrapper" style="overflow-x: auto;"></div>');
        }
    });
});
</script>
@endpush