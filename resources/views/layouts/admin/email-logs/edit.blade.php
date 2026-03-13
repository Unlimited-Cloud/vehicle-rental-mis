@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1 class="mb-3">
            Edit Email Log
        </h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<form action="{{ route('admin.email-logs.update', $emailLog->id) }}"
      method="POST">

@csrf
@method('PUT')
@include('layouts.admin_theme.alert')

<div class="card card-primary card-outline mb-4">
    <div class="card-header bg-primary">
        <h3 class="card-title text-white">
            <i class="fas fa-envelope"></i> Email Log Information
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Email Template</label>
                    <select name="emailtemplate_id" class="form-control">
                        <option value="">Select Template</option>
                        @foreach($emailTemplates as $template)
                            <option value="{{ $template->id }}" {{ old('emailtemplate_id', $emailLog->emailtemplate_id) == $template->id ? 'selected' : '' }}>
                                {{ $template->title }} (ID: {{ $template->id }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" class="form-control" required>
                        <option value="pending" {{ old('status', $emailLog->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="sent" {{ old('status', $emailLog->status) == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="failed" {{ old('status', $emailLog->status) == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Email From *</label>
                    <input type="email" name="email_from" class="form-control"
                           value="{{ old('email_from', $emailLog->email_from) }}" required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Email To *</label>
                    <input type="email" name="email_to" class="form-control"
                           value="{{ old('email_to', $emailLog->email_to) }}" required>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label>Email Subject *</label>
                    <input type="text" name="email_subject" class="form-control"
                           value="{{ old('email_subject', $emailLog->email_subject) }}" required>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label>Email CC</label>
                    <input type="text" name="email_cc" class="form-control"
                           value="{{ old('email_cc', $emailLog->email_cc) }}" placeholder="cc@example.com, another@example.com">
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label>Email Body *</label>
                    <textarea name="email_body" class="form-control" rows="5" required>{{ old('email_body', $emailLog->email_body) }}</textarea>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label>Failure Reason</label>
                    <textarea name="failure_reason" class="form-control" rows="2">{{ old('failure_reason', $emailLog->failure_reason) }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-footer text-right">
        <a href="{{ route('admin.email-logs.index') }}" class="btn btn-secondary">
            Back
        </a>

        <button type="submit" class="btn btn-primary">
            Update Email Log
        </button>
    </div>
</div>

</form>

</div>
</section>

@endsection