@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>Agent Details</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<div class="row">

{{-- LEFT SIDE --}}
<div class="col-md-4">

<div class="card card-primary card-outline">

<div class="card-body box-profile text-center">

@if($agent->user && $agent->user->img)

<img class="profile-user-img img-fluid img-circle"
     src="{{ asset('uploads/users/'.$agent->user->img) }}"
     alt="Agent Image"
     style="width:120px;height:120px;object-fit:cover;">

@else

<img class="profile-user-img img-fluid img-circle"
     src="{{ asset('admin/dist/img/user2-160x160.jpg') }}"
     alt="Default Image">

@endif

<h3 class="profile-username mt-3">
    {{ $agent->user->name ?? 'N/A' }}
</h3>

<p class="text-muted">
    {{ ucfirst($agent->role) }}
</p>

@if($agent->status)

<span class="badge bg-success px-3 py-2">
    Active
</span>

@else

<span class="badge bg-danger px-3 py-2">
    Inactive
</span>

@endif

@if($agent->is_verified)

<div class="mt-2">
    <span class="badge bg-primary px-3 py-2">
        Verified
    </span>
</div>

@endif

</div>

</div>

{{-- CONTACT INFO --}}
<div class="card">

<div class="card-header">
    <h3 class="card-title">
        <i class="fas fa-phone"></i> Contact Information
    </h3>
</div>

<div class="card-body">

@if($agent->user && $agent->user->email)

<strong>Email</strong>

<p class="text-muted">
    {{ $agent->user->email }}
</p>

<hr>

@endif

@if($agent->contact_number)

<strong>Phone</strong>

<p class="text-muted">
    {{ $agent->contact_number }}
</p>

<hr>

@endif

@if($agent->address)

<strong>Address</strong>

<p class="text-muted">
    {{ $agent->address }}
</p>

@endif

</div>
</div>

</div>

{{-- RIGHT SIDE --}}
<div class="col-md-8">

{{-- BASIC DETAILS --}}
<div class="card card-primary card-outline">

<div class="card-header">
    <h3 class="card-title">
        <i class="fas fa-user"></i> Basic Information
    </h3>
</div>

<div class="card-body">

<div class="row">

<div class="col-md-6">

<strong>Role</strong>

<p class="text-muted">
    {{ ucfirst($agent->role) }}
</p>

</div>

@if($agent->commission_rate)

<div class="col-md-6">

<strong>Commission Rate</strong>

<p class="text-muted">
    {{ $agent->commission_rate }}%
</p>

</div>

@endif

@if($agent->created_at)

<div class="col-md-6">

<strong>Joined Date</strong>

<p class="text-muted">
    {{ $agent->created_at->format('d M Y') }}
</p>

</div>

@endif

</div>

</div>

</div>

{{-- DOCUMENTS --}}
@if($agent->citizenship_doc)

<div class="card card-info card-outline">

<div class="card-header">
    <h3 class="card-title">
        <i class="fas fa-id-card"></i> Documents
    </h3>
</div>

<div class="card-body">

<a href="{{ asset($agent->citizenship_doc) }}"
   target="_blank"
   class="btn btn-info">

    <i class="fas fa-file"></i> View Citizenship Document
</a>

</div>

</div>

@endif

{{-- BANK INFO --}}
@if(
    $agent->bank_name ||
    $agent->bank_account_name ||
    $agent->bank_account_number
)

<div class="card card-success card-outline">

<div class="card-header">
    <h3 class="card-title">
        <i class="fas fa-university"></i> Bank Information
    </h3>
</div>

<div class="card-body">

<div class="row">

@if($agent->bank_name)

<div class="col-md-4">

<strong>Bank Name</strong>

<p class="text-muted">
    {{ $agent->bank_name }}
</p>

</div>

@endif

@if($agent->bank_account_name)

<div class="col-md-4">

<strong>Account Name</strong>

<p class="text-muted">
    {{ $agent->bank_account_name }}
</p>

</div>

@endif

@if($agent->bank_account_number)

<div class="col-md-4">

<strong>Account Number</strong>

<p class="text-muted">
    {{ $agent->bank_account_number }}
</p>

</div>

@endif

</div>

</div>

</div>

@endif

{{-- WALLET INFO --}}
@if($agent->wallet_name || $agent->wallet_number)

<div class="card card-warning card-outline">

<div class="card-header">
    <h3 class="card-title">
        <i class="fas fa-wallet"></i> Wallet Information
    </h3>
</div>

<div class="card-body">

<div class="row">

@if($agent->wallet_name)

<div class="col-md-6">

<strong>Wallet Name</strong>

<p class="text-muted">
    {{ $agent->wallet_name }}
</p>

</div>

@endif

@if($agent->wallet_number)

<div class="col-md-6">

<strong>Wallet Number</strong>

<p class="text-muted">
    {{ $agent->wallet_number }}
</p>

</div>

@endif

</div>

</div>

</div>

@endif

{{-- REMARKS --}}
@if($agent->remarks)

<div class="card card-secondary card-outline">

<div class="card-header">
    <h3 class="card-title">
        <i class="fas fa-sticky-note"></i> Remarks
    </h3>
</div>

<div class="card-body">

<p class="text-muted mb-0">
    {{ $agent->remarks }}
</p>

</div>

</div>

@endif

</div>

</div>

</div>
</section>

@endsection