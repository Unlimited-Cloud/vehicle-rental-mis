@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Crew Profiles</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')
@can('create_crew_profiles')
<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('admin.crew_profiles.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Add Crew Profile
    </a>
</div>
@endcan
<div class="table-responsive">
<table id="dataTable" class="table table-bordered table-striped show-search-bar">
<thead>
<tr>
    <th>SN</th>
    <th>User</th>
    <th>Role</th>
    <th>License Number</th>
    <th>License Expiry</th>
    <th>Contact</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
@foreach($crew as $c)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $c->user->name ?? 'N/A' }}</td>
    <td>{{ ucfirst($c->role) }}</td>
    <td>{{ $c->license_number ?? 'N/A' }}</td>
    <td>{{ $c->license_expiry ?? 'N/A' }}</td>
    <td>{{ $c->contact_number ?? 'N/A' }}</td>
    <td>
        <a href="{{ route('admin.crew_profiles.edit', $c->id) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i>
        </a>

         <a href="{{ route('admin.crew_profiles.show', $c->id) }}" class="btn btn-info btn-sm">
            <i class="fas fa-eye"></i>
        </a>

        <form action="{{ route('admin.crew_profiles.destroy', $c->id) }}"
              method="POST"
              style="display:inline-block;"
              onsubmit="return confirm('Are you sure you want to delete this crew profile?');">
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