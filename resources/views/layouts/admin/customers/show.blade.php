{{-- resources/views/layouts/admin/customers/show.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Customer Details</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                    <li class="breadcrumb-item active">View Customer</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                @include('layouts.admin_theme.alert')

                {{-- Customer Profile Card --}}
                <div class="card card-primary card-outline card-tabs">
                    <div class="card-header p-0 pt-1 border-bottom-0">
                        <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="custom-tabs-two-profile-tab" data-toggle="pill" href="#custom-tabs-two-profile" role="tab" aria-controls="custom-tabs-two-profile" aria-selected="true">Profile Information</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-two-activity-tab" data-toggle="pill" href="#custom-tabs-two-activity" role="tab" aria-controls="custom-tabs-two-activity" aria-selected="false">Recent Activity</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-two-tabContent">
                            {{-- Profile Tab --}}
                            <div class="tab-pane fade show active" id="custom-tabs-two-profile" role="tabpanel" aria-labelledby="custom-tabs-two-profile-tab">
                                <div class="row">
                                    {{-- Left Column: Customer Details --}}
                                    <div class="col-md-8">
                                        <h5 class="text-primary mb-3"><i class="fas fa-user-circle"></i> Personal Information</h5>
                                        <div class="row">
                                             <div class="col-sm-6">
                                                <div class="info-box bg-light">
                                                    <div class="info-box-content">
                                                        <span class="info-box-text text-muted">UUID</span>
                                                        <span class="info-box-number">{{ $customer->customer_uuid }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="info-box bg-light">
                                                    <div class="info-box-content">
                                                        <span class="info-box-text text-muted">Company/Individual Name</span>
                                                        <span class="info-box-number">{{ $customer->name }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="info-box bg-light">
                                                    <div class="info-box-content">
                                                        <span class="info-box-text text-muted">Full Name</span>
                                                        <span class="info-box-number">{{ $customer->full_name }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="info-box bg-light">
                                                    <div class="info-box-content">
                                                        <span class="info-box-text text-muted">Phone Number</span>
                                                        <span class="info-box-number">{{ $customer->phone }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="info-box bg-light">
                                                    <div class="info-box-content">
                                                        <span class="info-box-text text-muted">Email Address</span>
                                                        <span class="info-box-number">{{ $customer->email ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="info-box bg-light">
                                                    <div class="info-box-content">
                                                        <span class="info-box-text text-muted">Address</span>
                                                        <span class="info-box-number">{{ $customer->address ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <h5 class="text-primary mb-3 mt-3"><i class="fas fa-map-marker-alt"></i> Location & Tax Info</h5>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="info-box bg-light">
                                                    <div class="info-box-content">
                                                        <span class="info-box-text text-muted">City</span>
                                                        <span class="info-box-number">{{ $customer->city ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="info-box bg-light">
                                                    <div class="info-box-content">
                                                        <span class="info-box-text text-muted">State</span>
                                                        <span class="info-box-number">{{ $customer->state ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="info-box bg-light">
                                                    <div class="info-box-content">
                                                        <span class="info-box-text text-muted">PAN Number</span>
                                                        <span class="info-box-number">{{ $customer->pan_number ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <h5 class="text-primary mb-3 mt-3"><i class="fas fa-history"></i> System Information</h5>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="info-box bg-light">
                                                    <div class="info-box-content">
                                                        <span class="info-box-text text-muted">Created At</span>
                                                        <span class="info-box-number">{{ $customer->created_at ? $customer->created_at->format('d-m-Y H:i:s') : 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="info-box bg-light">
                                                    <div class="info-box-content">
                                                        <span class="info-box-text text-muted">Last Updated</span>
                                                        <span class="info-box-number">{{ $customer->updated_at ? $customer->updated_at->format('d-m-Y H:i:s') : 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Right Column: Profile Image & Status --}}
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <div class="position-relative d-inline-block">
                                                @if(!empty($customer->profile_image))
                                                    <img src="{{ asset($customer->profile_image) }}" 
                                                        alt="Profile Image"
                                                        class="img-circle elevation-2"
                                                        style="width: 150px; height: 150px; object-fit: contain; background: #f8f9fa; border: 3px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                                                @else
                                                    <img src="{{ asset('adminlte/dist/img/avatar4.png') }}" 
                                                        alt="Default Image"
                                                        class="img-circle elevation-2"
                                                        style="width: 150px; height: 150px; object-fit: contain; background: #f8f9fa; border: 3px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                                                @endif
                                            </div>
                                            <div class="mt-3">
                                                <h5>{{ $customer->full_name }}</h5>
                                                <p class="text-muted">{{ $customer->name }}</p>
                                                <div class="mt-2">
                                                    {!! $customer->status_badge !!}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4 pt-3 border-top">
                                            <h6 class="text-muted">Quick Actions</h6>
                                            <div class="btn-group-vertical w-100" role="group">
                                                <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-outline-primary btn-block text-left">
                                                    <i class="fas fa-edit"></i> Edit Customer
                                                </a>
                                                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-block text-left">
                                                    <i class="fa fa-arrow-left"></i> Back to List
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Activity Tab (Placeholder for future enhancement) --}}
                            <div class="tab-pane fade" id="custom-tabs-two-activity" role="tabpanel" aria-labelledby="custom-tabs-two-activity-tab">
                                <div class="text-center py-5">
                                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Recent activity timeline will be displayed here.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- /.card-body --}}
                    <div class="card-footer text-muted text-center">
                        <small>Customer ID: {{ $customer->id }} | Last login: N/A</small>
                    </div>
                </div>
                {{-- /.card --}}
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(function () {
        // Enable Bootstrap tabs if not already enabled
        if (typeof $.fn.tab !== 'undefined') {
            $('[data-toggle="pill"]').on('shown.bs.tab', function (e) {
                // Optional: Add any tab change logic here
            });
        }
    });
</script>
@endpush