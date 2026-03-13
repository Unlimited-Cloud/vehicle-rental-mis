@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($module) ? 'Edit Module' : 'Create Module' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<form action="{{ isset($module) ? route('admin.modules.update',$module->id) : route('admin.modules.store') }}"
      method="POST">

@csrf
@if(isset($module)) @method('PUT') @endif

<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="row">

<div class="col-md-6">
<div class="form-group">
<label>Name *</label>
<input type="text" name="name" class="form-control"
value="{{ old('name',$module->name ?? '') }}" required>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Parent Module *</label>
<select name="parent_id" class="form-control">
    <option value="">--Select parent Module--</option>
    @foreach($modules as $module_list)
    <option value="{{ $module_list->id }}" @if(isset($module) && $module->parent_id == $module_list->id) {{ 'selected' }} @endif>{{ $module_list->name }}</option>
    @endforeach
</select>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Icon *</label>
<input type="text" name="icon" class="form-control"
value="{{ old('icon',$module->icon ?? '') }}" required>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Route *</label>
<input type="text" name="route" class="form-control"
value="{{ old('route',$module->route ?? '') }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Order By *</label>
<input type="text" name="order_by" class="form-control"
value="{{ old('order_by',$module->order_by ?? '') }}">
</div>
</div>

</div>
</div>

            <div class="card-footer text-right">
                <a href="{{ route('admin.modules.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back
                </a>

                <button type="submit" class="btn btn-primary">
                    {{ isset($module) ? 'Update Module' : 'Create Module' }}
                </button>
            </div>

</form>
</div>
</div>
</section>

@endsection