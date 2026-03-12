@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1 class="mb-3">
            Activity Details
        </h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<div class="card card-primary card-outline">
    <div class="card-header bg-primary">
        <h3 class="card-title text-white">
            <i class="fas fa-envelope"></i> Activity Information
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 200px;">ID</th>
                        <td>{{ $activity->id }}</td>
                    </tr>
                    <tr>
                        <th>UUID</th>
                        <td>{{ $activity->Uuid }}</td>
                    </tr>
                    <tr>
                        <th>Activity For</th>
                        <td>{{ $activity->activity_for }}</td>
                    </tr>
                    <tr>
                        <th>Partner UUID</th>
                        <td>{{ $activity->partner_Uuid ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Activity</th>
                        <td>{{ $activity->activity }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 200px;">Email Triggered</th>
                        <td>
                            @if($activity->email_triggered)
                                <span class="badge badge-success">Yes</span>
                            @else
                                <span class="badge badge-danger">No</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>SMS Triggered</th>
                        <td>
                            @if($activity->sms_triggered)
                                <span class="badge badge-success">Yes</span>
                            @else
                                <span class="badge badge-danger">No</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Notification Triggered</th>
                        <td>
                            @if($activity->notification_triggered)
                                <span class="badge badge-success">Yes</span>
                            @else
                                <span class="badge badge-danger">No</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Added By</th>
                        <td>{{ $activity->added_by }}</td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At</th>
                        <td>{{ $activity->updated_at ? $activity->updated_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

@if($activity->emailTemplate)
<div class="card card-info card-outline mt-4">
    <div class="card-header bg-info">
        <h3 class="card-title text-white">
            <i class="fas fa-envelope-open-text"></i> Associated Email Template
        </h3>
    </div>

    <div class="card-body">
        <table class="table table-bordered">
            <tr>
                <th style="width: 200px;">Template Title</th>
                <td>{{ $activity->emailTemplate->title }}</td>
            </tr>
            <tr>
                <th>Email Subject</th>
                <td>{{ $activity->emailTemplate->email_subject }}</td>
            </tr>
            <tr>
                <th>Delay (Days/Hours/Min)</th>
                <td>
                    @if($activity->emailTemplate->delay_days)
                        {{ $activity->emailTemplate->delay_days }} days
                    @elseif($activity->emailTemplate->delay_hour)
                        {{ $activity->emailTemplate->delay_hour }} hours
                    @elseif($activity->emailTemplate->delay_min)
                        {{ $activity->emailTemplate->delay_min }} minutes
                    @else
                        No delay
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>
@endif

<div class="card mt-4">
    <div class="card-footer text-right">
        <a href="{{ route('admin.emailtemplate_activities.index') }}" class="btn btn-secondary">
            Back to List
        </a>
        <a href="{{ route('admin.emailtemplate_activities.edit', $activity->id) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit
        </a>
    </div>
</div>

</div>
</section>

@endsection