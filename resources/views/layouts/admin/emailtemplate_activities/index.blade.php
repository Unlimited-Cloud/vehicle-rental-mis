@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Email Template Activities</h1>
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
                            <a href="{{ route('admin.emailtemplate_activities.create') }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-plus"></i> Add New Activity
                            </a>
                        </div>

                        <table id="dataTable" class="table table-bordered table-striped show-search-bar">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>Activity For</th>
                                    <th>Partner UUID</th>
                                    <th>Activity</th>
                                    <th>Email Triggered</th>
                                    <th>SMS Triggered</th>
                                    <th>Notification Triggered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tablebody">
                                @foreach($activities as $activity)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $activity->activity_for }}</td>
                                    <td>{{ $activity->partner_Uuid ?? 'N/A' }}</td>
                                    <td>{{ $activity->activity }}</td>
                                    <td>
                                        @if($activity->email_triggered)
                                            <span class="badge badge-success">Yes</span>
                                        @else
                                            <span class="badge badge-danger">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($activity->sms_triggered)
                                            <span class="badge badge-success">Yes</span>
                                        @else
                                            <span class="badge badge-danger">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($activity->notification_triggered)
                                            <span class="badge badge-success">Yes</span>
                                        @else
                                            <span class="badge badge-danger">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.emailtemplate_activities.edit', $activity->id) }}"
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <a href="{{ route('admin.emailtemplate_activities.show', $activity->id) }}"
                                           class="btn btn-success btn-sm" title="View Activity Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <form action="{{ route('admin.emailtemplate_activities.destroy', $activity->id) }}"
                                            method="POST"
                                            style="display:inline-block;"
                                            onsubmit="return confirm('Are you sure you want to delete this activity?');">
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