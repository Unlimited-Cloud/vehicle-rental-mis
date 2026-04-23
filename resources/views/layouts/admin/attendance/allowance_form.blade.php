{{-- resources/views/layouts/admin/attendance/allowance_form.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="m-0">Add Allowance / Bhatta</h1>
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.vehicle_moments.index') }}">Vehicle Movements</a></li>
                <li class="breadcrumb-item active">Add Allowance</li>
            </ol>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-money-bill-wave"></i> Add Bhatta/Allowance for Trip
                </h3>
            </div>
            
            <form action="{{ route('admin.attendance.storeAllowance') }}" method="POST" id="allowanceForm">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vehicle_info">Vehicle Information</label>
                                <input type="text" class="form-control" value="{{ $vehicleMoment->vehicle->vehicle_name ?? 'N/A' }}" readonly>
                                <small class="text-muted">Trip Date: {{ $vehicleMoment->created_at ?? 'N/A' }}</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="crew_info">Driver Information</label>
                                <input type="text" class="form-control" 
                                       value="{{ $vehicleMoment->driver->user->name ?? 'N/A' }}" readonly>
                                <input type="hidden" name="crew_id" value="{{ $crewId }}">
                                <input type="hidden" name="vehicle_moment_id" value="{{ $vehicleMoment->id }}">
                                <input type="hidden" name="booking_id" value="{{ $vehicleMoment->booking_id ?? '' }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="attendance_date">Attendance Date *</label>
                                <input type="date" class="form-control @error('attendance_date') is-invalid @enderror" 
                                       name="attendance_date" value="{{ old('attendance_date', $today) }}" required>
                                @error('attendance_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="allowances">Allowance / Bhatta Amount (NPR) *</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">रू</span>
                                    </div>
                                    <input type="number" step="0.01" class="form-control @error('allowances') is-invalid @enderror" 
                                           name="allowances" value="{{ old('allowances', $existingAttendance->allowances ?? 0) }}" 
                                           placeholder="Enter allowance amount" required>
                                </div>
                                @error('allowances')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="remarks">Remarks</label>
                                <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                          name="remarks" rows="3" placeholder="Any additional notes...">{{ old('remarks', $existingAttendance->remarks ?? '') }}</textarea>
                                @error('remarks')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    @if($existingAttendance)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Note: An attendance record already exists for this driver on {{ $today }}. 
                            This will update the existing record with the allowance amount.
                        </div>
                    @endif
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Status</label>
                                <div class="form-control" style="background-color: #e9ecef;">
                                    <i class="fas fa-check-circle text-success"></i> Present (Auto-set)
                                </div>
                                <small class="text-muted">Status will be automatically set to "Present"</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Allowance
                    </button>
                    <a href="{{ route('admin.vehicle_moments.index') }}" class="btn btn-default">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#allowanceForm').on('submit', function(e) {
        // Optional: Add confirmation for allowance amount
        var allowance = $('input[name="allowances"]').val();
        if (allowance && confirm('Add allowance of NPR ' + allowance + ' for this trip?')) {
            return true;
        } else if (!allowance) {
            alert('Please enter an allowance amount');
            e.preventDefault();
            return false;
        }
        return true;
    });
});
</script>
@endpush