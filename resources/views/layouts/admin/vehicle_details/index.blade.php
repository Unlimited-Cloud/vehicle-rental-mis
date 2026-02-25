@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Vehicle Details</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('admin.vehicle_details.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Add Vehicle Details
    </a>
</div>

<table class="table table-bordered table-striped">
<thead>
<tr>
    <th>SN</th>
    <th>Vehicle</th>
    <th>Blue Book</th>
    <th>Insurance Number</th>
    <th>Insurance Expiry</th>
    <th>Permit Number</th>
    <th>Permit Expiry</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
@foreach($details as $detail)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $detail->vehicle->vehicle_name ?? 'N/A' }}</td>
    <td>{{ $detail->blue_book_number ?? 'N/A' }}</td>
    <td>{{ $detail->insurance_number ?? 'N/A' }}</td>
    <td>{{ $detail->insurance_expiry ?? 'N/A' }}</td>
    <td>{{ $detail->permit_number ?? 'N/A' }}</td>
    <td>{{ $detail->permit_expiry ?? 'N/A' }}</td>
    <td>
        <a href="{{ route('admin.vehicle_details.edit', $detail->id) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i>
        </a>

         <a href="{{ route('admin.vehicle_details.show', $detail->id) }}" class="btn btn-info btn-sm">
             <i class="fas fa-eye"></i>
        </a>

        <form action="{{ route('admin.vehicle_details.destroy', $detail->id) }}"
              method="POST"
              style="display:inline-block;"
              onsubmit="return confirm('Are you sure you want to delete this record?');">
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
</section>
@endsection