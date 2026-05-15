@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1>Basic Setup Details</h1>

        <a href="{{ route('admin.basic_tables.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<div class="row">

    <!-- Company Info -->
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-building"></i> Company Information
                </h3>
            </div>

            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th>Company Name:</th>
                        <td>{{ $item->company_name ?? 'N/A' }}</td>
                    </tr>

                    <tr>
                        <th>Footer Text:</th>
                        <td>{{ $item->footer_text ?? 'N/A' }}</td>
                    </tr>

                    <tr>
                        <th>Created At:</th>
                        <td>{{ $item->created_at ?? 'N/A' }}</td>
                    </tr>

                    <tr>
                        <th>Updated At:</th>
                        <td>{{ $item->updated_at ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Logo Preview -->
    <div class="col-md-6">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-image"></i> Logo Preview
                </h3>
            </div>

            <div class="card-body text-center">

                @if($item->logo)
                    @php
                        $ext = pathinfo($item->logo, PATHINFO_EXTENSION);
                    @endphp

                    @if(in_array(strtolower($ext), ['jpg','jpeg','png','webp']))
                        <img src="{{ asset($item->logo) }}"
                             alt="Logo"
                             class="img-fluid img-thumbnail"
                             style="max-height:200px;">
                    @elseif(strtolower($ext) === 'pdf')
                        <a href="{{ asset($item->logo) }}" target="_blank"
                           class="btn btn-outline-danger">
                            <i class="fa fa-file-pdf"></i> View Logo PDF
                        </a>
                    @else
                        <a href="{{ asset($item->logo) }}" target="_blank"
                           class="btn btn-outline-primary">
                            View File
                        </a>
                    @endif

                @else
                    <p class="text-muted">No Logo Uploaded</p>
                @endif

            </div>
        </div>
    </div>

    <!-- Content Sections -->
<!-- About Us -->
<div class="col-md-6">
    <div class="card card-success card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle"></i> About Us
            </h3>
        </div>

        <div class="card-body">
            @if(!empty($item->about_us))
                {!! $item->about_us !!}
            @else
                <p class="text-muted">N/A</p>
            @endif
        </div>
    </div>
</div>

<!-- Contact Us -->
{{-- <div class="col-md-6">
    <div class="card card-warning card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-phone"></i> Contact Us
            </h3>
        </div>

        <div class="card-body">
            @if(!empty($item->contact_us))
                {!! $item->contact_us !!}
            @else
                <p class="text-muted">N/A</p>
            @endif
        </div>
    </div>
</div> --}}


@php
    $privacyPreview = Str::limit(strip_tags($item->privacy_policy), 180);
    $termsPreview = Str::limit(strip_tags($item->terms_and_conditions), 180);
@endphp

<div class="col-md-6">
    <div class="card card-info card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-shield"></i> Privacy Policy
            </h3>
        </div>

        <div class="card-body">
            <p class="text-muted">
                {{ $privacyPreview ?? 'N/A' }}
            </p>

            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#privacyModal">
                View Full
            </button>
        </div>
    </div>
</div>

<div class="col-md-6">
    <div class="card card-danger card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-contract"></i> Terms & Conditions
            </h3>
        </div>

        <div class="card-body">
            <p class="text-muted">
                {{ $termsPreview ?? 'N/A' }}
            </p>

            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#termsModal">
                View Full
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="privacyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Privacy Policy</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                {!! $item->privacy_policy !!}
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Terms & Conditions</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                {!! $item->terms_and_conditions !!}
            </div>

        </div>
    </div>
</div>
<!-- Action Buttons -->
<div class="row mt-3">
    <div class="col-12 text-right">

        <a href="{{ route('admin.basic_tables.edit', $item->id) }}"
           class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit
        </a>

        <form action="{{ route('admin.basic_tables.destroy', $item->id) }}"
              method="POST"
              style="display:inline-block;"
              onsubmit="return confirm('Are you sure you want to delete this data?');">
            @csrf
            @method('DELETE')

            <button type="submit" class="btn btn-danger">
                <i class="fa fa-trash"></i> Delete
            </button>
        </form>

    </div>
</div>

</div>
</section>

@endsection