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

<form action="{{ isset($module) ? route('admin.permissions.update',$module->id) : route('admin.permissions.store') }}"
      method="POST">

@csrf
@if(isset($module)) @method('PUT') @endif

<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="row">

<div class="col-md-6">
<div class="form-group">
<label>Module *</label>
<select name="module_id" class="form-control">
    <option value="">--Select Module--</option>
    @foreach($modules as $module_list)
    <option value="{{ $module_list->id }}" @if(isset($module) && $permission->module_id == $module_list->id) {{ 'selected' }} @endif>{{ $module_list->name }}</option>
    @endforeach
</select>
</div>
</div>

<div class="col-md-6">
    <div class="form-group">
        <label class="d-block">Permission *</label>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="permission_create" id="permission_create" value="1">
            <label class="form-check-label" for="permission_create">Create</label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="permission_read" id="permission_read" value="1">
            <label class="form-check-label" for="permission_read">Read</label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="permission_update" id="permission_update" value="1">
            <label class="form-check-label" for="permission_update">Update</label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="permission_delete" id="permission_delete" value="1">
            <label class="form-check-label" for="permission_delete">Delete</label>
        </div>
    </div>
</div>

</div>
</div>

            <div class="card-footer text-right">
                <a href="{{ route('admin.modules.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back
                </a>

                <button type="submit" class="btn btn-primary">
                    {{ isset($module) ? 'Update Permission' : 'Create Permission' }}
                </button>
            </div>

</form>
</div>
</div>
</section>

@endsection