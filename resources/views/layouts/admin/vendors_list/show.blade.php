@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Repair Shop Details</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<div class="card-body">
    <table class="table table-bordered">
        <tr>
            <th>Company Name</th>
            <td>{{ $vendor->company_name }}</td>
        </tr>
        <tr>
            <th>Contact Person</th>
            <td>{{ $vendor->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Phone</th>
            <td>{{ $vendor->contact ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $vendor->email ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Address</th>
            <td>{{ $vendor->address ?? 'N/A' }}</td>
        </tr>
        
        
    </table>
</div>

<div class="card-footer">
    <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back
    </a>
</div>

</div>
</div>
</section>
@endsection