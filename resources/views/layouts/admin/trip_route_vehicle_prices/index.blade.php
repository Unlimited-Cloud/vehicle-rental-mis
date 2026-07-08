@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>
            <i class="fas fa-money-bill-wave mr-2"></i>
            Trip Route Vehicle Prices
        </h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @include('layouts.admin_theme.alert')

        <div class="mb-3">
            <a href="{{ route('admin.trip-routes-vehicle-prices.create') }}"
               class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Price
            </a>
            <a href="{{ route('admin.trip-routes-vehicle-prices.vehicle-view') }}"
               class="btn btn-success">
                <i class="fas fa-car"></i> Vehicle Wise Price View
            </a>
        </div>

        <div class="card">
            <div class="card-body">

                <table id="dataTable"
                       class="table table-bordered table-striped show-search-bar">

                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>Route</th>
                        <th>Vehicle</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th width="150">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($prices as $price)

                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                {{ $price->tripRoute->category->name ?? '-' }}
                            </td>

                            <td>
                                {{ $price->tripRoute->title ?? '-' }}
                            </td>

                            <td>
                                {{ $price->vehicle->vehicle_name ?? '-' }}
                            </td>
                             <td>
                                {{ $price->vehicle->vehicle_type ?? '-' }}
                            </td>

                            <td>
                                Rs {{ number_format($price->price,2) }}
                            </td>

                            <td>

                                <a href="{{ route('admin.trip-routes-vehicle-prices.edit',$price->id) }}"
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>

                              

                                <form
                                    action="{{ route('admin.trip-routes-vehicle-prices.destroy',$price->id) }}"
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
                            <td colspan="5" class="text-center">
                                No Records Found
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
    $('#dataTable').DataTable();
});
</script>
@endpush