@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1 class="mb-3">
            Edit Email Template
        </h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<form action="{{ route('admin.email-templates.update', $emailTemplate->id) }}"
      method="POST">

@csrf
@method('PUT')
@include('layouts.admin_theme.alert')

<!-- ================= BASIC INFORMATION ================= -->
<div class="card card-primary card-outline mb-4">
    <div class="card-header bg-primary">
        <h3 class="card-title text-white">
            <i class="fas fa-envelope"></i> Basic Template Information
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" class="form-control"
                           value="{{ old('title', $emailTemplate->title) }}" required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Activity *</label>
                    <input type="text" name="activity" class="form-control"
                           value="{{ old('activity', $emailTemplate->activity) }}" required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Template For *</label>
                    <input type="text" name="template_for" class="form-control"
                           value="{{ old('template_for', $emailTemplate->template_for) }}" required>
                </div>
            </div>

            {{-- <div class="col-md-6">
                <div class="form-group">
                    <label>Partner UUID</label>
                    <select name="partner_Uuid" class="form-control">
                        <option value="">Select Partner</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->Uuid }}" {{ old('partner_Uuid', $emailTemplate->partner_Uuid) == $partner->Uuid ? 'selected' : '' }}>
                                {{ $partner->name ?? $partner->Uuid }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div> --}}

            <div class="col-md-6">
                <div class="form-group">
                    <label>Activity UUID</label>
                    <select name="activity_UUID" class="form-control">
                        <option value="">Select Activity</option>
                        @foreach($activities as $activity)
                            <option value="{{ $activity->Uuid }}" {{ old('activity_UUID', $emailTemplate->activity_UUID) == $activity->Uuid ? 'selected' : '' }}>
                                {{ $activity->activity }} ({{ $activity->activity_for }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Email CC</label>
                    <input type="text" name="email_cc" class="form-control"
                           value="{{ old('email_cc', $emailTemplate->email_cc) }}" placeholder="cc@example.com, another@example.com">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= DELAY SETTINGS ================= -->
<div class="card card-info card-outline mb-4">
    <div class="card-header bg-info">
        <h3 class="card-title text-white">
            <i class="fas fa-clock"></i> Delay Settings
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Delay (Minutes)</label>
                    <input type="number" name="delay_min" class="form-control"
                           value="{{ old('delay_min', $emailTemplate->delay_min) }}">
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>Delay (Hours)</label>
                    <input type="number" name="delay_hour" class="form-control"
                           value="{{ old('delay_hour', $emailTemplate->delay_hour) }}">
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>Delay (Days)</label>
                    <input type="number" name="delay_days" class="form-control"
                           value="{{ old('delay_days', $emailTemplate->delay_days) }}">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= EMAIL CONTENT ================= -->
<div class="card card-success card-outline mb-4">
    <div class="card-header bg-success">
        <h3 class="card-title text-white">
            <i class="fas fa-envelope-open-text"></i> Email Content
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Email Subject *</label>
                    <input type="text" name="email_subject" class="form-control"
                           value="{{ old('email_subject', $emailTemplate->email_subject) }}" required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Success Email Content</label>
                    <textarea name="success_email_content" class="form-control" rows="5">{{ old('success_email_content', $emailTemplate->success_email_content) }}</textarea>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Error Email Content</label>
                    <textarea name="error_email_content" class="form-control" rows="5">{{ old('error_email_content', $emailTemplate->error_email_content) }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= SMS CONTENT ================= -->
<div class="card card-warning card-outline mb-4">
    <div class="card-header bg-warning">
        <h3 class="card-title text-white">
            <i class="fas fa-sms"></i> SMS Content
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Success SMS Content</label>
                    <textarea name="success_sms_content" class="form-control" rows="3">{{ old('success_sms_content', $emailTemplate->success_sms_content) }}</textarea>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Error SMS Content</label>
                    <textarea name="error_sms_content" class="form-control" rows="3">{{ old('error_sms_content', $emailTemplate->error_sms_content) }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= NOTIFICATION CONTENT ================= -->
<div class="card card-danger card-outline mb-4">
    <div class="card-header bg-danger">
        <h3 class="card-title text-white">
            <i class="fas fa-bell"></i> Notification Content
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Success Customer Notification</label>
                    <textarea name="success_customer_notification_content" class="form-control" rows="3">{{ old('success_customer_notification_content', $emailTemplate->success_customer_notification_content) }}</textarea>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Success Admin Notification</label>
                    <textarea name="success_admin_notification_content" class="form-control" rows="3">{{ old('success_admin_notification_content', $emailTemplate->success_admin_notification_content) }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= TRIGGER SETTINGS ================= -->
<div class="card card-secondary card-outline mb-4">
    <div class="card-header bg-secondary">
        <h3 class="card-title text-white">
            <i class="fas fa-toggle-on"></i> Trigger Settings
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Email Template Triggered</label>
                    <select name="email_template_triggered" class="form-control">
                        <option value="1" {{ old('email_template_triggered', $emailTemplate->email_template_triggered) == 1 ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('email_template_triggered', $emailTemplate->email_template_triggered) == 0 ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>SMS Template Triggered</label>
                    <select name="sms_template_triggered" class="form-control">
                        <option value="1" {{ old('sms_template_triggered', $emailTemplate->sms_template_triggered) == 1 ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('sms_template_triggered', $emailTemplate->sms_template_triggered) == 0 ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>Notification Template Triggered</label>
                    <select name="notification_template_triggered" class="form-control">
                        <option value="1" {{ old('notification_template_triggered', $emailTemplate->notification_template_triggered) == 1 ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('notification_template_triggered', $emailTemplate->notification_template_triggered) == 0 ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= SUBMIT ================= -->
<div class="card">
    <div class="card-footer text-right">
        <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary">
            Back
        </a>

        <button type="submit" class="btn btn-primary">
            Update Email Template
        </button>
    </div>
</div>

</form>

</div>
</section>

@endsection