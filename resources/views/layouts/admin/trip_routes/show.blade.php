@extends('layouts.admin_theme.container')

@section('dynamicdata')

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-route mr-2"></i>Trip Route Price List
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

        <div class="card card-primary card-outline">
           <div class="card-header d-flex flex-column">

    <div class="d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
            <i class="fas fa-table mr-2"></i>
            Nepal Tourist Vehicle Association - Official Price List
        </h3>

        
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2">
        <div>
           <a href="{{ route('admin.trip-routes.index') }}" class="btn btn-info btn-sm">
            <i class="fas fa-th-large mr-1"></i> Table View
        </a>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.trip-routes.export') }}" class="btn btn-success btn-sm mr-2">
                <i class="fas fa-file-excel mr-1"></i> Export to Excel
            </a>

            <a href="{{ route('admin.trip-routes.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Add New Route
            </a>
        </div>

    </div>

</div>

            <div class="card-body">

                <!-- Price Table -->
                <div class="table-responsive" style="max-height: 600px; overflow: auto; border-radius: 8px; border: 1px solid #dee2e6;">
                    <table id="priceTable" class="table table-hover table-bordered mb-0">
                        <thead style="position: sticky; top: 0; background: #343a40; color: white; z-index: 10;">
                            <tr>
                                <th width="50" class="text-center">S.N.</th>
                                <th>Route Description</th>
                                <th width="80" class="text-center">Distance (KM)</th>
                                <th width="130" class="text-center">
                                    <i class="fas fa-car mr-1"></i> Car
                                </th>
                                <th width="150" class="text-center">
                                    <i class="fas fa-truck mr-1"></i> Hiace/Jeep
                                </th>
                                <th width="130" class="text-center">
                                    <i class="fas fa-bus mr-1"></i> Coaster
                                </th>
                                <th width="130" class="text-center">
                                    <i class="fas fa-bus-alt mr-1"></i> Bus
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $globalCounter = 1; @endphp
                            @foreach($categories as $category)
                                @if($category->routes->count() > 0)
                                    <!-- Category Header -->
                                    <tr class="category-header" style="background: #e9ecef;">
                                        <td colspan="7" class="p-0">
                                            <div class="category-title p-2" style="background: #17a2b8; color: white; font-weight: bold;">
                                                <i class="fas fa-folder-open"></i>
                                                {{ $category->name }} ({{ $category->routes->count() }} routes)
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <!-- Category Routes -->
                                    @foreach($category->routes as $index => $route)
                                        <tr class="route-row">
                                            <td class="text-center align-middle">{{ $globalCounter++ }}</td>
                                            <td class="align-middle">
                                                <strong>{{ $route->title }}</strong>
                                                @if($route->description)
                                                    <br><small class="text-muted">{{ $route->description }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle font-weight-bold">{{ $route->km }} km</td>
                                            <td class="text-right align-middle font-weight-bold text-primary">
                                                Rs {{ number_format($route->car_price, 2) }}
                                            </td>
                                            <td class="text-right align-middle font-weight-bold text-success">
                                                Rs {{ number_format($route->hiace_price, 2) }}
                                            </td>
                                            <td class="text-right align-middle font-weight-bold text-warning">
                                                Rs {{ number_format($route->coaster_price, 2) }}
                                            </td>
                                            <td class="text-right align-middle font-weight-bold text-danger">
                                                Rs {{ number_format($route->bus_price, 2) }}
                                            </td>
                                            <td class="text-right align-middle font-weight-bold text-danger">
                                                Rs {{ number_format($route->van_price, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach

                           
                        </tbody>
                    </table>
                </div>

                <!-- Footer Actions -->
                <div class="row mt-4">
                    <div class="col-md-12 text-right">
                        <a href="{{ route('admin.trip-routes.index') }}" class="btn btn-secondary mr-2">
                            <i class="fas fa-arrow-left mr-1"></i> Back
                        </a>
                        <a href="{{ route('admin.trip-routes.edit', $route->id ?? 1) }}" class="btn btn-warning ml-2">
                            <i class="fas fa-edit mr-1"></i> Edit Routes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('styles')
<style>
    /* Custom styling for professional look */
    .small-box {
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    .small-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }
    
    .table thead th {
        border-bottom: 2px solid #dee2e6;
    }
    
    .table tbody tr:hover {
        background: rgba(23, 162, 184, 0.05);
    }
    
    .category-header td {
        padding: 0 !important;
    }
    
    .category-title {
        padding: 10px 15px;
        font-size: 16px;
        border-left: 4px solid #ffc107;
    }
    
    .route-row td {
        border-bottom: 1px solid #f0f0f0;
    }
    
    .route-row:last-child td {
        border-bottom: none;
    }
    
    .category-summary {
        border-top: 2px solid #17a2b8;
        border-bottom: 2px solid #17a2b8;
        font-size: 0.95em;
    }
    
    .grand-total {
        font-size: 1.1em;
        border-top: 3px solid #ffc107;
    }
    
    /* Print styles */
    @media print {
        .btn-group, .card-tools, .content-header, .small-box {
            display: none !important;
        }
        .table-responsive {
            max-height: none !important;
            overflow: visible !important;
        }
        .grand-total {
            background: #f0f0f0 !important;
            color: black !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable with custom options
    var table = $('#priceTable').DataTable({
        "paging": true,
        "pageLength": 25,
        "lengthChange": true,
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "searching": true,
        "ordering": true,
        "order": [[0, 'asc']],
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "dom": "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
               "<'row'<'col-sm-12'tr>>" +
               "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        "language": {
            "search": "Search Routes:",
            "lengthMenu": "Show _MENU_ routes per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ routes",
            "infoEmpty": "No routes available",
            "infoFiltered": "(filtered from _MAX_ total routes)"
        },
        "initComplete": function() {
            // Style the search box
            $('.dataTables_filter input').addClass('form-control form-control-sm');
            $('.dataTables_filter input').attr('placeholder', 'Type to search...');
            
            // Style the length menu
            $('.dataTables_length select').addClass('form-control form-control-sm');
        }
    });

    // Add custom search functionality for categories
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var searchTerm = table.search().toLowerCase();
            if (!searchTerm) return true;
            
            // Check if row contains category header
            var row = table.row(dataIndex).node();
            if ($(row).hasClass('category-header')) {
                return false; // Hide category headers when searching
            }
            
            return true;
        }
    );

    // Export functionality
    $('#exportBtn').click(function() {
        window.location.href = "{{ route('admin.trip-routes.export') }}";
    });

    // Print functionality
    $('#printBtn').click(function() {
        window.print();
    });
});
</script>
@endpush