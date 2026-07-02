@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($attendance) ? 'Edit' : 'Mark' }} Attendance</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <form action="{{ isset($attendance) ? route('admin.attendance.update', $attendance->id) : route('admin.attendance.store') }}" method="POST">
                    @csrf
                    @if(isset($attendance))
                        @method('PUT')
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Crew Member *</label>
                                <select name="crew_id" class="form-control @error('crew_id') is-invalid @enderror" required>
                                    <option value="">Select Crew Member</option>
                                    @foreach($crews as $crew)
                                        <option value="{{ $crew->id }}" {{ (old('crew_id', $attendance->crew_id ?? $selectedCrewId ?? '') == $crew->id) ? 'selected' : '' }}>
                                            {{ $crew->user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('crew_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date *</label>
                                <input type="date" name="attendance_date" class="form-control @error('attendance_date') is-invalid @enderror" 
                                       value="{{ old('attendance_date', $attendance->attendance_date ?? $selectedDate ?? '') }}" required>
                                @error('attendance_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status *</label>
                                <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                                    <option value="present" {{ (old('status', $attendance->status ?? '') == 'present') ? 'selected' : '' }}>Present</option>
                                    <option value="absent" {{ (old('status', $attendance->status ?? '') == 'absent') ? 'selected' : '' }}>Absent</option>
                                    <option value="half_day" {{ (old('status', $attendance->status ?? '') == 'half_day') ? 'selected' : '' }}>Half Day</option>
                                    <option value="holiday" {{ (old('status', $attendance->status ?? '') == 'holiday') ? 'selected' : '' }}>Holiday</option>
                                    <option value="leave" {{ (old('status', $attendance->status ?? '') == 'leave') ? 'selected' : '' }}>Leave</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Allowances Amount (Rs.)</label>
                                <input type="number" step="0.01" name="allowances" class="form-control @error('allowances') is-invalid @enderror" 
                                       value="{{ old('allowances', $attendance->allowances ?? 0) }}">
                                @error('allowances')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Bonus (Rs.)</label>
                                <input type="number" step="0.01" name="bonus" class="form-control @error('bonus') is-invalid @enderror" 
                                       value="{{ old('bonus', $attendance->bonus ?? 0) }}">
                                @error('bonus')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Deduction (Rs.)</label>
                                <input type="number" step="0.01" name="deduction" class="form-control @error('deduction') is-invalid @enderror" 
                                       value="{{ old('deduction', $attendance->deduction ?? 0) }}">
                                @error('deduction')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div> --}}

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Related Booking (Optional)</label>
                                <select name="booking_id" class="form-control">
                                    <option value="">Select Booking</option>
                                    @if(isset($bookings) && count($bookings) > 0)
                                        @foreach($bookings as $booking)
                                            <option value="{{ $booking->id }}" {{ (old('booking_id', $attendance->booking_id ?? '') == $booking->id) ? 'selected' : '' }}>
                                                #{{ $booking->id }} - {{ $booking->vehicle->vehicle_name ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vehicle Moment (Optional)</label>
                                <select name="vehicle_moment_id" class="form-control">
                                    <option value="">Select Vehicle Moment</option>
                                    @if(isset($vehicleMoments) && count($vehicleMoments) > 0)
                                        @foreach($vehicleMoments as $moment)
                                            <option value="{{ $moment->id }}" {{ (old('vehicle_moment_id', $attendance->vehicle_moment_id ?? '') == $moment->id) ? 'selected' : '' }}>
                                                #{{ $moment->id }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror">{{ old('remarks', $attendance->remarks ?? '') }}</textarea>
                        @error('remarks')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> {{ isset($attendance) ? 'Update' : 'Save' }} Attendance
                        </button>
                        <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary">
                            <i class="fa fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection