@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($banner) ? 'Edit Banner' : 'Add Banner' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<form action="{{ isset($banner) ? route('admin.banner.update',$banner->id) : route('admin.banner.store') }}"
      method="POST" enctype="multipart/form-data">
@csrf
@if(isset($banner)) @method('PUT') @endif

<div class="card-body">
@include('layouts.admin_theme.alert')

<div class="row">

<div class="col-md-6">
<div class="form-group">
<label>Title *</label>
<input type="text" name="title" class="form-control"
value="{{ old('title',$banner->title ?? '') }}" required>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Image *</label>
<input type="file" name="image" class="form-control">
</div>

@if(isset($banner) && $banner->image)
    <img src="{{ asset('uploads/banners/'.$banner->image) }}" width="100">
@endif
</div>

<div class="col-md-6">
<div class="form-group">
<label>Link</label>
<input type="url" name="link" class="form-control"
value="{{ old('link',$banner->link ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Order</label>
<input type="number" name="order" class="form-control"
value="{{ old('order',$banner->order ?? 0) }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Start Date</label>
<input type="datetime-local" name="start_date" class="form-control"
value="{{ old('start_date', isset($banner->start_date) ? \Carbon\Carbon::parse($banner->start_date)->format('Y-m-d\TH:i') : '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>End Date</label>
<input type="datetime-local" name="end_date" class="form-control"
value="{{ old('end_date', isset($banner->end_date) ? \Carbon\Carbon::parse($banner->end_date)->format('Y-m-d\TH:i') : '') }}">
</div>
</div>

<div class="col-md-12">
<div class="form-group">
<label>Description</label>
<textarea name="description" class="form-control" rows="3">{{ old('description',$banner->description ?? '') }}</textarea>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Status</label>
<select name="is_active" class="form-control">
    <option value="1" {{ old('is_active',$banner->is_active ?? 1) == 1 ? 'selected' : '' }}>Active</option>
    <option value="0" {{ old('is_active',$banner->is_active ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
</select>
</div>
</div>

</div>
</div>

<div class="card-footer text-right">
<button type="submit" class="btn btn-primary">
{{ isset($banner) ? 'Update Banner' : 'Add Banner' }}
</button>
</div>

</form>
</div>
</div>
</section>
@endsection