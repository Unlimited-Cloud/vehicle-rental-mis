{{-- resources/views/layouts/admin/customers/index.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Customers (Vendors/Agents)</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('admin.customers.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Add Customer
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
            @foreach($customers as $customer)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $customer->name }}</td>
                <td>{{ $customer->phone }}</td>
                <td>{{ $customer->email ?? 'N/A' }}</td>
                <td>{{ $customer->city ?? 'N/A' }}</td>
                <td>{{ $customer->license_number ?? 'N/A' }}</td>
                <td>{{ $customer->license_expiry ? $customer->license_expiry->format('d-m-Y') : 'N/A' }}</td>
                <td>{!! $customer->status_badge !!}</td>
                <td>
                    <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i>
                    </a>

                    <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-eye"></i>
                    </a>

                    <form action="{{ route('admin.customers.destroy', $customer->id) }}"
                          method="POST"
                          style="display:inline-block;"
                          onsubmit="return confirm('Are you sure you want to delete this customer?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm bg-red">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
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
