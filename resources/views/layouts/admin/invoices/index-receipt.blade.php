@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Vehicle Invoices</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

{{-- NEW SECTION: Generate Invoice by File Number --}}
<div class="card card-primary card-outline mb-4">
    <div class="card-header">
        <h3 class="card-title">Generate Invoice by File Number</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Select File Number</label>
                    <select id="file_no_select" class="form-control">
                        <option value="">-- Select File Number --</option>
                        @php
                            $fileNumbers = \App\Models\VehicleBooking::whereNotNull('file_no')
                                ->distinct()
                                ->orderBy('file_no', 'desc')
                                ->pluck('file_no');
                        @endphp
                        @foreach($fileNumbers as $fileNo)
                            <option value="{{ $fileNo }}">{{ $fileNo }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div>
                       <button type="button" id="generateInvoiceBtn" class="btn btn-success">
                            <i class="fas fa-file-invoice"></i> Generate Invoice
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
                <th>Receipt No.</th>
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
           @foreach($receipts as $index => $receipt)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $receipt->receipt_number }}</td>
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
    <div class="d-flex flex-wrap" style="gap: 5px;">

        {{-- Existing Invoice PDF --}}
        @if($receipt->pdf_path && file_exists(public_path($receipt->pdf_path)))
            <a href="{{ route('admin.vehicle_receipt.download', $receipt->id) }}" 
               class="btn btn-sm btn-primary" 
               title="Download Invoice" 
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

        {{-- Final Receipt PDF --}}
        @if($receipt->receipt_path && file_exists(public_path($receipt->receipt_path)))
            <a href="{{ route('admin.vehicle_final_receipt.download', $receipt->id) }}" 
               class="btn btn-sm btn-success" 
               title="Download Receipt" 
               target="_blank">
                <i class="fas fa-download"></i> Receipt
            </a>
        @else
            <button class="btn btn-sm btn-success"
                    onclick="openReceiptModal({{ $receipt->id }})">
                <i class="fas fa-money-check"></i> Finalize
            </button>
        @endif

    </div>
</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="modal fade" id="receiptModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5>Finalize Receipt</h5>
      </div>

      <div class="modal-body">

        <input type="hidden" id="receipt_id">

        <div class="form-group">
            <label>Payment Method</label>
            <select id="payment_method" class="form-control">
                <option value="">Select</option>
                <option value="cash">Cash</option>
                <option value="cheque">Cheque</option>
                <option value="bank">Bank Transfer</option>
            </select>
        </div>

        <div id="cheque_fields" style="display:none;">
            <input type="text" id="check_no" class="form-control mb-2" placeholder="Cheque Number">
            <input type="date" id="check_date" class="form-control">
        </div>

        <div id="bank_fields" style="display:none;">
            <input type="text" id="bank_name" class="form-control mb-2" placeholder="Bank Name">
            <input type="text" id="bank_account" class="form-control" placeholder="Bank Account">
        </div>

        <div class="form-group mt-2">
            <label>Amount</label>
            <input type="number" id="amount" class="form-control">
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-primary" onclick="saveReceipt()">Save</button>
      </div>

    </div>
  </div>
</div>

</div>
</div>
</div>
</section>


{{-- @push('scripts') --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function openReceiptModal(id) {
    $('#receipt_id').val(id);
    $('#receiptModal').modal('show');
}
$(document).ready(function () {

    $('#dataTable').DataTable({
      order: [[5, 'desc']]
    });

    // GENERATE INVOICE
    $('#generateInvoiceBtn').on('click', function () {
        console.log("here");

        let fileNo = $('#file_no_select').val();

        if (!fileNo) {
            alert('Please select a file number');
            return;
        }

        let btn = $(this);

        btn.prop('disabled', true)
           .html('<i class="fas fa-spinner fa-spin"></i> Generating...');

        $('#invoiceStatus').html('<span class="text-info">Generating invoice...</span>');

        $.ajax({
            url: "{{ url('/api/invoice/generate') }}",
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

                $('#invoiceStatus').html('<span class="text-success">Invoice generated!</span>');

                setTimeout(() => location.reload(), 1500);
            },

            error: function (xhr) {
                console.error(xhr.responseText);

                $('#invoiceStatus').html('<span class="text-danger">Error generating invoice</span>');
                btn.prop('disabled', false)
                   .html('<i class="fas fa-file-invoice"></i> Generate Invoice');
            }
        });
    });

 

$('#payment_method').on('change', function () {
    let val = $(this).val();

    $('#cheque_fields').hide();
    $('#bank_fields').hide();

    if (val === 'cheque') {
        $('#cheque_fields').show();
    } else if (val === 'bank') {
        $('#bank_fields').show();
    }
});



});

function saveReceipt() {

    $.ajax({
        url: "/dashboard/receipt/finalize",
        method: "POST",
        data: {
            id: $('#receipt_id').val(),
            payment_method: $('#payment_method').val(),
            check_no: $('#check_no').val(),
            check_date: $('#check_date').val(),
            bank_name: $('#bank_name').val(),
            bank_account: $('#bank_account').val(),
            amount: $('#amount').val(),
            _token: "{{ csrf_token() }}"
        },

        success: function () {
            alert('Receipt finalized!');
            location.reload();
        }
    });
}

/* ===========================
   REGENERATE (GLOBAL FUNCTION)
=========================== */
function regenerateByFileNo(fileNo) {

    if (!confirm('This will regenerate invoice. Continue?')) return;

    $('#invoiceStatus').html('<span class="text-warning">Regenerating...</span>');

    $.ajax({
        url: "{{ url('/api/invoice/regenerate') }}",
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

            $('#invoiceStatus').html('<span class="text-success">Invoice regenerated!</span>');

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