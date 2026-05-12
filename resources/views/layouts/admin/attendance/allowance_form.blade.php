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
                    <!-- Vehicle Information -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="vehicle_info">Vehicle Information</label>
                                <input type="text" class="form-control" value="{{ $vehicleMoment->vehicle->vehicle_name ?? 'N/A' }}" readonly>
                                <small class="text-muted">Trip Date: {{ $vehicleMoment->created_at ?? 'N/A' }}</small>
                                <input type="hidden" name="vehicle_moment_id" value="{{ $vehicleMoment->id }}">
                                <input type="hidden" name="booking_id" value="{{ $vehicleMoment->booking_id ?? '' }}">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attendance Date -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="attendance_date">Attendance Date *</label>
                                <input type="date" class="form-control @error('attendance_date') is-invalid @enderror" 
                                       name="attendance_date" value="{{ old('attendance_date', $today) }}" required>
                                @error('attendance_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Driver Information & Allowance -->
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="mb-3 text-primary">
                                <i class="fas fa-user"></i> Driver Information
                            </h5>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Driver Name</label>
                                <input type="text" class="form-control" 
                                       value="{{ $vehicleMoment->driver->user->name ?? 'N/A' }}" readonly>
                                <input type="hidden" name="driver_id" value="{{ $crewId }}">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="driver_allowance">Allowance / Bhatta Amount (NPR) *</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">रू</span>
                                    </div>
                                    <input type="number" step="0.01" class="form-control @error('driver_allowance') is-invalid @enderror" 
                                           name="driver_allowance" value="{{ old('driver_allowance', $existingDriverAttendance->allowances ?? 0) }}" 
                                           placeholder="Enter driver allowance amount">
                                </div>
                                @error('driver_allowance')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hidden driver salary fields if needed -->
                    <input type="hidden" name="driver_salary_amount" value="{{ $request->driver_salary_amount ?? 0 }}">
                    <input type="hidden" name="driver_bonus" value="{{ $request->driver_bonus ?? 0 }}">
                    <input type="hidden" name="driver_deduction" value="{{ $request->driver_deduction ?? 0 }}">
                    
                    <hr>
                    
                    <!-- Helper Information & Allowance -->
                    @if($helperId)
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="mb-3 text-success">
                                <i class="fas fa-user-friends"></i> Helper Information
                            </h5>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Helper Name</label>
                                <input type="text" class="form-control" 
                                       value="{{ $vehicleMoment->helper->user->name ?? 'N/A' }}" readonly>
                                <input type="hidden" name="helper_id" value="{{ $helperId }}">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="helper_allowance">Allowance / Bhatta Amount (NPR)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">रू</span>
                                    </div>
                                    <input type="number" step="0.01" class="form-control @error('helper_allowance') is-invalid @enderror" 
                                           name="helper_allowance" value="{{ old('helper_allowance', $existingHelperAttendance->allowances ?? 0) }}" 
                                           placeholder="Enter helper allowance amount (optional)">
                                </div>
                                @error('helper_allowance')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hidden helper salary fields if needed -->
                    <input type="hidden" name="helper_salary_amount" value="{{ $request->helper_salary_amount ?? 0 }}">
                    <input type="hidden" name="helper_bonus" value="{{ $request->helper_bonus ?? 0 }}">
                    <input type="hidden" name="helper_deduction" value="{{ $request->helper_deduction ?? 0 }}">
                    
                    <hr>
                    @endif
                    
                    <!-- Remarks -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="remarks">Remarks (Optional)</label>
                                <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                          name="remarks" rows="3" placeholder="Any additional notes...">{{ old('remarks') }}</textarea>
                                @error('remarks')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Alert for existing records -->
                    @if($existingDriverAttendance || $existingHelperAttendance)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Note:</strong> 
                            @if($existingDriverAttendance)
                                - Driver already has an attendance record on {{ $today }}. This will update the existing record.<br>
                            @endif
                            @if($existingHelperAttendance)
                                - Helper already has an attendance record on {{ $today }}. This will update the existing record.
                            @endif
                        </div>
                    @endif
                    
                    <!-- Status Info -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Status</label>
                                <div class="form-control" style="background-color: #e9ecef;">
                                    <i class="fas fa-check-circle text-success"></i> Present (Auto-set for all crew members)
                                </div>
                                <small class="text-muted">Status will be automatically set to "Present" for both driver and helper</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Allowances
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
        var driverAllowance = $('input[name="driver_allowance"]').val();
        var helperAllowance = $('input[name="helper_allowance"]').val();
        
        // Check if at least one allowance is entered
        if (!driverAllowance && !helperAllowance) {
            alert('Please enter at least one allowance amount (Driver or Helper)');
            e.preventDefault();
            return false;
        }
        
        // Confirmation message
        var message = 'Add allowance';
        if (driverAllowance) message += '\n- Driver: NPR ' + driverAllowance;
        if (helperAllowance) message += '\n- Helper: NPR ' + helperAllowance;
        message += '\n\nProceed?';
        
        return confirm(message);
    });
});
</script>
@endpush