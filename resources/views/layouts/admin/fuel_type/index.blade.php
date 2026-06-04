@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>Fuel Types</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('admin.fuel-type.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Add Fuel Type
    </a>
</div>

<div class="table-responsive">
<table id="dataTable" class="table table-bordered table-striped show-search-bar">
<thead>
<tr>
    <th>SN</th>
    <th>Logo</th>
    <th>Name</th>
    <th>Status</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
@foreach($fuelTypes as $fuelType)
<tr>
    <td>{{ $loop->iteration }}</td>

<td>
    @if($fuelType->logo)
        <img src="{{ asset('uploads/fuel-types/'.$fuelType->logo) }}" width="60" height="60">
    @else
        N/A
    @endif
</td>

<td>{{ $fuelType->name }}</td>

<td>
    @if($fuelType->status)
        <span class="badge bg-success">Active</span>
    @else
        <span class="badge bg-danger">Inactive</span>
    @endif
</td>

<td>
    <a href="{{ route('admin.fuel-type.edit', $fuelType->id) }}"
       class="btn btn-primary btn-sm">
        <i class="fas fa-edit"></i>
    </a>

    <form action="{{ route('admin.fuel-type.destroy', $fuelType->id) }}"
          method="POST"
          style="display:inline-block;"
          onsubmit="return confirm('Delete this fuel type?');">
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
