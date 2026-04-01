@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Basic Setups</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('admin.basic_tables.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Add Data
    </a>
</div>

<div class="table-responsive">
<table id="dataTable" class="table table-bordered table-striped show-search-bar">
<thead>
<tr>
    <th>SN</th>
    <th>Logo</th>
    <th>Login Logo</th>
    <th>Company Name</th>
    <th>Footer Text</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>
@foreach($items as $item)
<tr>
    <td>{{ $loop->iteration }}</td>

    <td>
        @if($item->logo)
            <img src="{{ asset($item->logo) }}" width="80">
        @else
            N/A
        @endif
    </td>

    <td>
        @if($item->login_logo)
            <img src="{{ asset($item->login_logo) }}" width="80">
        @else
            N/A
        @endif
    </td>

    <td>{{ $item->company_name ?? 'N/A' }}</td>

    <td>{{ $item->footer_text ?? 'N/A' }}</td>

    <td>
        <a href="{{ route('admin.basic_tables.edit', $item->id) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i>
        </a>

        <a href="{{ route('admin.basic_tables.show', $item->id) }}" class="btn btn-info btn-sm">
            <i class="fas fa-eye"></i>
        </a>

        <form action="{{ route('admin.basic_tables.destroy', $item->id) }}"
              method="POST"
              style="display:inline-block;"
              onsubmit="return confirm('Are you sure you want to delete this data?');">
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