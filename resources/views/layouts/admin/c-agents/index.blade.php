@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>Agents</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<div class="card card-primary card-outline card-tabs">

<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="d-flex justify-content-between mb-3">

<a href="{{ route('admin.agents.create') }}"
   class="btn btn-sm btn-primary">

    <i class="fa fa-plus"></i> Add Agent
</a>

</div>

<div class="table-responsive">

<table id="dataTable"
       class="table table-bordered table-striped show-search-bar">

<thead>
<tr>

<th>SN</th>
<th>Image</th>
<th>Agent Code</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Contact</th>
<th>Wallet</th>
<th>Status</th>
<th>Actions</th>

</tr>
</thead>

<tbody>

@foreach($agents as $a)

<tr>

<td>{{ $loop->iteration }}</td>

<td>

@if($a->user && $a->user->img)

<img src="{{ asset('uploads/users/'.$a->user->img) }}"
     width="60"
     height="60"
     style="object-fit:cover; border-radius:5px;">

@else

N/A

@endif

</td>

<td>{{ $a->agent_code }}</td>

<td>{{ $a->user->name ?? 'N/A' }}</td>

<td>{{ $a->user->email ?? 'N/A' }}</td>

<td>
<span class="badge bg-info">
    {{ ucfirst($a->role) }}
</span>
</td>

<td>{{ $a->contact_number }}</td>

<td>
{{ $a->wallet_name }}
<br>
<small>{{ $a->wallet_number }}</small>
</td>

<td>

@if($a->status)

<span class="badge bg-success">
    Active
</span>

@else

<span class="badge bg-danger">
    Inactive
</span>

@endif

</td>

<td>
<a href="{{ route('admin.agents.show', $a->id) }}"
   class="btn btn-primary btn-sm">

    <i class="fas fa-eye"></i>
</a>

<a href="{{ route('admin.agents.edit', $a->id) }}"
   class="btn btn-primary btn-sm">

    <i class="fas fa-edit"></i>
</a>

<form action="{{ route('admin.agents.destroy', $a->id) }}"
      method="POST"
      style="display:inline-block;"
      onsubmit="return confirm('Delete this agent?');">

    @csrf
    @method('DELETE')

    <button type="submit"
            class="btn btn-sm bg-red">

        <i class="fa fa-trash"></i>
    </button>

</form>

</td>

</tr>

@endforeach

</tbody>

</table>

</div>
</div>
</div>
</div>
</section>

@endsection

@push('scripts')

<script>
$(document).ready(function() {
    $('#dataTable').DataTable();
});
</script>

@endpush