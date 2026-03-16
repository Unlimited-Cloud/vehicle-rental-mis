@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
<div class="container-fluid">

<h1>
{{ isset($category) ? 'Edit Category' : 'Create Category' }}
</h1>

</div>
</div>

<section class="content">
<div class="container-fluid">

<form action="{{ isset($category) ? route('admin.trip-categories.update',$category->id) : route('admin.trip-categories.store') }}"
method="POST">

@csrf
@if(isset($category)) @method('PUT') @endif

@include('layouts.admin_theme.alert')

<div class="card">

<div class="card-body">

<div class="form-group">
<label>Category Name</label>

<input type="text" name="name" class="form-control"
value="{{ old('name',$category->name ?? '') }}">
</div>

<div class="form-group">
<label>Description</label>

<textarea name="description" class="form-control">
{{ old('description',$category->description ?? '') }}
</textarea>
</div>

<div class="form-group">
<label>Status</label>

<select name="status" class="form-control">

<option value="1"
{{ old('status',$category->status ?? 1)==1?'selected':'' }}>
Active
</option>

<option value="0"
{{ old('status',$category->status ?? 1)==0?'selected':'' }}>
Inactive
</option>

</select>

</div>

</div>

<div class="card-footer text-right">

<a href="{{ route('admin.trip-categories.index') }}"
class="btn btn-secondary">
Back
</a>

<button class="btn btn-primary">
{{ isset($category) ? 'Update' : 'Create' }}
</button>

</div>

</div>

</form>

</div>
</section>

@endsection