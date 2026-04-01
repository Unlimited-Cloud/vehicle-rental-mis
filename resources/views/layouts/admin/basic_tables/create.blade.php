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


@if(isset($item) && $item->login_logo)
    <div class="mt-2">
        <img src="{{ asset($item->login_logo) }}" width="80">
    </div>
@endif
</div>
</div>


<!-- Company Name -->
<div class="col-md-6">
<div class="form-group">
<label>Company Name</label>
<input type="text" name="company_name" class="form-control"
value="{{ old('company_name',$item->company_name ?? '') }}">
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