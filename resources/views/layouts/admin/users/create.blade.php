@extends('layouts.admin_theme.container')

@section('dynamicdata')

    <div class="content-header">
        <div class="container-fluid">
            <h1>{{ isset($user) ? 'Edit User' : 'Create User' }}</h1>
        </div>
    </div>

    <section class="content">
    <div class="container-fluid">
    <div class="card card-primary card-outline">

    <form action="{{ isset($user) ? route('admin.users.update', $user->id) : route('admin.users.store') }}"
         enctype="multipart/form-data"  method="POST">

    @csrf
    @if(isset($user)) @method('PUT') @endif

    <div class="card-body">

    @include('layouts.admin_theme.alert')

    <div class="row">
        @if($isCustomerUser == 'N')
        <div class="col-md-6">
            <div class="form-group">
                <label>Customer</label>
                <select name="customer_id" id="customer_id" class="form-control">
                    <option value="">--Select Customer--</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif


<div class="col-md-6">
    <div class="form-group">
        <label>Profile Image</label>
        <input type="file" name="img" class="form-control">
    </div>
    <div class="col-md-6">
    <label>Preview</label><br>

    @if(isset($user) && $user->img)
        <img src="{{ asset('uploads/users/'.$user->img) }}" width="100" height="100">
    @else
        <p>No Image</p>
    @endif
</div>
</div>



</div>

    <div class="col-md-6">
    <div class="form-group">
    <label>Name *</label>
    <input type="text" name="name" class="form-control"
    value="{{ old('name', $user->name ?? '') }}" required>
    </div>
    </div>

    <div class="col-md-6">
    <div class="form-group">
    <label>Email *</label>
    <input type="email" name="email" class="form-control"
    value="{{ old('email', $user->email ?? '') }}" required>
    </div>
    </div>

    <div class="col-md-6">
    <div class="form-group">
    <label>Password {{ isset($user) ? '' : '*' }}</label>
    <input type="password" name="password" class="form-control"
    {{ isset($user) ? '' : 'required' }}>
    @if(isset($user))
    <small class="text-muted">Leave blank to keep existing password</small>
    @endif
    </div>
    </div>

    <div class="col-md-6">
    <div class="form-group">
    <label>Confirm Password</label>
    <input type="password" name="password_confirmation" class="form-control">
    </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Role</label>
            <select name="role_id" id="role_id" class="form-control">
                <option value="">--Select Role--</option>
                @foreach($roles as $role)
                <option value="{{ $role->id }}" {{ isset($user) && $user->role_id == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    </div>
    </div>

                <div class="card-footer text-right">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>

                    <button type="submit" class="btn btn-primary">
                        {{ isset($user) ? 'Update User' : 'Create User' }}
                    </button>
                </div>

    </form>
    </div>
    </div>
    </section>

@endsection