@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>SMS Logs</h1>
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

                        <table id="dataTable" class="table table-bordered table-striped show-search-bar">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    {{-- <th>Template ID</th> --}}
                                    <th>Date</th>
                                    <th>Sent To</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody id="tablebody">
                                @foreach($smsLogs as $log)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $log->created_at }}</td>
                                    <td>{{ $log->mobile_number }}</td>
                                    <td>{!! $log->message !!}</td>
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