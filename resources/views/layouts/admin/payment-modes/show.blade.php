@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1>Crew Profile Details</h1>
        <a href="{{ route('admin.crew_profiles.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<div class="row">

    <!-- User Info Card -->
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user"></i> User Information</h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th>Name:</th>
                        <td>{{ $crew_profile->user->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $crew_profile->user->email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Role:</th>
                        <td>{{ ucfirst($crew_profile->role) }}</td>
                    </tr>
                    <tr>
                        <th>Contact Number:</th>
                        <td>{{ $crew_profile->contact_number ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Experience Years:</th>
                        <td>{{ $crew_profile->experience ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- License & Documents Card -->
    <div class="col-md-6">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-id-card"></i> License & Documents</h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th>License Number:</th>
                        <td>{{ $crew_profile->license_number ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>License Expiry:</th>
                        <td>{{ $crew_profile->license_expiry ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Citizenship Document:</th>
                        <td>
                            @if($crew_profile->citizenship_doc)
                                @php
                                    $ext = pathinfo($crew_profile->citizenship_doc, PATHINFO_EXTENSION);
                                @endphp
                                @if(in_array(strtolower($ext), ['jpg','jpeg','png']))
                                    <img src="{{ asset($crew_profile->citizenship_doc) }}" alt="Citizenship" width="200" class="img-thumbnail">
                                @elseif(strtolower($ext) === 'pdf')
                                    <a href="{{ asset($crew_profile->citizenship_doc) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                        <i class="fa fa-file-pdf"></i> View PDF
                                    </a>
                                @else
                                    <a href="{{ asset($crew_profile->citizenship_doc) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        View Document
                                    </a>
                                @endif
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Footer with Actions -->
<div class="row">
    <div class="col-12 text-right">
        <a href="{{ route('admin.crew_profiles.edit', $crew_profile->id) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit Profile
        </a>
    </div>
</div>

</div>
</section>

@endsection