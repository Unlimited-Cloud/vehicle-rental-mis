@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Email Logs</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline card-tabs">
                    <div class="card-body">

                        @include('layouts.admin_theme.alert')
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <a href="{{ route('admin.email-logs.create') }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-plus"></i> Add New Email Log
                            </a>
                        </div>

                        <table id="dataTable" class="table table-bordered table-striped show-search-bar">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>Template ID</th>
                                    <th>Email From</th>
                                    <th>Email To</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Failure Reason</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tablebody">
                                @foreach($emailLogs as $log)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $log->emailtemplate_id ?? 'N/A' }}</td>
                                    <td>{{ $log->email_from }}</td>
                                    <td>{{ $log->email_to }}</td>
                                    <td>{{ Str::limit($log->email_subject, 30) }}</td>
                                    <td>
                                        @if($log->status == 'sent')
                                            <span class="badge badge-success">Sent</span>
                                        @elseif($log->status == 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @elseif($log->status == 'failed')
                                            <span class="badge badge-danger">Failed</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($log->failure_reason, 30) ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('admin.email-logs.edit', $log->id) }}"
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <a href="{{ route('admin.email-logs.show', $log->id) }}"
                                           class="btn btn-success btn-sm" title="View Log Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <form action="{{ route('admin.email-logs.destroy', $log->id) }}"
                                            method="POST"
                                            style="display:inline-block;"
                                            onsubmit="return confirm('Are you sure you want to delete this log?');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm bg-red">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection