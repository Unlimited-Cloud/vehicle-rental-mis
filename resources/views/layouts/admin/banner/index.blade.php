@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Banners</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('admin.banner.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Add Banner
    </a>
</div>

<div class="table-responsive">
<table id="dataTable" class="table table-bordered table-striped show-search-bar">
<thead>
<tr>
    <th>SN</th>
    <th>Image</th>
    <th>Title</th>
    <th>Order</th>
    <th>Status</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
@foreach($banners as $b)
<tr>
    <td>{{ $loop->iteration }}</td>

    <td>
    @if($b->image)
        <img src="{{ asset('uploads/banners/'.$b->image) }}" width="70" height="50">
    @else
        N/A
    @endif
    </td>

    <td>{{ $b->title }}</td>

    <td>{{ $b->order }}</td>

    <td>
        @if($b->is_active)
            <span class="badge bg-success">Active</span>
        @else
            <span class="badge bg-danger">Inactive</span>
        @endif
    </td>

    <td>
        <a href="{{ route('admin.banner.edit', $b->id) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i>
        </a>

        <form action="{{ route('admin.banner.destroy', $b->id) }}"
              method="POST"
              style="display:inline-block;"
              onsubmit="return confirm('Delete this banner?');">
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