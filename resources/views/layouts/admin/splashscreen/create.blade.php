@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($splashscreen) ? 'Edit Splashscreen' : 'Add Splashscreen' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<form action="{{ isset($splashscreen) ? route('admin.splashscreen.update',$splashscreen->id) : route('admin.splashscreen.store') }}"
      method="POST" enctype="multipart/form-data">
@csrf
@if(isset($splashscreen)) @method('PUT') @endif

<div class="card-body">
@include('layouts.admin_theme.alert')

<div class="row">

    <!-- Header -->
    <div class="col-md-6">
        <div class="form-group">
            <label>Header *</label>
            <input type="text" name="header" class="form-control"
                   value="{{ old('header',$splashscreen->header ?? '') }}" required>
        </div>
    </div>

    <!-- Order -->
    <div class="col-md-6">
        <div class="form-group">
            <label>Order</label>
            <input type="number" name="order" class="form-control"
                   value="{{ old('order',$splashscreen->order ?? 0) }}">
        </div>
    </div>

    <!-- Image -->
    <div class="col-md-6">
        <div class="form-group">
            <label>Image</label>
            <input type="file" name="image" class="form-control">
        </div>
    </div>

    <!-- Description -->
    <div class="col-md-6">
        <div class="form-group">
            <label>Description *</label>
            <textarea name="description" class="form-control" required>
{{ old('description',$splashscreen->description ?? '') }}
            </textarea>
        </div>
    </div>

    <!-- Preview Image -->
    @if(isset($splashscreen) && $splashscreen->image)
    <div class="col-md-6">
        <div class="form-group">
            <label>Current Image</label><br>
            <img src="{{ asset('uploads/splashscreens/'.$splashscreen->image) }}" width="100">
        </div>
    </div>
    @endif

</div>

</div>

<div class="card-footer text-right">
    <button type="submit" class="btn btn-primary">
        {{ isset($splashscreen) ? 'Update Splashscreen' : 'Add Splashscreen' }}
    </button>
</div>

</form>
</div>
</div>
</section>
@endsection