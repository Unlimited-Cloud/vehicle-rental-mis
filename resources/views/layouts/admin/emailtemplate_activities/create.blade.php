@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1 class="mb-3">
            Create Email Template Activity
        </h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<form action="{{ route('admin.emailtemplate_activities.store') }}"
      method="POST">

@csrf
@include('layouts.admin_theme.alert')

<div class="card card-primary card-outline mb-4">
    <div class="card-header bg-primary">
        <h3 class="card-title text-white">
            <i class="fas fa-envelope"></i> Activity Information
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Activity For *</label>
                    <input type="text" name="activity_for" class="form-control"
                           value="{{ old('activity_for') }}" required>
                </div>
            </div>

            {{-- <div class="col-md-6">
                <div class="form-group">
                    <label>Partner UUID</label>
                    <select name="partner_Uuid" class="form-control">
                        <option value="">Select Partner</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->Uuid }}" {{ old('partner_Uuid') == $partner->Uuid ? 'selected' : '' }}>
                                {{ $partner->name ?? $partner->Uuid }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div> --}}

            <div class="col-md-12">
                <div class="form-group">
                    <label>Activity *</label>
                    <textarea name="activity" class="form-control" rows="3" required>{{ old('activity') }}</textarea>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>Email Triggered</label>
                    <select name="email_triggered" class="form-control">
                        <option value="1" {{ old('email_triggered') == 1 ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('email_triggered') == 0 ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>SMS Triggered</label>
                    <select name="sms_triggered" class="form-control">
                        <option value="1" {{ old('sms_triggered') == 1 ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('sms_triggered') == 0 ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>Notification Triggered</label>
                    <select name="notification_triggered" class="form-control">
                        <option value="1" {{ old('notification_triggered') == 1 ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('notification_triggered') == 0 ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-footer text-right">
        <a href="{{ route('admin.emailtemplate_activities.index') }}" class="btn btn-secondary">
            Back
        </a>

        <button type="submit" class="btn btn-primary">
            Create Activity
        </button>
    </div>
</div>

</form>

</div>
</section>

@endsection