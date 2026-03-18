@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Repair Shop</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('admin.vendors.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Add Repair Shop
    </a>
</div>

<div class="table-responsive">
    <table id="dataTable" class="table table-bordered table-striped show-search-bar">
        <thead>
            <tr>
                <th>SN</th>
                <th>Company Name</th>
                <th>Contact Person</th>
                <th>Phone</th>
                <th>Email</th>
                <th>City</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendors as $vendor)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $vendor->company_name }}</td>
                <td>{{ $vendor->name ?? 'N/A' }}</td>
                <td>{{ $vendor->contact ?? 'N/A' }}</td>
                <td>{{ $vendor->email ?? 'N/A' }}</td>
                <td>{{ $vendor->address ?? 'N/A' }}</td>
                <td>
                    <a href="{{ route('admin.vendors.edit', $vendor->id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i>
                    </a>

                    <a href="{{ route('admin.vendors.show', $vendor->id) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-eye"></i>
                    </a>

                    <form action="{{ route('admin.vendors.destroy', $vendor->id) }}"
                          method="POST"
                          style="display:inline-block;"
                          onsubmit="return confirm('Are you sure you want to delete this vendor?');">
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