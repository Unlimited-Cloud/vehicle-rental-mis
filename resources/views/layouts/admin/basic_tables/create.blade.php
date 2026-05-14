@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($item) ? 'Edit Basic Setup' : 'Add Basic Setup' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<form action="{{ isset($item) ? route('admin.basic_tables.update',$item->id) : route('admin.basic_tables.store') }}"
      method="POST"
      enctype="multipart/form-data">
@csrf
@if(isset($item)) @method('PUT') @endif

<div class="card-body">
@include('layouts.admin_theme.alert')

<div class="row">

<!-- Logo -->
<div class="col-md-6">
<div class="form-group">
<label>Logo</label>
<input type="file" name="logo" class="form-control">

@if(isset($item) && $item->logo)
    <div class="mt-2">
        <img src="{{ asset($item->logo) }}" width="80">
    </div>
@endif
</div>
</div>


<!-- Login Logo -->
<div class="col-md-6">
<div class="form-group">
<label>Login Logo</label>
<input type="file" name="login_logo" class="form-control">

@if(isset($item) && $item->login_logo)
    <div class="mt-2">
        <img src="{{ asset($item->login_logo) }}" width="80">
    </div>
@endif
</div>
</div>


{{-- @if(isset($item) && $item->login_logo)
    <div class="mt-2">
        <img src="{{ asset($item->login_logo) }}" width="80">
    </div>
@endif --}}
{{-- </div>
</div> --}}


<!-- Company Name -->
<div class="col-md-12">
<div class="form-group">
<label>Company Name</label>
<input type="text" name="company_name" class="form-control"
value="{{ old('company_name',$item->company_name ?? '') }}">
</div>
</div>

<!-- About  us -->
<div class="col-md-12">
<div class="form-group">
<label>About Us</label>
<textarea name="about_us" class="form-control ckeditor" rows="5">{{ old('about_us',$item->about_us ?? '') }}</textarea>
</div>
</div>


<!-- Contact Us -->
<div class="col-md-12">
<div class="form-group">
<label>Contact Us</label>
<textarea name="contact_us" class="form-control ckeditor" rows="5">{{ old('contact_us',$item->contact_us ?? '') }}</textarea>
</div>


<div class="row">
    
    <!-- Privacy Policy -->
    <div class="col-md-6">
        <div class="form-group">
            <label>Privacy Policy</label>
            <input type="text" name="privacy_policy" class="form-control"
                value="{{ old('privacy_policy', $item->privacy_policy ?? '') }}">
        </div>
    </div>

    <!-- Terms and Conditions -->
    <div class="col-md-6">
        <div class="form-group">
            <label>Terms and Conditions</label>
            <input type="text" name="terms_and_conditions" class="form-control"
                value="{{ old('terms_and_conditions', $item->terms_and_conditions ?? '') }}">
        </div>
    </div>

</div>
<!-- Footer Text -->
<div class="col-md-12">
<div class="form-group">
<label>Footer Text</label>
<textarea name="footer_text" class="form-control" rows="3">{{ old('footer_text',$item->footer_text ?? '') }}</textarea>
</div>
</div>

</div>
</div>

<div class="card-footer text-right">
<button type="submit" class="btn btn-primary">
    {{ isset($item) ? 'Update' : 'Submit' }}
</button>
<a href="{{ route('admin.basic_tables.index') }}" class="btn btn-secondary">
    <i class="fa fa-arrow-left"></i> Back to List
</a>
</div>

</form>
</div>
</div>
</section>
@endsection

@section('scripts')

<script src="https://cdn.ckeditor.com/ckeditor5/23.0.0/classic/ckeditor.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll('.ckeditor').forEach(function (textarea) {

        ClassicEditor
            .create(textarea)
            .catch(error => {
                console.error(error);
            });

    });

});
</script>

@endsection