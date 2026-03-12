@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Email Templates</h1>
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
                            <a href="{{ route('admin.email-templates.create') }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-plus"></i> Add New Email Template
                            </a>
                        </div>

                        <table id="dataTable" class="table table-bordered table-striped show-search-bar">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>Title</th>
                                    <th>Activity</th>
                                    <th>Template For</th>
                                    <th>Email Subject</th>
                                    <th>Delay</th>
                                    <th>Email CC</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tablebody">
                                @foreach($emailTemplates as $template)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $template->title }}</td>
                                    <td>{{ $template->activity }}</td>
                                    <td>{{ $template->template_for }}</td>
                                    <td>{{ Str::limit($template->email_subject, 30) }}</td>
                                    <td>
                                        @if($template->delay_days)
                                            {{ $template->delay_days }} days
                                        @elseif($template->delay_hour)
                                            {{ $template->delay_hour }} hours
                                        @elseif($template->delay_min)
                                            {{ $template->delay_min }} minutes
                                        @else
                                            None
                                        @endif
                                    </td>
                                    <td>{{ $template->email_cc ?? 'N/A' }}</td>
                                    <td>
                                        @if($template->email_template_triggered)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.email-templates.edit', $template->id) }}"
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <a href="{{ route('admin.email-templates.show', $template->id) }}"
                                           class="btn btn-success btn-sm" title="View Template Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <form action="{{ route('admin.email-templates.destroy', $template->id) }}"
                                            method="POST"
                                            style="display:inline-block;"
                                            onsubmit="return confirm('Are you sure you want to delete this template?');">
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