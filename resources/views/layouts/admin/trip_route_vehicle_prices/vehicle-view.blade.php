@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-car mr-2"></i>Vehicle Wise Price List
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.trip-routes-vehicle-prices.index') }}">Prices</a></li>
                    <li class="breadcrumb-item active">Vehicle View</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @include('layouts.admin_theme.alert')

        <div class="card card-primary card-outline">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-table mr-2"></i>
                        Global Vehicle Prices
                    </h3>
                    <div>
                        <a href="{{ route('admin.trip-routes-vehicle-prices.index') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-list mr-1"></i> List View
                        </a>
                        <a href="{{ route('admin.trip-routes-vehicle-prices.create') }}" class="btn btn-primary btn-sm ml-2">
                            <i class="fas fa-plus mr-1"></i> Add Price
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Filter Section -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card card-secondary">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-filter mr-2"></i>Filter Prices
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Vehicle</label>
                                        <select id="filterVehicle" class="form-control">
                                            <option value="">All Vehicles</option>
                                            @foreach($vehicles as $vehicle)
                                                <option value="{{ $vehicle->id }}">
                                                    {{ $vehicle->vehicle_name }} ({{ $vehicle->vehicle_type }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- <div class="col-md-4">
                                        <label>Price Type</label>
                                        <select id="filterPriceType" class="form-control">
                                            <option value="">All Types</option>
                                            <option value="per_km">Per KM</option>
                                            <option value="per_hour">Per Hour</option>
                                            <option value="overnight">Overnight</option>
                                        </select>
                                    </div> --}}
                                    <div class="col-md-4">
                                        <label>Price Range</label>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <input type="number" id="minPrice" class="form-control" placeholder="Min Price" step="0.01">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="number" id="maxPrice" class="form-control" placeholder="Max Price" step="0.01">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <button class="btn btn-primary" onclick="applyFilters()">
                                            <i class="fas fa-search mr-1"></i> Apply Filters
                                        </button>
                                        <button class="btn btn-secondary ml-2" onclick="resetFilters()">
                                            <i class="fas fa-undo mr-1"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Tabs -->
                <ul class="nav nav-tabs nav-tabs-custom mb-4" id="vehicleTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="all-vehicles-tab" data-toggle="tab" href="#all-vehicles" role="tab">
                            <i class="fas fa-th-list mr-1"></i> All Vehicles
                            <span class="badge badge-primary ml-1">{{ $vehicles->count() }}</span>
                        </a>
                    </li>
                    {{-- @foreach($vehicles as $vehicle)
                        <li class="nav-item">
                            <a class="nav-link vehicle-tab" id="vehicle-{{ $vehicle->id }}-tab" data-toggle="tab" 
                               href="#vehicle-{{ $vehicle->id }}" role="tab" data-vehicle-id="{{ $vehicle->id }}">
                                <i class="fas fa-{{ $vehicle->vehicle_type == 'Car' ? 'car' : ($vehicle->vehicle_type == 'Bus' ? 'bus' : ($vehicle->vehicle_type == 'Van' ? 'van-shuttle' : 'truck')) }} mr-1"></i> 
                                {{ $vehicle->vehicle_name }}
                            </a>
                        </li>
                    @endforeach --}}
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="vehicleTabsContent">
                    <!-- All Vehicles Tab -->
                    <div class="tab-pane fade show active" id="all-vehicles" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="allVehiclesTable">
                                <thead style="background: #343a40; color: white;">
                                    <tr>
                                        <th width="50">S.N.</th>
                                        <th>Vehicle</th>
                                        <th>Type</th>
                                        <th>Per KM (Rs)</th>
                                        <th>Per Hour (Rs)</th>
                                        <th>Overnight</th>
                                        <th width="150" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $counter = 1; @endphp
                                    @foreach($vehicles as $vehicle)
                                        @php
                                            $price = $vehicle->routePrices->first();
                                        @endphp
                                        <tr class="price-row" 
                                            data-vehicle-id="{{ $vehicle->id }}"
                                            data-per-km="{{ $price->per_km ?? 0 }}"
                                            data-per-hour="{{ $price->per_hour ?? 0 }}"
                                            data-overnight="{{ $price->overnight ?? 0 }}">
                                            <td class="text-center">{{ $counter++ }}</td>
                                            <td>
                                                <strong>{{ $vehicle->vehicle_name }}</strong>
                                            </td>
                                            <td>
                                                @php
                                                    $vehicleType = $vehicle->vehicle_type ?? '-';
                                                    $badgeClass = 'secondary';
                                                    
                                                    switch(strtolower($vehicleType)) {
                                                        case 'car': $badgeClass = 'primary'; break;
                                                        case 'hiace': $badgeClass = 'info'; break;
                                                        case 'coaster': $badgeClass = 'warning'; break;
                                                        case 'bus': $badgeClass = 'danger'; break;
                                                        case 'van': $badgeClass = 'success'; break;
                                                        case 'jeep': $badgeClass = 'dark'; break;
                                                        case 'mini bus':
                                                        case 'minibus': $badgeClass = 'secondary'; break;
                                                        case 'truck': $badgeClass = 'danger'; break;
                                                        default: $badgeClass = 'secondary';
                                                    }
                                                @endphp
                                                <span class="badge badge-{{ $badgeClass }}">
                                                    {{ $vehicleType }}
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                @if($price && $price->per_km)
                                                    Rs {{ number_format($price->per_km, 2) }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if($price && $price->per_hour)
                                                    Rs {{ number_format($price->per_hour, 2) }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if($price && $price->price)
                                                    Rs {{ number_format($price->price, 2) }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            {{-- <td>
                                                @if($price && $price->overnight)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check-circle"></i> Yes
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary">
                                                        <i class="fas fa-times-circle"></i> No
                                                    </span>
                                                @endif
                                            </td> --}}
                                            <td class="text-center">
                                                @if($price)
                                                    <a href="{{ route('admin.trip-routes-vehicle-prices.edit', $price->id) }}" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.trip-routes-vehicle-prices.destroy', $price->id) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                                onclick="return confirm('Delete this price?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('admin.trip-routes-vehicle-prices.create') }}" 
                                                       class="btn btn-sm btn-success">
                                                        <i class="fas fa-plus"></i> Add
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Individual Vehicle Tabs -->
                    @foreach($vehicles as $vehicle)
                        @php
                            $price = $vehicle->routePrices->first();
                        @endphp
                        <div class="tab-pane fade" id="vehicle-{{ $vehicle->id }}" role="tabpanel">
                            <!-- Vehicle Summary -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="small-box bg-info">
                                        <div class="inner">
                                            <h4>{{ $vehicle->vehicle_name }}</h4>
                                            <p>{{ $vehicle->vehicle_type }}</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-{{ $vehicle->vehicle_type == 'Car' ? 'car' : ($vehicle->vehicle_type == 'Bus' ? 'bus' : 'truck') }}"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="small-box bg-success">
                                        <div class="inner">
                                            <h4>@if($price && $price->per_km) Rs {{ number_format($price->per_km, 2) }} @else - @endif</h4>
                                            <p>Per KM</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-road"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="small-box bg-warning">
                                        <div class="inner">
                                            <h4>@if($price && $price->per_hour) Rs {{ number_format($price->per_hour, 2) }} @else - @endif</h4>
                                            <p>Per Hour</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="small-box @if($price && $price->overnight) bg-danger @else bg-secondary @endif">
                                        <div class="inner">
                                            <h4>@if($price && $price->overnight) <i class="fas fa-check-circle"></i> Yes @else <i class="fas fa-times-circle"></i> No @endif</h4>
                                            <p>Overnight Stay</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-moon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Price Details -->
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-money-bill mr-2"></i>Price Details
                                    </h5>
                                    @if(!$price)
                                        <a href="{{ route('admin.trip-routes-vehicle-prices.create') }}" 
                                           class="btn btn-sm btn-success float-right">
                                            <i class="fas fa-plus mr-1"></i> Add Price
                                        </a>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Per KM Price</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">Rs</span>
                                                    </div>
                                                    <input type="text" class="form-control" 
                                                           value="{{ $price && $price->per_km ? number_format($price->per_km, 2) : 'Not Set' }}" 
                                                           readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Per Hour Price</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">Rs</span>
                                                    </div>
                                                    <input type="text" class="form-control" 
                                                           value="{{ $price && $price->per_hour ? number_format($price->per_hour, 2) : 'Not Set' }}" 
                                                           readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Overnight Stay</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <i class="fas fa-moon"></i>
                                                        </span>
                                                    </div>
                                                    <input type="text" class="form-control" 
                                                           value="{{ $price && $price->overnight ? 'Yes' : 'No' }}" 
                                                           readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if($price)
                                        <div class="mt-3">
                                            <a href="{{ route('admin.trip-routes-vehicle-prices.edit', $price->id) }}" 
                                               class="btn btn-primary">
                                                <i class="fas fa-edit mr-1"></i> Edit Price
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Note -->
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Note:</strong> This is the global price for this vehicle. These prices apply to all routes by default unless overridden at the route level.
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<style>
    .nav-tabs-custom .nav-link {
        border-radius: 0;
        padding: 10px 20px;
        margin-right: 5px;
        border: 1px solid #dee2e6;
        border-bottom: none;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }
    
    .nav-tabs-custom .nav-link:hover {
        background: #e9ecef;
        border-color: #dee2e6;
    }
    
    .nav-tabs-custom .nav-link.active {
        background: #fff;
        border-color: #dee2e6;
        border-bottom: 2px solid #007bff;
        color: #007bff;
    }
    
    .nav-tabs-custom .nav-link .badge {
        font-size: 12px;
        padding: 2px 8px;
    }
    
    .small-box {
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.2s;
        cursor: pointer;
    }
    
    .small-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }
    
    .table-responsive {
        border-radius: 8px;
        overflow-x: auto;
    }
    
    .table thead th {
        border-bottom: 2px solid #dee2e6;
        padding: 12px 8px;
    }
    
    .table tbody tr:hover {
        background: rgba(23, 162, 184, 0.05);
    }
</style>

<script>
$(document).ready(function() {
    // Initialize DataTable
    if ($.fn.DataTable.isDataTable('#allVehiclesTable')) {
        $('#allVehiclesTable').DataTable().destroy();
    }
    
    var table = $('#allVehiclesTable').DataTable({
        "paging": true,
        "pageLength": 25,
        "lengthChange": true,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "dom": "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
               "<'row'<'col-sm-12'tr>>" +
               "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoFiltered": "(filtered from _MAX_ total entries)"
        }
    });
});

function applyFilters() {
    var vehicleId = $('#filterVehicle').val();
    var priceType = $('#filterPriceType').val();
    var minPrice = parseFloat($('#minPrice').val()) || 0;
    var maxPrice = parseFloat($('#maxPrice').val()) || Infinity;
    
    var table = $('#allVehiclesTable').DataTable();
    table.search('').draw();
    
    $.fn.dataTable.ext.search.pop();
    
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var row = $(table.row(dataIndex).node());
            var rowVehicleId = row.data('vehicle-id');
            var perKm = parseFloat(row.data('per-km')) || 0;
            var perHour = parseFloat(row.data('per-hour')) || 0;
            
            var show = true;
            
            if (vehicleId && vehicleId !== '') {
                if (String(rowVehicleId) !== String(vehicleId)) {
                    show = false;
                }
            }
            
            if (show && priceType && priceType !== '') {
                var priceValue = 0;
                if (priceType === 'per_km') {
                    priceValue = perKm;
                } else if (priceType === 'per_hour') {
                    priceValue = perHour;
                } else if (priceType === 'overnight') {
                    // Overnight is boolean, filter differently
                }
                
                if (priceType !== 'overnight' && (priceValue < minPrice || priceValue > maxPrice)) {
                    show = false;
                }
            }
            
            return show;
        }
    );
    
    table.draw();
    
    var visibleCount = table.rows({ filter: 'applied' }).count();
    if (visibleCount === 0) {
        toastr.warning('No results found for the selected filters');
    } else {
        toastr.success('Found ' + visibleCount + ' results');
    }
}

function resetFilters() {
    $('#filterVehicle').val('');
    $('#filterPriceType').val('');
    $('#minPrice').val('');
    $('#maxPrice').val('');
    
    var table = $('#allVehiclesTable').DataTable();
    $.fn.dataTable.ext.search.pop();
    table.search('').draw();
    
    toastr.info('Filters reset');
}
</script>
@endsection