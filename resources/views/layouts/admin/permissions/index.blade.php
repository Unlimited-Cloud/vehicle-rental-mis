@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Permission Lists</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="row">
<div class="col-12">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

@if(auth()->user()->can('create_configuration_permissions'))
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('admin.permissions.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Add New Permission
    </a>
</div>
@endif

 <table id="dataTable" class="table table-bordered table-striped show-search-bar">
<thead>
<tr>
    <th>SN</th>
    <th>Module</th>
    <th>Sub Module</th>
    <th>Name</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>

@foreach($permissions as $permission)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $permission->modulename }}</td>
    <td>{{ $permission->submodule_name }}</td>
    <td>{{ $permission->name }}</td>

    <td>
        @if(auth()->user()->can('update_configuration_modules'))
        <a href="{{ route('admin.permissions.edit', $permission->id) }}"
           class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i>
        </a>
        @endif
        @if(auth()->user()->can('delete_configuration_modules'))
        <form action="{{ route('admin.permissions.destroy', $permission->id) }}"
              method="POST"
              style="display:inline-block;"
              onsubmit="return confirm('Are you sure you want to delete this user?');">
            @csrf
            @method('DELETE')

            <button type="submit" class="btn btn-sm bg-red">
                <i class="fa fa-trash"></i>
            </button>
        </form>
        @endif
    </td>
</tr>
@endforeach

</tbody>
</table>

</div>
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
