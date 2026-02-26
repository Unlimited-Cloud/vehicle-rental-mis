{{-- resources/views/layouts/admin/customers/show.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Customer Details: {{ $customer->name }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-body">
                @include('layouts.admin_theme.alert')

                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">Company/Individual Name</th>
                                <td>{{ $customer->name }}</td>
                            </tr>
                            <tr>
                                <th>Full Name</th>
                                <td>{{ $customer->full_name }}</td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td>{{ $customer->phone }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $customer->email ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Address</th>
                                <td>{{ $customer->address ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">City</th>
                                <td>{{ $customer->city ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>State</th>
                                <td>{{ $customer->state ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>PAN Number</th>
                                <td>{{ $customer->pan_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>License Number</th>
                                <td>{{ $customer->license_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>License Expiry</th>
                                <td>{{ $customer->license_expiry ? $customer->license_expiry->format('d-m-Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>{!! $customer->status_badge !!}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <h4>Additional Information</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">Created At</th>
                                <td>{{ $customer->created_at ? $customer->created_at->format('d-m-Y H:i:s') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Last Updated</th>
                                <td>{{ $customer->updated_at ? $customer->updated_at->format('d-m-Y H:i:s') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="text-right mt-3">
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Customer
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</section>
@endsection