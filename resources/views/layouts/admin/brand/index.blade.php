@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Brands</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('admin.brand.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Add Brand
    </a>
</div>

<div class="table-responsive">
<table id="dataTable" class="table table-bordered table-striped show-search-bar">
<thead>
<tr>
    <th>SN</th>
    <th>Logo</th>
    <th>Name</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
@foreach($brands as $b)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>
    @if($b->logo)
        <img src="{{ asset('uploads/brands/'.$b->logo) }}" width="50" height="50">
    @else
        N/A
    @endif
    </td>
    <td>{{ $b->name }}</td>
    <td>
        <a href="{{ route('admin.brand.edit', $b->id) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i>
        </a>

        {{-- <a href="{{ route('admin.brand.show', $b->id) }}" class="btn btn-info btn-sm">
            <i class="fas fa-eye"></i>
        </a> --}}

        <form action="{{ route('admin.brand.destroy', $b->id) }}"
              method="POST"
              style="display:inline-block;"
              onsubmit="return confirm('Delete this brand?');">
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