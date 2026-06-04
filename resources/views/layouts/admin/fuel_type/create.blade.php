@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($fuelType) ? 'Edit Fuel Type' : 'Add Fuel Type' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<form action="{{ isset($fuelType) ? route('admin.fuel-type.update',$fuelType->id) : route('admin.fuel-type.store') }}"
      method="POST"
      enctype="multipart/form-data">

@csrf
@if(isset($fuelType))
    @method('PUT')
@endif

<div class="card-body">

    @include('layouts.admin_theme.alert')

    <div class="row">

        <div class="col-md-6">
            <div class="form-group">
                <label>Name *</label>
                <input type="text"
                       name="name"
                       class="form-control"
                       value="{{ old('name',$fuelType->name ?? '') }}"
                       required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label>Logo</label>
                <input type="file"
                       name="logo"
                       class="form-control">
            </div>

            @if(isset($fuelType) && $fuelType->logo)
                <img src="{{ asset('uploads/fuel-types/'.$fuelType->logo) }}"
                     width="80">
            @endif
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label>Status</label>

                <select name="status" class="form-control">
                    <option value="1"
                        {{ old('status',$fuelType->status ?? 1) == 1 ? 'selected' : '' }}>
                        Active
                    </option>

                    <option value="0"
                        {{ old('status',$fuelType->status ?? 1) == 0 ? 'selected' : '' }}>
                        Inactive
                    </option>
                </select>
            </div>
        </div>

    </div>

</div>

<div class="card-footer text-right">
    <button type="submit" class="btn btn-primary">
        {{ isset($fuelType) ? 'Update Fuel Type' : 'Add Fuel Type' }}
    </button>
</div>

</form>

</div>
</div>
</section>
@endsection
