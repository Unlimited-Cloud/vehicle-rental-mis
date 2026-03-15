@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
<div class="container-fluid">
<h1>Trip Categories</h1>
</div>
</div>

<section class="content">
<div class="container-fluid">

<div class="card">
<div class="card-body">

@include('layouts.admin_theme.alert')

<a href="{{ route('admin.trip-categories.create') }}" class="btn btn-primary mb-3">
Add Category
</a>

<table id="dataTable" class="table table-bordered table-striped show-search-bar">
<thead>
<tr>
<th>SN</th>
<th>Name</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>

@foreach($categories as $category)

<tr>
<td>{{ $loop->iteration }}</td>

<td>{{ $category->name }}</td>

<td>
@if($category->status)
<span class="badge badge-success">Active</span>
@else
<span class="badge badge-danger">Inactive</span>
@endif
</td>

<td>

<a href="{{ route('admin.trip-categories.edit',$category->id) }}" 
class="btn btn-sm btn-primary">
Edit
</a>

<form action="{{ route('admin.trip-categories.destroy',$category->id) }}"
method="POST" style="display:inline-block">

@csrf
@method('DELETE')

<button class="btn btn-danger btn-sm">
Delete
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