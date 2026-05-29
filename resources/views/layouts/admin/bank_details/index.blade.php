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
            <th>Is Validated</th>
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
                @if($detail->is_validated)
                    <span class="badge bg-success">Validated</span>
                @else
                    <span class="badge bg-secondary">Non Validated</span>
                @endif
            </td>

            {{-- Single <td> for all action buttons --}}
            <td>
                @if(!$detail->is_verified)
                <button type="button"
                        class="btn btn-success btn-sm validate-bank"
                        data-id="{{ $detail->id }}"
                        data-account-number="{{ $detail->account_number }}"
                        data-swift-code="{{ $detail->bank_code }}"
                        data-account-holder="{{ $detail->account_holder_name }}">
                    <i class="fas fa-check-circle"></i> Validate
                </button>
                @endif

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
            {{-- colspan must match thead column count (8) --}}
            <td colspan="8" class="text-center">
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

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<script>
$(document).ready(function() {

    // Handle validate button click
    $('.validate-bank').click(function() {
        const button = $(this);
        const bankId      = button.data('id');
        const accountNumber = button.data('account-number');
        const swiftCode   = button.data('swift-code');
        const accountHolder = button.data('account-holder');

        button.prop('disabled', true);
        button.html('<i class="fas fa-spinner fa-spin"></i> Validating...');

        $.ajax({
            url: '{{ route("admin.bank-details.validate") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                bank_detail_id: bankId,
                account_number: accountNumber,
                swift_code: swiftCode,
                account_holder_name: accountHolder
            },
            success: function(response) {
                if (response.success) {
                    let message = response.message || 'Bank account validated successfully!';
                    if (response.data && response.data.requested_name) {
                        message += `\nAccount Holder: ${response.data.requested_name}`;
                    }
                    toastr.success(message);

                    const statusCell = button.closest('tr').find('td:eq(6)');
                    statusCell.html('<span class="badge bg-success">Validated</span>');
                    button.remove();

                    setTimeout(function() { location.reload(); }, 2000);
                } else {
                    toastr.error(response.message || 'Validation failed');
                    button.prop('disabled', false);
                    button.html('<i class="fas fa-check-circle"></i> Validate');
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred during validation';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join(', ');
                }
                toastr.error(errorMessage);
                button.prop('disabled', false);
                button.html('<i class="fas fa-check-circle"></i> Validate');
            }
        });
    });

});
</script>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true
    });
});
</script>
@endpush