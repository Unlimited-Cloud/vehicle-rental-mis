@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Contact Us</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('admin.contact-us.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Add Contact
    </a>
</div>

<div class="table-responsive">
<table id="dataTable" class="table table-bordered table-striped show-search-bar">
<thead>
<tr>
    <th>SN</th>
    <th>Full Name</th>
    <th>Email</th>
    <th>Mobile Number</th>
    <th>Address</th>
    <th>WhatsApp</th>
    <th>Status</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>
@foreach($contacts as $c)
<tr>

    <td>{{ $loop->iteration }}</td>

    <td>{{ $c->full_name }}</td>

    <td>{{ $c->email }}</td>

    <td>{{ $c->mobile_number }}</td>

    <td>{{ $c->address }}</td>

    <td>{{ $c->whatsapp_number ?? 'N/A' }}</td>

    <td>
        @if($c->status == 'active')
            <span class="badge badge-success">Active</span>
        @else
            <span class="badge badge-danger">Inactive</span>
        @endif
    </td>

    <td>

        <a href="{{ route('admin.contact-us.edit', $c->id) }}"
           class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i>
        </a>

        <a href="{{ route('admin.contact-us.show', $c->id) }}"
           class="btn btn-info btn-sm">
            <i class="fas fa-eye"></i>
        </a>

        <form action="{{ route('admin.contact-us.destroy', $c->id) }}"
              method="POST"
              style="display:inline-block;"
              onsubmit="return confirm('Delete this contact?');">

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
    $('#dataTable').DataTable();
});
</script>
@endpush