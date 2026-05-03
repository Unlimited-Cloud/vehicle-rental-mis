@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($crew_profile) ? 'Edit Crew Profile' : 'Add Crew Profile' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<form action="{{ isset($crew_profile) ? route('admin.crew_profiles.update',$crew_profile->id) : route('admin.crew_profiles.store') }}"
      method="POST" enctype="multipart/form-data">
@csrf
@if(isset($crew_profile)) @method('PUT') @endif

<div class="card-body">
@include('layouts.admin_theme.alert')

<div class="row">
@if(isset($crew_profile))
<input type="hidden" name="user_id" class="form-control"
value="{{ $crew_profile->user_id }}">
@endif
<div class="col-md-6">
<div class="form-group">
<label>Name *</label>
<input type="text" name="crew_member_name" class="form-control"
value="{{ old('crew_member_name',$crew_profile->crew_member_name ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Email *</label>
<input type="text" name="crew_member_email" class="form-control"
value="{{ old('crew_member_email',$crew_profile->crew_member_email ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Role *</label>
<select name="role" class="form-control" required>
    <option value="driver" {{ old('role',$crew_profile->role ?? '')=='driver'?'selected':'' }}>Driver</option>
    <option value="helper" {{ old('role',$crew_profile->role ?? '')=='helper'?'selected':'' }}>Helper</option>
</select>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>License Number</label>
<input type="text" name="license_number" class="form-control"
value="{{ old('license_number',$crew_profile->license_number ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>License Expiry</label>
<input type="date" name="license_expiry" class="form-control"
value="{{ old('license_expiry',$crew_profile->license_expiry ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Citizenship Document</label>
<input type="file" name="citizenship_doc" class="form-control">
@if(isset($crew_profile) && $crew_profile->citizenship_doc)
    <br>
    <a href="{{ asset($crew_profile->citizenship_doc) }}" target="_blank">View Document</a>
@endif
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Contact Number</label>
<input type="text" name="contact_number" class="form-control"
value="{{ old('contact_number',$crew_profile->contact_number ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Age</label>
<input type="text" name="age" class="form-control"
value="{{ old('age',$crew_profile->age ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Experience Years</label>
<input type="text" name="experience" class="form-control"
value="{{ old('experience',$crew_profile->experience ?? '') }}">
</div>
</div>

</div>
</div>

<div class="card-footer text-right">
<button type="submit" class="btn btn-primary">
{{ isset($crew_profile) ? 'Update Crew Profile' : 'Add Crew Profile' }}
</button>
</div>

</form>
</div>
</div>
</section>
@endsection