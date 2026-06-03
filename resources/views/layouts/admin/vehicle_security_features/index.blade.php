@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Vehicle Security Features</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('admin.vehicle-security-features.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Add Security Features
    </a>
</div>

<div class="table-responsive">
<table id="dataTable" class="table table-bordered table-striped show-search-bar">
<thead>
<tr>
    <th>SN</th>
    <th>Vehicle</th>
    <th>Dash Cam</th>
    <th>EBS</th>
    <th>Reverse Camera</th>
    <th>360 Camera</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>

@foreach($features as $feature)
<tr>

    <td>{{ $loop->iteration }}</td>

    <td>
        {{ $feature->vehicle->vehicle_name ?? 'N/A' }}
    </td>

    <td>
        @if($feature->dash_cam)
            <span class="badge bg-success">Yes</span>
        @else
            <span class="badge bg-danger">No</span>
        @endif
    </td>

    <td>
        @if($feature->ebs)
            <span class="badge bg-success">Yes</span>
        @else
            <span class="badge bg-danger">No</span>
        @endif
    </td>

    <td>
        @if($feature->reverse_camera)
            <span class="badge bg-success">Yes</span>
        @else
            <span class="badge bg-danger">No</span>
        @endif
    </td>

    <td>
        @if($feature->camera_360)
            <span class="badge bg-success">Yes</span>
        @else
            <span class="badge bg-danger">No</span>
        @endif
    </td>

    <td>

        <a href="{{ route('admin.vehicle-security-features.edit',$feature->id) }}"
           class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i>
        </a>
        <a href="{{ route('admin.vehicle-security-features.show',$feature->id) }}"
            class="btn btn-success btn-sm">
                <i class="fas fa-eye"></i>
        </a>

        <form action="{{ route('admin.vehicle-security-features.destroy',$feature->id) }}"
              method="POST"
              style="display:inline-block"
              onsubmit="return confirm('Delete this record?');">

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