@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1 class="mb-3">
            Email Log Details
        </h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<div class="card card-primary card-outline">
    <div class="card-header bg-primary">
        <h3 class="card-title text-white">
            <i class="fas fa-envelope"></i> Email Log Information
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 200px;">ID</th>
                        <td>{{ $emailLog->id }}</td>
                    </tr>
                    <tr>
                        <th>Email Template ID</th>
                        <td>{{ $emailLog->emailtemplate_id ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Email From</th>
                        <td>{{ $emailLog->email_from }}</td>
                    </tr>
                    <tr>
                        <th>Email To</th>
                        <td>{{ $emailLog->email_to }}</td>
                    </tr>
                    <tr>
                        <th>Email Subject</th>
                        <td>{{ $emailLog->email_subject }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 200px;">Email CC</th>
                        <td>{{ $emailLog->email_cc ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($emailLog->status == 'sent')
                                <span class="badge badge-success">Sent</span>
                            @elseif($emailLog->status == 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($emailLog->status == 'failed')
                                <span class="badge badge-danger">Failed</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ $emailLog->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At</th>
                        <td>{{ $emailLog->updated_at ? $emailLog->updated_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <h5 class="bg-info p-2 text-white">Email Body</h5>
                <div class="card">
                    <div class="card-body">
                        {{ $emailLog->email_body }}
                    </div>
                </div>
            </div>
        </div>

        @if($emailLog->failure_reason)
        <div class="row mt-4">
            <div class="col-md-12">
                <h5 class="bg-danger p-2 text-white">Failure Reason</h5>
                <div class="card">
                    <div class="card-body">
                        {{ $emailLog->failure_reason }}
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@if($emailLog->emailTemplate)
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
                <td>{{ $emailLog->emailTemplate->title }}</td>
            </tr>
            <tr>
                <th>Template UUID</th>
                <td>{{ $emailLog->emailTemplate->template_UUID }}</td>
            </tr>
            <tr>
                <th>Email Subject</th>
                <td>{{ $emailLog->emailTemplate->email_subject }}</td>
            </tr>
        </table>
    </div>
</div>
@endif

<div class="card mt-4">
    <div class="card-footer text-right">
        <a href="{{ route('admin.email-logs.index') }}" class="btn btn-secondary">
            Back to List
        </a>
        <a href="{{ route('admin.email-logs.edit', $emailLog->id) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit
        </a>
    </div>
</div>

</div>
</section>

@endsection