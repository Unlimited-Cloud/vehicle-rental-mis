{{-- resources/views/layouts/admin/customers/index.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Vehicle Owner</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('admin.vehicleowner.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Add Vehicle Owner
    </a>
</div>

<div class="table-responsive">
    <table id="dataTable" class="table table-bordered table-striped show-search-bar">
        <thead>
            <tr>
                <th>SN</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>City</th>
                <th>License No.</th>
                <th>License Expiry</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @if($currentUserIsCustomer == 'N')
            @foreach($vehicleowners as $vehicleowner)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $vehicleowner->name }}</td>
                <td>{{ $vehicleowner->phone }}</td>
                <td>{{ $vehicleowner->email ?? 'N/A' }}</td>
                <td>{{ $vehicleowner->city ?? 'N/A' }}</td>
                <td>{{ $vehicleowner->license_number ?? 'N/A' }}</td>
                <td>{{ $vehicleowner->license_expiry ? $customer->license_expiry->format('d-m-Y') : 'N/A' }}</td>
                <td>{!! $vehicleowner->status_badge !!}</td>
                <td>
                    <a href="{{ route('admin.vehicleowner.edit', $vehicleowner->id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i>
                    </a>

                    <a href="{{ route('admin.vehicleowner.show', $vehicleowner->id) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-eye"></i>
                    </a>

                    <form action="{{ route('admin.vehicleowner.destroy', $vehicleowner->id) }}"
                          method="POST"
                          style="display:inline-block;"
                          onsubmit="return confirm('Are you sure you want to delete this owner?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm bg-red">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
            @else
            @php
                $vehicleowner = $vehicleowners;
            @endphp 
                <tr>
                    <td>{{ 1 }}</td>
                    <td>{{ $vehicleowner->name }}</td>
                    <td>{{ $vehicleowner->phone }}</td>
                    <td>{{ $vehicleowner->email ?? 'N/A' }}</td>
                    <td>{{ $vehicleowner->city ?? 'N/A' }}</td>
                    <td>{{ $vehicleowner->license_number ?? 'N/A' }}</td>
                    <td>{{ $vehicleowner->license_expiry ? $customer->license_expiry->format('d-m-Y') : 'N/A' }}</td>
                    <td>{!! $vehicleowner->status_badge !!}</td>
                    <td>
                        <a href="{{ route('admin.vehicleowner.edit', $vehicleowner->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>

                        <a href="{{ route('admin.vehicleowner.show', $vehicleowner->id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>

                        <form action="{{ route('admin.vehicleowner.destroy', $vehicleowner->id) }}"
                            method="POST"
                            style="display:inline-block;"
                            onsubmit="return confirm('Are you sure you want to delete this Owner?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm bg-red">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

</div>
</div>
</div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true
    });
});
</script>
@endpush
