@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1 class="mb-3">
            Email Template Details
        </h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<div class="card card-primary card-outline">
    <div class="card-header bg-primary">
        <h3 class="card-title text-white">
            <i class="fas fa-envelope"></i> Template Information
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 200px;">ID</th>
                        <td>{{ $emailTemplate->id }}</td>
                    </tr>
                    <tr>
                        <th>Template UUID</th>
                        <td>{{ $emailTemplate->template_UUID }}</td>
                    </tr>
                    <tr>
                        <th>Title</th>
                        <td>{{ $emailTemplate->title }}</td>
                    </tr>
                    <tr>
                        <th>Activity</th>
                        <td>{{ $emailTemplate->activity }}</td>
                    </tr>
                    <tr>
                        <th>Template For</th>
                        <td>{{ $emailTemplate->template_for }}</td>
                    </tr>
                    <tr>
                        <th>Partner UUID</th>
                        <td>{{ $emailTemplate->partner_Uuid ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Activity UUID</th>
                        <td>{{ $emailTemplate->activity_UUID ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Email CC</th>
                        <td>{{ $emailTemplate->email_cc ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 200px;">Delay (Minutes)</th>
                        <td>{{ $emailTemplate->delay_min ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Delay (Hours)</th>
                        <td>{{ $emailTemplate->delay_hour ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Delay (Days)</th>
                        <td>{{ $emailTemplate->delay_days ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Email Subject</th>
                        <td>{{ $emailTemplate->email_subject }}</td>
                    </tr>
                    <tr>
                        <th>Email Template Triggered</th>
                        <td>
                            @if($emailTemplate->email_template_triggered)
                                <span class="badge badge-success">Yes</span>
                            @else
                                <span class="badge badge-danger">No</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>SMS Template Triggered</th>
                        <td>
                            @if($emailTemplate->sms_template_triggered)
                                <span class="badge badge-success">Yes</span>
                            @else
                                <span class="badge badge-danger">No</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Notification Template Triggered</th>
                        <td>
                            @if($emailTemplate->notification_template_triggered)
                                <span class="badge badge-success">Yes</span>
                            @else
                                <span class="badge badge-danger">No</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ $emailTemplate->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <h5 class="bg-info p-2 text-white">Email Content</h5>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        Success Email Content
                    </div>
                    <div class="card-body">
                        {{ $emailTemplate->success_email_content ?? 'N/A' }}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        Error Email Content
                    </div>
                    <div class="card-body">
                        {{ $emailTemplate->error_email_content ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <h5 class="bg-warning p-2 text-white">SMS Content</h5>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        Success SMS Content
                    </div>
                    <div class="card-body">
                        {{ $emailTemplate->success_sms_content ?? 'N/A' }}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        Error SMS Content
                    </div>
                    <div class="card-body">
                        {{ $emailTemplate->error_sms_content ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <h5 class="bg-danger p-2 text-white">Notification Content</h5>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        Success Customer Notification
                    </div>
                    <div class="card-body">
                        {{ $emailTemplate->success_customer_notification_content ?? 'N/A' }}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        Success Admin Notification
                    </div>
                    <div class="card-body">
                        {{ $emailTemplate->success_admin_notification_content ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($emailTemplate->emailActivities)
<div class="card card-info card-outline mt-4">
    <div class="card-header bg-info">
        <h3 class="card-title text-white">
            <i class="fas fa-tasks"></i> Associated Activity
        </h3>
    </div>

    <div class="card-body">
        <table class="table table-bordered">
            <tr>
                <th style="width: 200px;">Activity For</th>
                <td>{{ $emailTemplate->emailActivities->activity_for ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Activity</th>
                <td>{{ $emailTemplate->emailActivities->activity ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Email Triggered</th>
                <td>
                    @if($emailTemplate->emailActivities->email_triggered ?? false)
                        <span class="badge badge-success">Yes</span>
                    @else
                        <span class="badge badge-danger">No</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>
@endif

<div class="card mt-4">
    <div class="card-footer text-right">
        <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary">
            Back to List
        </a>
        <a href="{{ route('admin.email-templates.edit', $emailTemplate->id) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit
        </a>
    </div>
</div>

</div>
</section>

@endsection