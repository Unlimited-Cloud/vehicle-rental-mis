@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($brand) ? 'Edit Brand' : 'Add Brand' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<form action="{{ isset($brand) ? route('admin.brand.update',$brand->id) : route('admin.brand.store') }}"
      method="POST" enctype="multipart/form-data">
@csrf
@if(isset($brand)) @method('PUT') @endif

<div class="card-body">
@include('layouts.admin_theme.alert')

<div class="row">

<div class="col-md-6">
<div class="form-group">
<label>Name *</label>
<input type="text" name="name" class="form-control"
value="{{ old('name',$brand->name ?? '') }}" required>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Logo</label>
<input type="file" name="logo" class="form-control">
</div>

@if(isset($brand) && $brand->logo)
    <img src="{{ asset('uploads/brands/'.$brand->logo) }}" width="80">
@endif
</div>



</div>
</div>

<div class="card-footer text-right">
<button type="submit" class="btn btn-primary">
{{ isset($brand) ? 'Update Brand' : 'Add Brand' }}
</button>
</div>

</form>
</div>
</div>
</section>
@endsection