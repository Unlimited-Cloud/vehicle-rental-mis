@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
<div class="container-fluid">
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0">Edit Profile</h1>
</div>
</div>
</div>

<div class="content">
<div class="container-fluid">

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card">
<div class="card-body">

<form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
@csrf

<div class="row">

<div class="col-md-6">
    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" value="{{ $user->name }}" class="form-control">
    </div>
</div>

<div class="col-md-6">
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="{{ $user->email }}" class="form-control">
    </div>
</div>

<div class="col-md-6">
    <div class="form-group">
        <label>Profile Image</label>
        <input type="file" name="img" class="form-control">
    </div>
</div>

<div class="col-md-6">
    <label>Preview</label><br>
    @if($user->img)
        <img src="{{ asset('uploads/users/'.$user->img) }}" width="100" height="100">
    @else
        <p>No Image</p>
    @endif
</div>

</div>

<div class="d-flex align-items-center gap-2 mt-3">
    <button type="submit" class="btn btn-success mr-2">
        <i class="fas fa-save"></i> Update Profile
    </button>

    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

</form>

</div>
</div>

</div>
</div>

@endsection