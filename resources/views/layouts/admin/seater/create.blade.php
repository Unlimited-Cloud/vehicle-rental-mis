@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($seater) ? 'Edit Seater' : 'Add Seater' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<form action="{{ isset($seater) ? route('admin.seater.update',$seater->id) : route('admin.seater.store') }}"
      method="POST" enctype="multipart/form-data">
@csrf
@if(isset($seater)) @method('PUT') @endif

<div class="card-body">
@include('layouts.admin_theme.alert')

<div class="row">

<div class="col-md-6">
<div class="form-group">
<label>Name *</label>
<input type="text" name="name" class="form-control"
value="{{ old('name',$seater->name ?? '') }}" required>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Logo</label>
<input type="file" name="logo" class="form-control">
</div>

@if(isset($seater) && $seater->logo)
    <img src="{{ asset('uploads/seaters/'.$seater->logo) }}" width="80">
@endif
</div>



</div>
</div>

<div class="card-footer text-right">
<button type="submit" class="btn btn-primary">
{{ isset($seater) ? 'Update Seater' : 'Add Seater' }}
</button>
</div>

</form>
</div>
</div>
</section>
@endsection