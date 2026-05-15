@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($contact) ? 'Edit Contact Us' : 'Add Contact Us' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<form action="{{ isset($contact) ? route('admin.contact-us.update',$contact->id) : route('admin.contact-us.store') }}"
      method="POST">
@csrf
@if(isset($contact)) @method('PUT') @endif

<div class="card-body">
@include('layouts.admin_theme.alert')

<div class="row">

<div class="col-md-6">
<div class="form-group">
<label>Full Name *</label>
<input type="text" name="full_name" class="form-control"
value="{{ old('full_name',$contact->full_name ?? '') }}" required>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Email *</label>
<input type="email" name="email" class="form-control"
value="{{ old('email',$contact->email ?? '') }}" required>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Mobile Number *</label>
<input type="text" name="mobile_number" class="form-control"
value="{{ old('mobile_number',$contact->mobile_number ?? '') }}" required>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>WhatsApp Number</label>
<input type="text" name="whatsapp_number" class="form-control"
value="{{ old('whatsapp_number',$contact->whatsapp_number ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Facebook URL</label>
<input type="url" name="facebook_url" class="form-control"
value="{{ old('facebook_url',$contact->facebook_url ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Instagram URL</label>
<input type="url" name="instagram_url" class="form-control"
value="{{ old('instagram_url',$contact->instagram_url ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Tiktok URL</label>
<input type="url" name="tiktok_url" class="form-control"
value="{{ old('tiktok_url',$contact->tiktok_url ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>LinkedIn URL</label>
<input type="url" name="linkedin_url" class="form-control"
value="{{ old('linkedin_url',$contact->linkedin_url ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Twitter URL</label>
<input type="url" name="twitter_url" class="form-control"
value="{{ old('twitter_url',$contact->twitter_url ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>YouTube URL</label>
<input type="url" name="youtube_url" class="form-control"
value="{{ old('youtube_url',$contact->youtube_url ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Subject</label>
<input type="text" name="subject" class="form-control"
value="{{ old('subject',$contact->subject ?? '') }}">
</div>
</div>

<div class="col-md-12">
<div class="form-group">
<label>Message</label>
<textarea name="message" rows="4" class="form-control">{{ old('message',$contact->message ?? '') }}</textarea>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Status</label>
<select name="status" class="form-control">
    <option value="active"
        {{ old('status',$contact->status ?? '') == 'active' ? 'selected' : '' }}>
        Active
    </option>

    <option value="inactive"
        {{ old('status',$contact->status ?? '') == 'inactive' ? 'selected' : '' }}>
        Inactive
    </option>
</select>
</div>
</div>

</div>
</div>

<div class="card-footer text-right">
<button type="submit" class="btn btn-primary">
{{ isset($contact) ? 'Update Contact' : 'Add Contact' }}
</button>
</div>

</form>
</div>
</div>
</section>
@endsection