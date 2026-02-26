{{-- resources/views/layouts/admin/petrol_pumps/index.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Petrol Pumps</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('admin.petrol_pumps.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Add Petrol Pump
    </a>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>SN</th>
                <th>Pump Name</th>
                <th>Owner Name</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Current Balance</th>
                {{-- <th>Credit Limit</th> --}}
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($petrolPumps as $pump)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $pump->name }}</td>
                <td>{{ $pump->owner_name ?? 'N/A' }}</td>
                <td>{{ $pump->phone }}</td>
                <td>{{ $pump->address ?? 'N/A' }}</td>
                <td>{{ $pump->current_balance ?? 'N/A' }}</td>
                {{-- <td>{{ $pump->formatted_credit_limit }}</td> --}}
                <td>{{$pump->status}}</td>
                <td>
                    <a href="{{ route('admin.petrol_pumps.edit', $pump->id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i>
                    </a>

                    <a href="{{ route('admin.petrol_pumps.show', $pump->id) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-eye"></i>
                    </a>

                    <form action="{{ route('admin.petrol_pumps.destroy', $pump->id) }}"
                          method="POST"
                          style="display:inline-block;"
                          onsubmit="return confirm('Are you sure you want to delete this petrol pump?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm bg-red">
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