@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Crew Profile</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.crew_profiles.index') }}">Crew Profiles</a></li>
                    <li class="breadcrumb-item active">Profile Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<div class="row">
    <div class="col-md-4">
        <!-- Profile Image -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    @if(!empty($crew_profile->user->img))
                        <img class="profile-user-img img-fluid img-circle" 
                             src="{{ asset('uploads/users/' . $crew_profile->user->img) }}" 
                             alt="Profile picture"
                             style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                        <div class="profile-user-img img-fluid img-circle bg-secondary d-flex align-items-center justify-content-center mx-auto" 
                             style="width: 150px; height: 150px;">
                            <i class="fas fa-user fa-4x text-white"></i>
                        </div>
                    @endif
                </div>

                <h3 class="profile-username text-center mt-3">{{ $crew_profile->user->name ?? 'N/A' }}</h3>
                <p class="text-muted text-center">
                    <span class="badge badge-primary">{{ ucfirst($crew_profile->role) }}</span>
                </p>

                <ul class="list-group list-group-unbordered mb-3">
                     <li class="list-group-item">
                        <b>Owner</b> 
                        <a class="float-right">{{ $crew_profile->vehicleOwner->name ?? 'N/A' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Experience</b> 
                        <a class="float-right">{{ $crew_profile->experience ?? '0' }} years</a>
                    </li>
                    <li class="list-group-item">
                        <b>Member Since</b> 
                        <a class="float-right">{{ $crew_profile->created_at ? $crew_profile->created_at->format('M d, Y') : 'N/A' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Status</b> 
                        <a class="float-right"><span class="badge badge-success">Active</span></a>
                    </li>
                </ul>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.crew_profiles.edit', $crew_profile->id) }}" class="btn btn-primary btn-block mr-1">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <button type="button" class="btn btn-danger btn-block ml-1" onclick="confirmDelete({{ $crew_profile->id }})">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">Contact Information</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                        <p class="text-muted mt-1">{{ $crew_profile->user->email ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <strong><i class="fas fa-phone mr-1"></i> Phone</strong>
                        <p class="text-muted mt-1">{{ $crew_profile->contact_number ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- License Information -->
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">License Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <div class="info-box-content">
                                <span class="info-box-text text-muted">License Number</span>
                                <span class="info-box-number">{{ $crew_profile->license_number ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <div class="info-box-content">
                                <span class="info-box-text text-muted">Expiry Date</span>
                                <span class="info-box-number">
                                    @if($crew_profile->license_expiry)
                                        @php $isExpired = \Carbon\Carbon::parse($crew_profile->license_expiry)->isPast(); @endphp
                                        <span class="{{ $isExpired ? 'text-danger' : 'text-warning' }}">
                                            {{ \Carbon\Carbon::parse($crew_profile->license_expiry)->format('M d, Y') }}
                                            @if($isExpired) <small>(Expired)</small> @endif
                                        </span>
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Citizenship Document -->
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h3 class="card-title">Citizenship Document</h3>
            </div>
            <div class="card-body">
                @if($crew_profile->citizenship_doc)
                    @php
                        $ext = strtolower(pathinfo($crew_profile->citizenship_doc, PATHINFO_EXTENSION));
                        $filePath = asset($crew_profile->citizenship_doc);
                    @endphp
                    <div class="text-center">
                        @if(in_array($ext, ['jpg','jpeg','png']))
                            <img src="{{ $filePath }}" alt="Citizenship Document" class="img-fluid mb-2" style="max-height: 200px;">
                            <br>
                            <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View Full Size
                            </a>
                        @elseif($ext === 'pdf')
                            <div class="text-center py-4">
                                <i class="fas fa-file-pdf fa-4x text-danger mb-2"></i>
                                <p>PDF Document Available</p>
                                <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-danger">
                                    <i class="fas fa-download"></i> View PDF
                                </a>
                            </div>
                        @else
                            <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-info">
                                <i class="fas fa-file"></i> View Document
                            </a>
                        @endif
                    </div>
                @else
                    <p class="text-muted text-center mb-0">No citizenship document uploaded</p>
                @endif
            </div>
        </div>

        <!-- Bank Details -->
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">Bank Account Details</h3>
                <div class="card-tools">
                    <span class="badge badge-success">{{ $crew_profile->bankDetails->count() }} Account(s)</span>
                </div>
            </div>
            <div class="card-body">
                @if($crew_profile->bankDetails->count())
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Bank Name</th>
                                    <th>Account Holder</th>
                                    <th>Account Number</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($crew_profile->bankDetails as $bank)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><strong>{{ $bank->bank_name }}</strong> <br><small class="text-muted">Code: {{ $bank->bank_code }}</small></td>
                                        <td>{{ $bank->account_holder_name }}</td>
                                        <td><code>{{ $bank->account_number }}</code></td>
                                        <td class="text-center">
                                            @if($bank->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center mb-0">No bank details available</p>
                @endif
            </div>
        </div>
    </div>
</div>

<form id="delete-form-{{ $crew_profile->id }}" action="{{ route('admin.crew_profiles.destroy', $crew_profile->id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

</div>
</section>

@push('scripts')
<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this crew profile? This action cannot be undone.')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endpush

@endsection