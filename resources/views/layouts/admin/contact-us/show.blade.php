@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1>Contact Us Details</h1>

        <a href="{{ route('admin.contact-us.index') }}"
           class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<div class="row">

    <!-- Basic Information -->
    <div class="col-md-6">
        <div class="card card-primary card-outline">

            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user"></i> Basic Information
                </h3>
            </div>

            <div class="card-body">

                <table class="table table-borderless">

                    <tr>
                        <th>Full Name:</th>
                        <td>{{ $contact->full_name ?? 'N/A' }}</td>
                    </tr>

                    <tr>
                        <th>Email:</th>
                        <td>{{ $contact->email ?? 'N/A' }}</td>
                    </tr>

                    <tr>
                        <th>Mobile Number:</th>
                        <td>{{ $contact->mobile_number ?? 'N/A' }}</td>
                    </tr>

                    <tr>
                        <th>WhatsApp Number:</th>
                        <td>{{ $contact->whatsapp_number ?? 'N/A' }}</td>
                    </tr>

                    <tr>
                        <th>Website URL:</th>
                        <td>
                            @if($contact->website_url)
                                <a href="{{ $contact->website_url }}"
                                   target="_blank">
                                    {{ $contact->website_url }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>Status:</th>
                        <td>
                            @if($contact->status == 'active')
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                    </tr>

                </table>

            </div>
        </div>
    </div>

    <!-- Address & Social Media -->
    <div class="col-md-6">
        <div class="card card-info card-outline">

            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-share-alt"></i> Address & Social Links
                </h3>
            </div>

            <div class="card-body">

                <table class="table table-borderless">

                    <tr>
                        <th>Address:</th>
                        <td>{{ $contact->address ?? 'N/A' }}</td>
                    </tr>

                    <tr>
                        <th>Facebook:</th>
                        <td>
                            @if($contact->facebook_url)
                                <a href="{{ $contact->facebook_url }}"
                                   target="_blank">
                                    {{ $contact->facebook_url }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>Instagram:</th>
                        <td>
                            @if($contact->instagram_url)
                                <a href="{{ $contact->instagram_url }}"
                                   target="_blank">
                                    {{ $contact->instagram_url }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>LinkedIn:</th>
                        <td>
                            @if($contact->linkedin_url)
                                <a href="{{ $contact->linkedin_url }}"
                                   target="_blank">
                                    {{ $contact->linkedin_url }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>Twitter:</th>
                        <td>
                            @if($contact->twitter_url)
                                <a href="{{ $contact->twitter_url }}"
                                   target="_blank">
                                    {{ $contact->twitter_url }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>YouTube:</th>
                        <td>
                            @if($contact->youtube_url)
                                <a href="{{ $contact->youtube_url }}"
                                   target="_blank">
                                    {{ $contact->youtube_url }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>

                </table>

            </div>
        </div>
    </div>

</div>

<!-- Message Section -->
<div class="row">

    <div class="col-md-12">

        <div class="card card-warning card-outline">

            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-envelope"></i> Subject & Message
                </h3>
            </div>

            <div class="card-body">

                <table class="table table-borderless">

                    <tr>
                        <th width="150">Subject:</th>
                        <td>{{ $contact->subject ?? 'N/A' }}</td>
                    </tr>

                    <tr>
                        <th>Message:</th>
                        <td>{{ $contact->message ?? 'N/A' }}</td>
                    </tr>

                </table>

            </div>

        </div>

    </div>

</div>

<!-- Footer Actions -->
<div class="row">
    <div class="col-12 text-right">

        <a href="{{ route('admin.contact-us.edit', $contact->id) }}"
           class="btn btn-primary">

            <i class="fas fa-edit"></i> Edit Contact

        </a>

    </div>
</div>

</div>
</section>

@endsection