@extends('layouts.admin_theme.container')

<!-- Content Wrapper. Contains page content -->
@section('dynamicdata')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-3 align-items-center">
            <div class="col-sm-12">
                <div class="d-flex align-items-center p-3 rounded shadow-sm bg-white">
                    <div class="mr-3">
                        <i class="fas fa-tachometer-alt fa-2x text-primary"></i>
                    </div>
                    <div>
                        <h1 class="m-0 font-weight-bold text-dark">
                            Welcome to <span class="text-primary">Vehicle Rental Pvt Ltd</span>
                        </h1>
                        <small class="text-muted">
                            Dashboard overview & system insights
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- @role(['admin', 'superadmin']) --}}
<section class="content">
    <div class="container-fluid">

        <div class="col-12">
            <h4 class="mb-3 font-weight-bold">Summary</h4>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-5">
            <!-- Total Passengers -->
            <div class="col mb-3">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <a href="{{ route('admin.vehicles.index') }}">
                            <span class="info-box-text font-weight-bold">Total Vehicles</span>
                        </a>
                        <h5><b>{{ $totalVehicles }}</b></h5>
                    </div>
                </div>
            </div>

    

        </div>

    </div>
</section>
{{-- @endrole --}}

@endsection
