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
                        Prices by Vehicle
                    </h3>
                    <div>
                        <a href="{{ route('admin.trip-routes-vehicle-prices.index') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-list mr-1"></i> List View
                        </a>
                        <a href="{{ route('admin.trip-routes-vehicle-prices.create') }}" class="btn btn-primary btn-sm ml-2">
                            <i class="fas fa-plus mr-1"></i> Add Price
                        </a>
                        <a href="{{ route('admin.trip-routes-price.upload') }}" class="btn btn-success btn-sm mr-2">
                            <i class="fas fa-file-excel mr-1"></i> Import from Excel
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
                                                    {{ $vehicle->vehicle_name }} ({{ $vehicle->vehicle_type }}) - ID: {{ $vehicle->id }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- <div class="col-md-4">
                                        <label>Category</label>
                                        <select id="filterCategory" class="form-control">
                                            <option value="">All Categories</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
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
                            <span class="badge badge-primary ml-1">{{ $prices->count() }}</span>
                        </a>
                    </li>
                    @foreach($vehicles as $vehicle)
                        @if($vehicle->routePrices->count() > 0)
                            <li class="nav-item">
                                <a class="nav-link vehicle-tab" id="vehicle-{{ $vehicle->id }}-tab" data-toggle="tab" 
                                   href="#vehicle-{{ $vehicle->id }}" role="tab" data-vehicle-id="{{ $vehicle->id }}">
                                    <i class="fas fa-{{ $vehicle->vehicle_type == 'Car' ? 'car' : ($vehicle->vehicle_type == 'Bus' ? 'bus' : ($vehicle->vehicle_type == 'Van' ? 'van-shuttle' : 'truck')) }} mr-1"></i> 
                                    {{ $vehicle->vehicle_name }}
                                    <span class="badge badge-info ml-1">{{ $vehicle->routePrices->count() }}</span>
                                </a>
                            </li>
                        @endif
                    @endforeach
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
                                        {{-- <th>Vehicle ID</th> --}}
                                        <th>Vehicle</th>
                                        <th>Type</th>
                                        <th>Category</th>
                                        <th>Route</th>
                                        <th>Distance (KM)</th>
                                        <th class="text-right">Price (Rs)</th>
                                        <th width="150" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $counter = 1; @endphp
                                    @foreach($prices as $price)
                                        <tr class="price-row" 
                                            data-vehicle-id="{{ $price->vehicle_id }}"
                                            data-vehicle-name="{{ $price->vehicle->vehicle_name ?? '' }}"
                                            data-category-id="{{ $price->tripRoute->category_id ?? 0 }}"
                                            data-price="{{ $price->price }}">
                                            <td class="text-center">{{ $counter++ }}</td>
                                            {{-- <td class="text-center">{{ $price->vehicle_id }}</td> --}}
                                            <td>
                                                <strong>{{ $price->vehicle->vehicle_name ?? '-' }}</strong>
                                            </td>
                                            <td>
                                                @php
                                                    $vehicleType = $price->vehicle->vehicle_type ?? '-';
                                                    $badgeClass = 'secondary';
                                                    
                                                    switch(strtolower($vehicleType)) {
                                                        case 'car':
                                                            $badgeClass = 'primary';
                                                            break;
                                                        case 'hiace':
                                                            $badgeClass = 'info';
                                                            break;
                                                        case 'coaster':
                                                            $badgeClass = 'warning';
                                                            break;
                                                        case 'bus':
                                                            $badgeClass = 'danger';
                                                            break;
                                                        case 'van':
                                                            $badgeClass = 'success';
                                                            break;
                                                        case 'jeep':
                                                            $badgeClass = 'dark';
                                                            break;
                                                        case 'mini bus':
                                                        case 'minibus':
                                                            $badgeClass = 'secondary';
                                                            break;
                                                        case 'truck':
                                                            $badgeClass = 'danger';
                                                            break;
                                                        default:
                                                            $badgeClass = 'secondary';
                                                    }
                                                @endphp
                                                <span class="badge badge-{{ $badgeClass }}">
                                                    {{ $vehicleType }}
                                                </span>
                                            </td>
                                            <td>{{ $price->tripRoute->category->name ?? '-' }}</td>
                                            <td>
                                                {{ $price->tripRoute->title ?? '-' }}
                                                @if($price->tripRoute->description)
                                                    <br><small class="text-muted">{{ $price->tripRoute->description }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $price->tripRoute->km ?? 0 }} km</td>
                                            <td class="text-right font-weight-bold text-primary">
                                                Rs {{ number_format($price->price, 2) }}
                                            </td>
                                            <td class="text-center">
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
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Individual Vehicle Tabs -->
                    @foreach($vehicles as $vehicle)
                        @if($vehicle->routePrices->count() > 0)
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
                                                <h4>{{ $vehicle->routePrices->count() }}</h4>
                                                <p>Total Routes</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-route"></i>
                                            </div>
                                        </div>
                                    </div>
                                    
                                   
                                </div>

                                <!-- Price Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered" id="vehicleTable-{{ $vehicle->id }}">
                                        <thead style="background: #343a40; color: white;">
                                            <tr>
                                                <th width="50">S.N.</th>
                                                <th>Category</th>
                                                <th>Route</th>
                                                <th class="text-center">Distance (KM)</th>
                                                <th class="text-right">Price (Rs)</th>
                                                <th width="150" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $counter = 1; @endphp
                                            @foreach($vehicle->routePrices as $price)
                                                <tr>
                                                    <td class="text-center">{{ $counter++ }}</td>
                                                    <td>{{ $price->tripRoute->category->name ?? '-' }}</td>
                                                    <td>
                                                        <strong>{{ $price->tripRoute->title ?? '-' }}</strong>
                                                        @if($price->tripRoute->description)
                                                            <br><small class="text-muted">{{ $price->tripRoute->description }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ $price->tripRoute->km ?? 0 }} km</td>
                                                    <td class="text-right font-weight-bold text-primary">
                                                        Rs {{ number_format($price->price, 2) }}
                                                    </td>
                                                    <td class="text-center">
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
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
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
    // Initialize DataTable with proper settings
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
        },
        "initComplete": function() {
            $('.dataTables_filter input').addClass('form-control form-control-sm');
            $('.dataTables_length select').addClass('form-control form-control-sm');
        }
    });

    // Initialize DataTables for each vehicle tab
    @foreach($vehicles as $vehicle)
        @if($vehicle->routePrices->count() > 0)
            var tableId = '#vehicleTable-{{ $vehicle->id }}';
            if ($.fn.DataTable.isDataTable(tableId)) {
                $(tableId).DataTable().destroy();
            }
            $(tableId).DataTable({
                "paging": true,
                "pageLength": 25,
                "lengthChange": true,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "dom": "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
                       "<'row'<'col-sm-12'tr>>" +
                       "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries"
                },
                "initComplete": function() {
                    $('.dataTables_filter input').addClass('form-control form-control-sm');
                    $('.dataTables_length select').addClass('form-control form-control-sm');
                }
            });
        @endif
    @endforeach
});

// Updated filter function using DataTable API
function applyFilters() {
    var vehicleId = $('#filterVehicle').val();
    var categoryId = $('#filterCategory').val();
    var minPrice = parseFloat($('#minPrice').val()) || 0;
    var maxPrice = parseFloat($('#maxPrice').val()) || Infinity;
    
    console.log('=== APPLYING FILTERS ===');
    console.log('Selected Vehicle ID:', vehicleId);
    console.log('Selected Category ID:', categoryId);
    console.log('Price Range:', minPrice, '-', maxPrice);
    
    // Get DataTable instance
    var table = $('#allVehiclesTable').DataTable();
    
    // Clear previous search
    table.search('').draw();
    
    // Build custom filter function for DataTable
    $.fn.dataTable.ext.search.pop(); // Remove previous custom filter
    
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var row = $(table.row(dataIndex).node());
            var rowVehicleId = row.data('vehicle-id');
            var rowCategoryId = row.data('category-id');
            var rowPrice = row.data('price');
            
            var show = true;
            
            if (vehicleId && vehicleId !== '') {
                if (String(rowVehicleId) !== String(vehicleId)) {
                    show = false;
                }
            }
            
            if (show && categoryId && categoryId !== '') {
                if (String(rowCategoryId) !== String(categoryId)) {
                    show = false;
                }
            }
            
            if (show) {
                if (rowPrice < minPrice || rowPrice > maxPrice) {
                    show = false;
                }
            }
            
            return show;
        }
    );
    
    // Redraw the table with the filter
    table.draw();
    
    // Count visible rows
    var visibleCount = table.rows({ filter: 'applied' }).count();
    console.log('Visible rows after filter:', visibleCount);
    
    if (visibleCount === 0) {
        toastr.warning('No results found for the selected filters');
    } else {
        toastr.success('Found ' + visibleCount + ' results');
    }
}

function resetFilters() {
    $('#filterVehicle').val('');
    $('#filterCategory').val('');
    $('#minPrice').val('');
    $('#maxPrice').val('');
    
    // Reset DataTable
    var table = $('#allVehiclesTable').DataTable();
    $.fn.dataTable.ext.search.pop(); // Remove custom filter
    table.search('').draw();
    
    $('#noResultsMessage').remove();
    toastr.info('Filters reset');
}
</script>
@endsection