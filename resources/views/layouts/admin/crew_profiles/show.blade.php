@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark">Crew Profile Details</h1>
            <p class="text-muted mt-1 mb-0">Complete information and credentials</p>
        </div>
        <a href="{{ route('admin.crew_profiles.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<div class="row">
    <!-- Left Column: User Information -->
    <div class="col-md-6">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title"><i class="fas fa-user-circle"></i> User Information</h3>
                <div class="card-tools">
                    <span class="badge bg-light text-primary">{{ ucfirst($crew_profile->role) }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    @if(!empty($crew_profile->user->img))
                        <img src="{{ asset('uploads/users/' . $crew_profile->user->img) }}" 
                             alt="Profile Image" 
                             class="profile-user-img img-fluid img-circle"
                             style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #adb5bd;">
                    @else
                        <div class="profile-user-img img-fluid img-circle bg-secondary d-flex align-items-center justify-content-center mx-auto" 
                             style="width: 120px; height: 120px;">
                            <i class="fas fa-user fa-3x text-white"></i>
                        </div>
                    @endif
                </div>
                
                <table class="table table-bordered table-hover">
                    <tbody>
                        <tr class="bg-light">
                            <th style="width: 40%">Full Name</th>
                            <td style="width: 60%">{{ $crew_profile->user->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Email Address</th>
                            <td>{{ $crew_profile->user->email ?? 'N/A' }}</td>
                        </tr>
                        <tr class="bg-light">
                            <th>Contact Number</th>
                            <td>{{ $crew_profile->contact_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Years of Experience</th>
                            <td>
                                @if($crew_profile->experience)
                                    <span class="badge bg-success">{{ $crew_profile->experience }} years</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="bg-light">
                            <th>Member Since</th>
                            <td>{{ $crew_profile->created_at ? $crew_profile->created_at->format('M d, Y') : 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: License & Documents -->
    <div class="col-md-6">
        <div class="card card-info card-outline shadow-sm">
            <div class="card-header bg-info text-white">
                <h3 class="card-title"><i class="fas fa-id-card"></i> License & Documents</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <tbody>
                        <tr class="bg-light">
                            <th style="width: 40%">License Number</th>
                            <td style="width: 60%">
                                @if($crew_profile->license_number)
                                    <code>{{ $crew_profile->license_number }}</code>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>License Expiry Date</th>
                            <td>
                                @if($crew_profile->license_expiry)
                                    @php
                                        $expiryDate = \Carbon\Carbon::parse($crew_profile->license_expiry);
                                        $isExpired = $expiryDate->isPast();
                                    @endphp
                                    <span class="badge {{ $isExpired ? 'bg-danger' : 'bg-warning' }}">
                                        {{ $expiryDate->format('M d, Y') }}
                                        @if($isExpired) (Expired) @endif
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="bg-light">
                            <th>Citizenship Document</th>
                            <td>
                                @if($crew_profile->citizenship_doc)
                                    @php
                                        $ext = strtolower(pathinfo($crew_profile->citizenship_doc, PATHINFO_EXTENSION));
                                        $filePath = asset($crew_profile->citizenship_doc);
                                    @endphp
                                    @if(in_array($ext, ['jpg','jpeg','png']))
                                        <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-image"></i> Preview Image
                                        </a>
                                    @elseif($ext === 'pdf')
                                        <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-file-pdf"></i> View PDF
                                        </a>
                                    @else
                                        <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-file"></i> View Document
                                        </a>
                                    @endif
                                @else
                                    <span class="text-muted">Not uploaded</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>

                @if($crew_profile->citizenship_doc && in_array(strtolower(pathinfo($crew_profile->citizenship_doc, PATHINFO_EXTENSION)), ['jpg','jpeg','png']))
                    <div class="mt-3 text-center border rounded p-2 bg-light">
                        <small class="text-muted">Document Preview</small>
                        <img src="{{ asset($crew_profile->citizenship_doc) }}" alt="Citizenship Document" class="img-fluid mt-1" style="max-height: 180px;">
                    </div>
                @endif
            </div>
        </div>

        <!-- Additional Credentials Card (Optional) -->
        <div class="card card-secondary card-outline shadow-sm mt-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-shield-alt"></i> Credentials</h3>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 border-right">
                        <strong>Status</strong><br>
                        <span class="badge bg-success mt-1 p-2">Active</span>
                    </div>
                    <div class="col-6">
                        <strong>Verification</strong><br>
                        <span class="badge bg-info mt-1 p-2">Verified</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Footer -->
<div class="row mt-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> Last updated: {{ $crew_profile->updated_at ? $crew_profile->updated_at->diffForHumans() : 'N/A' }}
                    </small>
                </div>
                <div>
                    <a href="{{ route('admin.crew_profiles.edit', $crew_profile->id) }}" class="btn btn-primary px-4">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                    <button type="button" class="btn btn-danger ml-2" onclick="confirmDelete({{ $crew_profile->id }})">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
</section>

@push('scripts')
<script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this crew profile? This action cannot be undone.')) {
            // Submit delete form or AJAX call
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>
@endpush

@endsection