@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>Payment Modes</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('admin.payment-modes.create') }}"
       class="btn btn-sm btn-primary">

        <i class="fa fa-plus"></i> Add Payment Mode
    </a>
</div>

<div class="table-responsive">

<table id="dataTable"
       class="table table-bordered table-striped show-search-bar">

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

@foreach($paymentModes as $pm)

<tr>

<td>{{ $loop->iteration }}</td>

<td>
@if($pm->logo)
    <img src="{{ asset('uploads/payment_modes/'.$pm->logo) }}"
         width="50"
         height="50">
@else
    N/A
@endif
</td>

<td>{{ $pm->name }}</td>

<td>
@if($pm->status)
    <span class="badge badge-success">Active</span>
@else
    <span class="badge badge-danger">Inactive</span>
@endif
</td>

<td>

<a href="{{ route('admin.payment-modes.edit', $pm->id) }}"
   class="btn btn-primary btn-sm">
    <i class="fas fa-edit"></i>
</a>

{{-- 
<a href="{{ route('admin.payment-mode.show', $pm->id) }}"
   class="btn btn-info btn-sm">
    <i class="fas fa-eye"></i>
</a>
--}}

<form action="{{ route('admin.payment-modes.destroy', $pm->id) }}"
      method="POST"
      style="display:inline-block;"
      onsubmit="return confirm('Delete this payment mode?');">

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
$(document).ready(function () {
    $('#dataTable').DataTable();
});
</script>
@endpush