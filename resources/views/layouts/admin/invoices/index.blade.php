@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Proforma Invoices</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

{{-- NEW SECTION: Generate Invoice by File Number --}}
<div class="card card-primary card-outline mb-4">
    <div class="card-header">
        <h3 class="card-title">Generate Proforma by File Number</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                @php
                    $usedFileNos = \App\Models\ProformaInvoice::whereNotNull('file_no')
                        ->pluck('file_no');

                    $fileNumbers = \App\Models\VehicleBooking::whereNotNull('file_no')
                        ->whereNotIn('file_no', $usedFileNos)
                        ->distinct()
                        ->orderBy('file_no', 'desc')
                        ->pluck('file_no');
                @endphp

                <div class="form-group">
                    <label>Select File Number</label>

                    <input list="fileNumbers" id="file_no_input" class="form-control" placeholder="Type or select file number">

                    <datalist id="fileNumbers">
                        @foreach($fileNumbers as $fileNo)
                            <option value="{{ $fileNo }}">
                        @endforeach
                    </datalist>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div>
                       <button type="button" id="generateInvoiceBtn" class="btn btn-success">
                            <i class="fas fa-file-invoice"></i> Generate Proforma
                        </button>
                        <span id="invoiceStatus" class="ml-2"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- EXISTING RECEIPTS TABLE --}}
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="table-responsive">
    <table id="dataTable" class="table table-bordered table-striped show-search-bar">
        <thead>
            <tr>
                <th>S.N.</th>
                <th>Proforma No.</th>
                <th>File No.</th>
                {{-- <th>Vehicle</th> --}}
                <th>Customer</th>
                <th>Total Amount</th>
                {{-- <th>Invoice Type</th> --}}
                <th>Generated Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
           @foreach($invoices as $index => $receipt)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $receipt->invoice_number }}</td>
                <td>
                    @if($receipt->file_no)
                        <span class="badge badge-info">{{ $receipt->file_no }}</span>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </td>
                {{-- <td>
                    @if($receipt->vehicle)
                        {{ $receipt->vehicle->vehicle_name ?? $receipt->vehicle->name ?? 'N/A' }}
                    @else
                        <span class="text-muted">Multiple Vehicles</span>
                    @endif
                </td> --}}
                <td>
                    @if($receipt->customer)
                        {{ $receipt->customer->name ?? $receipt->customer->customer_name ?? 'N/A' }}
                    @else
                        Customer ID: {{ $receipt->customer_id }}
                    @endif
                </td>
                <td>रू {{ number_format($receipt->total_amount, 2) }}</td>
                {{-- <td>
                    @if($receipt->invoice_type == 'credit' || $receipt->invoice_type == 'vat')
                        <span class="badge badge-success">Credit</span>
                    @else
                        <span class="badge badge-secondary">Non VAT</span>
                    @endif
                </td> --}}
                <td>{{ $receipt->created_at->format('Y-m-d H:i') }}</td>
                <td>
                    @if($receipt->pdf_path && file_exists(public_path($receipt->pdf_path)))
                        <a href="{{ route('admin.proforma.download', $receipt->id) }}" 
                           class="btn btn-sm btn-primary" 
                        title="Download" 
                           target="_blank">
                            <i class="fas fa-download"></i>
                        </a>
                        
                        <a href="{{ asset($receipt->pdf_path) }}" 
                           class="btn btn-sm btn-info" 
                           title="View" 
                           target="_blank">
                            <i class="fas fa-eye"></i>
                        </a>
                         @if($receipt->file_no)
                        <button type="button" onclick="regenerateByFileNo('{{ $receipt->file_no }}')"
        class="btn btn-sm btn-warning" title="Regenerate">
                            <i class="fas fa-sync"></i> 
                        </button>
                        @endif
                    @else
                        <span class="badge badge-danger">File Missing</span>
                        
                        @if($receipt->file_no)
                        <button type="button" onclick="regenerateByFileNo('{{ $receipt->file_no }}')"
        class="btn btn-sm btn-warning">
                            <i class="fas fa-sync"></i> Regenerate
                        </button>
                        @endif
                    @endif
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


{{-- @push('scripts') --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function () {

      

    $('#dataTable').DataTable({
      order: [[5, 'desc']]
    });

    // GENERATE INVOICE
    $('#generateInvoiceBtn').on('click', function () {
        console.log("here");

      let fileNo = $('#file_no_input').val();

        if (!fileNo) {
            alert('Please select a file number');
            return;
        }

        let btn = $(this);

        btn.prop('disabled', true)
           .html('<i class="fas fa-spinner fa-spin"></i> Generating...');

        $('#invoiceStatus').html('<span class="text-info">Generating invoice...</span>');

        $.ajax({
            url: "{{ url('/api/proforma/generate') }}",
            method: "POST",
            data: {
                file_no: fileNo,
                _token: "{{ csrf_token() }}"
            },
            xhrFields: {
                responseType: 'blob'
            },

            success: function (blob, status, xhr) {

                let disposition = xhr.getResponseHeader('Content-Disposition');
                let fileName = 'invoice-' + fileNo + '.pdf';

                if (disposition && disposition.includes('filename=')) {
                    fileName = disposition.split('filename=')[1].replace(/"/g, '');
                }

                let url = window.URL.createObjectURL(blob);
                let a = document.createElement('a');

                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();

                window.URL.revokeObjectURL(url);
                a.remove();

                $('#invoiceStatus').html('<span class="text-success">Proforma generated!</span>');

                setTimeout(() => location.reload(), 1500);
            },

            error: function (xhr) {
                console.error(xhr.responseText);

                $('#invoiceStatus').html('<span class="text-danger">Error generating proforma</span>');
                btn.prop('disabled', false)
                   .html('<i class="fas fa-file-invoice"></i> Generate Proforma');
            }
        });
    });

});


/* ===========================
   REGENERATE (GLOBAL FUNCTION)
=========================== */
function regenerateByFileNo(fileNo) {

    if (!confirm('This will regenerate invoice. Continue?')) return;

    $('#invoiceStatus').html('<span class="text-warning">Regenerating...</span>');

    $.ajax({
        url: "{{ url('/api/proforma/regenerate') }}",
        method: "POST",
        data: {
            file_no: fileNo,
            _token: "{{ csrf_token() }}"
        },
        xhrFields: {
            responseType: 'blob'
        },

        success: function (blob) {

            let url = window.URL.createObjectURL(blob);
            let a = document.createElement('a');

            a.href = url;
            a.download = 'invoice-' + fileNo + '.pdf';

            document.body.appendChild(a);
            a.click();

            window.URL.revokeObjectURL(url);
            a.remove();

            $('#invoiceStatus').html('<span class="text-success">Proforma regenerated!</span>');

            setTimeout(() => location.reload(), 1500);
        },

        error: function (xhr) {
            console.error(xhr.responseText);
            $('#invoiceStatus').html('<span class="text-danger">Regeneration failed</span>');
        }
    });
}
</script>
{{-- @endpush --}}
@endsection