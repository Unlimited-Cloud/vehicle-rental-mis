@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-map-marked-alt mr-2"></i>Trip Route Management
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Trip Routes</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @include('layouts.admin_theme.alert')


        <!-- Action Buttons -->
        <div class="row mb-3">
            <div class="col-md-6">
                <a href="{{ route('admin.trip-routes.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Add New Route
                </a>
                <a href="{{ route('admin.trip-routes.category.view') }}" class="btn btn-info">
                    <i class="fas fa-th-large mr-1"></i> Category View
                </a>
            </div>
            <div class="col-md-6 text-right">
                <a href="{{ route('admin.trip-routes.export') }}" class="btn btn-success">
                    <i class="fas fa-file-excel mr-1"></i> Export
                </a>
                <a href="{{ route('admin.trip-routes.upload') }}" class="btn btn-warning">
                    <i class="fas fa-file-import mr-1"></i> Import
                </a>
            </div>
        </div>

        <!-- Filters -->
<div class="card card-outline card-secondary mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label for="filterCategory">Filter by Category</label>
                <select id="filterCategory" class="form-control">
                    <option value="">All Categories</option>
                    @foreach($categories as $id => $name)
                        <option value="{{ $name }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="filterRoute">Filter by Route Title</label>
                <input type="text" id="filterRoute" class="form-control" placeholder="Type route name...">
            </div>
                <div class="col-md-4 d-flex align-items-end">
                <button type="button" id="applyFilters" class="btn btn-primary mr-2">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                <button type="button" id="clearFilters" class="btn btn-outline-secondary">
                    <i class="fas fa-times mr-1"></i> Clear
                </button>
            </div>
        </div>
    </div>
</div>

        <!-- Main Table Card -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>
                    All Trip Routes (Table View)
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                     <table id="dataTable" class="table table-bordered table-striped show-search-bar">
                        <thead>
                            <tr>
                                <th width="40">#</th>
                                <th>Category</th>
                                <th>Route Title</th>
                                <th width="80">KM</th>
                                <th width="120">Car (Rs)</th>
                                <th width="120">Hiace (Rs)</th>
                                <th width="120">Coaster (Rs)</th>
                                <th width="120">Bus (Rs)</th>
                                <th width="120">Van (Rs)</th>
                                <th width="80">Status</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($routes as $index => $route)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                        {{ $route->category->name ?? 'Uncategorized' }}
                                </td>
                                <td>
                                    {{ $route->title }}
                                </td>
                                <td class="text-center">{{ $route->km }}</td>
                                <td class="text-right">Rs {{ number_format($route->car_price, 2) }}</td>
                                <td class="text-right">Rs {{ number_format($route->hiace_price, 2) }}</td>
                                <td class="text-right">Rs {{ number_format($route->coaster_price, 2) }}</td>
                                <td class="text-right">Rs {{ number_format($route->bus_price, 2) }}</td>
                                <td class="text-right">Rs {{ number_format($route->van_price, 2) }}</td>
                                <td class="text-center">
                                    @if($route->status)
                                        <span class="badge badge-success"><i class="fas fa-check-circle"></i> Active</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        {{-- <a href="{{ route('admin.trip-routes.show', $route->id) }}" 
                                           class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a> --}}
                                        <a href="{{ route('admin.trip-routes.edit', $route->id) }}" 
                                           class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <form action="{{ route('admin.trip-routes.destroy', $route->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Delete this route?')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-route fa-3x text-muted mb-3"></i>
                                    <h5>No routes found</h5>
                                    <a href="{{ route('admin.trip-routes.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Add First Route
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<style>
    .btn-group .btn {
        margin-right: 2px;
        border-radius: 4px !important;
    }
    .btn-group .btn:last-child {
        margin-right: 0;
    }
    .table td {
        vertical-align: middle;
    }
</style>

<script>
var table; // accessible to both init and filter handlers

$(document).ready(function() {
     table = $('#dataTable').DataTable();

    // Apply filters only when the button is clicked
    $('#applyFilters').on('click', function() {
        var category = $.fn.dataTable.util.escapeRegex($('#filterCategory').val());
        var routeTitle = $('#filterRoute').val();

        table.column(1).search(category ? '^' + category + '$' : '', true, false);
        table.column(2).search(routeTitle);
        table.draw();
    });

    // Clear both
    $('#clearFilters').on('click', function() {
        $('#filterCategory').val('');
        $('#filterRoute').val('');
        table.column(1).search('').column(2).search('').draw();
    });
});
</script>
@endsection

