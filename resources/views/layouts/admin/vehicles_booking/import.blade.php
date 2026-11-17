@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <i class="fas fa-file-import fa-2x text-primary mr-3"></i>
            <h1 class="m-0">Import Bookings</h1>
        </div>
        <a href="{{ route('admin.vehicle_bookings.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to Bookings
        </a>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @include('layouts.admin_theme.alert')

        @if(session('import_result'))
            @php $r = session('import_result'); @endphp
            <div class="card card-outline {{ count($r['errors']) ? 'card-warning' : 'card-success' }}">
                <div class="card-header"><h3 class="card-title">Import result</h3></div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>{{ $r['created'] }}</strong> of {{ $r['total'] }} booking(s) imported
                        @if(count($r['skipped'])) &middot; <strong>{{ count($r['skipped']) }}</strong> skipped @endif
                        @if(count($r['errors'])) &middot; <strong class="text-danger">{{ count($r['errors']) }}</strong> failed @endif
                    </p>

                    @if(count($r['errors']))
                        <h6 class="text-danger mt-3">Failed (fix these rows and import them again)</h6>
                        <ul class="mb-0">
                            @foreach($r['errors'] as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    @endif

                    @if(count($r['skipped']))
                        <h6 class="mt-3">Skipped</h6>
                        <ul class="mb-0">
                            @foreach($r['skipped'] as $s)
                                <li>{{ $s }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-5">
                <div class="card card-primary card-outline">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-upload mr-1"></i> Upload file</h3></div>
                    <form method="POST" action="{{ route('admin.vehicle_bookings.import.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label>Excel file (.xlsx / .xls, max 5 MB)</label>
                                <input type="file" name="file" class="form-control-file" accept=".xlsx,.xls" required>
                                @error('file') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                            </div>
                            <a href="{{ route('admin.vehicle_bookings.import.template') }}" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-download mr-1"></i> Download template
                            </a>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-file-import mr-1"></i> Import</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card card-secondary card-outline">
                    <div class="card-header"><h3 class="card-title">How it works</h3></div>
                    <div class="card-body">
                        <ul class="mb-2">
                            <li>The file has two sheets: <strong>Bookings</strong> (one row per booking) and <strong>Itineraries</strong> (one row per day).</li>
                            <li>Both are linked by the <code>ref</code> column, e.g. <code>B1</code>.</li>
                            <li><strong>Dynamic</strong> bookings: the amount is calculated from the itinerary rows and the vehicle rates.</li>
                            <li><strong>Static</strong> bookings: put the amount in the <code>amount</code> column; itinerary rows are optional.</li>
                            <li>Customer, vehicle, driver and helper must already exist.</li>
                            <li>Each booking is saved on its own, so a bad row does not stop the others.</li>
                        </ul>
                        <p class="mb-1"><strong>Required:</strong> <code>ref</code>, <code>customer</code>, <code>vehicle</code>, <code>start_date</code>, <code>start_time</code>, <code>end_date</code></p>
                        <p class="mb-0 text-muted">Open the template's <em>Instructions</em> sheet for every column and its allowed values.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection