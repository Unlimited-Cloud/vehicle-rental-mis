@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Splashscreens</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('admin.splashscreen.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Add Splashscreen
    </a>
</div>

<div class="table-responsive">
<table id="dataTable" class="table table-bordered table-striped show-search-bar">
<thead>
<tr>
    <th>SN</th>
    <th>Image</th>
    <th>Header</th>
    <th>Description</th>
    <th>Order</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
@foreach($splashscreens as $s)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>
    @if($s->image)
        <img src="{{ asset('uploads/splashscreens/'.$s->image) }}" width="50" height="50">
    @else
        N/A
    @endif
    </td>
    <td>{{ $s->header }}</td>
    <td>{{ $s->description }}</td>
    <td>{{ $s->order }}</td>
    <td>
        <a href="{{ route('admin.splashscreen.edit', $s->id) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i>
        </a>

        {{-- <a href="{{ route('admin.splashscreen.show', $s->id) }}" class="btn btn-info btn-sm">
            <i class="fas fa-eye"></i>
        </a> --}}

        <form action="{{ route('admin.splashscreen.destroy', $s->id) }}"
              method="POST"
              style="display:inline-block;"
              onsubmit="return confirm('Delete this splashscreen?');">
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