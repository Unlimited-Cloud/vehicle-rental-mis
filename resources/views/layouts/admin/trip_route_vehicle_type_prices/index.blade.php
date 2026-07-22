@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>
            <i class="fas fa-money-bill-wave mr-2"></i>
            Trip Route Vehicle Type Prices
        </h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @include('layouts.admin_theme.alert')

        <div class="mb-3">
            <a href="{{ route('admin.trip-routes-vehicle-type-prices.create') }}"
               class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Price
            </a>
            {{-- <a href="{{ route('admin.trip-routes-vehicle-type-prices.vehicle-view') }}"
               class="btn btn-success">
                <i class="fas fa-car"></i> Vehicle Wise Price View
            </a> --}}
        </div>

        <div class="card">
            <div class="card-body">

                <table id="dataTable"
                       class="table table-bordered table-striped show-search-bar">

                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Brand</th>
                        <th>Type</th>
                        <th>Seater</th>
                        <th>Per KM (Rs)</th>
                        <th>Per Hour (Rs)</th>
                        <th>Overnight</th>
                        <th width="150">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($prices as $price)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                             <td class="text-right">
                                {{ $price->brand ? $price->brand : '-' }}
                            </td>
                            <td>
                                @php
                                    $vehicleType = $price->vehicle_type ?? '-';
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
                                {{ $price->seater ? $price->seater : '-' }}
                            </td>
                            <td class="text-right">
                                {{ $price->per_km ? 'Rs ' . number_format($price->per_km, 2) : '-' }}
                            </td>
                            <td class="text-right">
                                {{ $price->per_hour ? 'Rs ' . number_format($price->per_hour, 2) : '-' }}
                            </td>
                             <td class="text-right">
                                {{ $price->overnight_price ? 'Rs ' . number_format($price->overnight_price, 2) : '-' }}
                            </td>
                            {{-- <td>
                                @if($price->overnight)
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i> Yes
                                    </span>
                                @else
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-times-circle"></i> No
                                    </span>
                                @endif
                            </td> --}}
                            <td>
                                <a href="{{ route('admin.trip-routes-vehicle-type-prices.edit', $price->id) }}"
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form
                                    action="{{ route('admin.trip-routes-vehicle-type-prices.destroy', $price->id) }}"
                                    method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Delete this record?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="py-4">
                                    <i class="fas fa-inbox fa-3x text-muted"></i>
                                    <p class="mt-2 text-muted">No Records Found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

            </div>
        </div>

    </div>
</section>

@endsection

@push('scripts')
<script>
$(function(){
    $('#dataTable').DataTable({
        "paging": true,
        "pageLength": 25,
        "lengthChange": true,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "order": [[0, 'asc']]
    });
});
</script>
@endpush