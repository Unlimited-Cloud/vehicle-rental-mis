@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>
            Bank Details - {{ $crew->crew_member_name ?? 'Crew' }}
        </h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<div class="card card-primary card-outline card-tabs">

<div class="card-body">

@include('layouts.admin_theme.alert')

{{-- <div class="d-flex justify-content-between mb-3">

    <a href="{{ route('admin.bank-details.create', $crew->id) }}"
       class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Add Bank Detail
    </a>

</div> --}}

<div class="table-responsive">

<table id="dataTable" class="table table-bordered table-striped show-search-bar">

    <thead>
        <tr>
            <th>SN</th>
            <th>Bank Name</th>
            <th>Bank Code</th>
            <th>Account Holder</th>
            <th>Account Number</th>
            <th>Active</th>
            <th width="150">Actions</th>
        </tr>
    </thead>

    <tbody>

    @forelse($bankDetails as $detail)

        <tr>

            <td>{{ $loop->iteration }}</td>

            <td>{{ $detail->bank_name }}</td>

            <td>{{ $detail->bank_code }}</td>

            <td>{{ $detail->account_holder_name }}</td>

            <td>{{ $detail->account_number }}</td>

            <td>
                @if($detail->is_active)
                    <span class="badge bg-success">Yes</span>
                @else
                    <span class="badge bg-secondary">No</span>
                @endif
            </td>

            <td>

                <a href="{{ route('admin.bank-details.edit', [$crew->id, $detail->id]) }}"
                   class="btn btn-primary btn-sm">
                    <i class="fas fa-edit"></i>
                </a>

                <form action="{{ route('admin.bank-details.destroy', [$crew->id, $detail->id]) }}"
                      method="POST"
                      style="display:inline-block;"
                      onsubmit="return confirm('Delete this bank detail?');">

                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-sm bg-red">
                        <i class="fa fa-trash"></i>
                    </button>

                </form>

            </td>

        </tr>

    @empty

        <tr>
            <td colspan="7" class="text-center">
                No bank details found.
            </td>
        </tr>

    @endforelse

    </tbody>

</table>

<div class="d-flex justify-content-between mb-3">

    <a href="{{ route('admin.crew_profiles.index', $crew->id) }}"
       class="btn btn-sm btn-primary">
        <i class="fa fa-arrow-left"></i> Back
    </a>

</div> 

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