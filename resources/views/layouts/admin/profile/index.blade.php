@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
<div class="container-fluid">
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0">My Profile</h1>

    <div class="d-flex align-items-center mt-3">

    <a href="{{ route('admin.profile.edit') }}" class="btn btn-primary mr-2">
        <i class="fas fa-edit"></i> Edit Profile
    </a>

    <a href="{{ route('dashboard') }}" class="btn btn-secondary mr-2">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
    </div>
</div>
</div>
</div>

<div class="content">
<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row">


    <!-- Profile Image Card -->
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile text-center">

                @if($user->img)
                    <img class="profile-user-img img-fluid img-circle"
                         src="{{ asset('uploads/users/'.$user->img) }}"
                         alt="User profile picture">
                @else
                    <img class="profile-user-img img-fluid img-circle"
                         src="https://via.placeholder.com/150"
                         alt="User profile picture">
                @endif

                <h3 class="profile-username text-center mt-2">{{ $user->name }}</h3>

                <p class="text-muted text-center">
                    {{ ucfirst($user->user_type ?? 'User') }}
                </p>

                <ul class="list-group list-group-unbordered mb-3 mt-3 text-left">
                    <li class="list-group-item">
                        <b>Role ID</b> <span class="float-right">{{ $user->role_id ?? '-' }}</span>
                    </li>

                    @if($user->customer_id) 
                    <li class="list-group-item">
                        <b>Customer ID</b> <span class="float-right">{{ $user->customer_id ?? '-' }}</span>
                    </li>
                    @endif
                </ul>

            </div>
        </div>
    </div>

    <!-- Profile Details -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">User Details</h3>
            </div>

            <div class="card-body">

                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Full Name</div>
                    <div class="col-md-8">{{ $user->name }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Email</div>
                    <div class="col-md-8">{{ $user->email }}</div>
                </div>

               
                @if($user->mobile_number)   
                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Mobile Number</div>
                    <div class="col-md-8">
                        {{ $user->mobile_number_country_code }} {{ $user->mobile_number }}
                    </div>
                </div>
                @endif
                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">User Type</div>
                    <div class="col-md-8">
                        <span class="badge badge-info">
                            {{ ucfirst($user->user_type ?? 'N/A') }}
                        </span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4 font-weight-bold">Role</div>
                    <div class="col-md-8">
                        <span class="badge badge-success">
                            {{ $user->role_id ?? 'N/A' }}
                        </span>
                    </div>
                </div>

            </div>
        </div>
        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#changePasswordModal">
         <i class="fas fa-key"></i> Change Password
        </button>
    </div>
    <div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <form action="{{ route('admin.profile.password.update') }}" method="POST">
        @csrf

        <div class="modal-content">
            
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <!-- Current Password -->
                <div class="form-group position-relative">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control" id="current_password" required>
                    <span class="password-toggle" style="position:absolute; right:10px; top:38px; cursor:pointer;">
                        <i class="fas fa-eye" onclick="togglePassword('current_password', this)"></i>
                    </span>
                </div>

                <!-- New Password -->
                <div class="form-group position-relative">
                    <label>New Password</label>
                    <input type="password" name="password" class="form-control" id="new_password" required>
                    <span class="password-toggle" style="position:absolute; right:10px; top:38px; cursor:pointer;">
                        <i class="fas fa-eye" onclick="togglePassword('new_password', this)"></i>
                    </span>
                </div>

                <!-- Confirm Password -->
                <div class="form-group position-relative">
                    <label>Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" id="confirm_password" required>
                    <span class="password-toggle" style="position:absolute; right:10px; top:38px; cursor:pointer;">
                        <i class="fas fa-eye" onclick="togglePassword('confirm_password', this)"></i>
                    </span>
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Update Password
                </button>

                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Cancel
                </button>
            </div>

        </div>
    </form>
  </div>
</div>
     

</div>

</div>
</div>

@endsection
<script>
function togglePassword(fieldId, icon) {
    var input = document.getElementById(fieldId);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>