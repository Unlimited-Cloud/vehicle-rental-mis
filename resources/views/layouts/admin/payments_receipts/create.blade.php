{{-- resources/views/layouts/admin/payment_receipts/create.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Receive Payment</h1>
            <a href="{{ route('admin.payment_receipt.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Receipts
            </a>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Payment Details</h3>
        </div>
        <div class="card-body">
            <form id="paymentForm">
                @csrf
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Customer *</label>
                            <select id="customer_id" class="form-control" required>
                                <option value="">-- Select Customer --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Payment Date *</label>
                            <input type="date" id="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Payment Method *</label>
                            <select id="payment_method" class="form-control" required>
                                <option value="">-- Select --</option>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="wallet">Digital Wallet</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Bank Details (hidden by default) -->
                <div id="bank_details" class="row" style="display: none;">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Bank Name</label>
                            <input type="text" id="bank_name" class="form-control" placeholder="Bank Name">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Account Number</label>
                            <input type="text" id="bank_account" class="form-control" placeholder="Account Number">
                        </div>
                    </div>
                </div>
                
                <!-- Cheque Details (hidden by default) -->
                <div id="cheque_details" class="row" style="display: none;">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Cheque Number</label>
                            <input type="text" id="cheque_number" class="form-control" placeholder="Cheque Number">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Cheque Date</label>
                            <input type="date" id="cheque_date" class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="tds_applied">
                                <label class="custom-control-label" for="tds_applied">
                                    Apply TDS Deduction (Rate: {{ $tdsRateValue }}%)
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Transaction ID (Optional)</label>
                            <input type="text" id="transaction_id" class="form-control" placeholder="Transaction/Reference ID">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Notes (Optional)</label>
                    <textarea id="notes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
                </div>
                
                <hr>
                
                <h4>Unpaid Invoices</h4>
                <div id="invoices_list">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 30px;"><input type="checkbox" id="select_all"></th>
                                <th>Invoice No.</th>
                                <th>Date</th>
                                <th class="text-right">Amount (रू)</th>
                            </tr>
                        </thead>
                        <tbody id="invoices_tbody">
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Select a customer to view unpaid invoices
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Total Selected Amount:</th>
                                <th class="text-right" id="total_selected">रू 0.00</th>
                            </tr>
                            <tr id="tds_row" style="display: none;">
                                <th colspan="3" class="text-right text-danger">TDS Deduction ({{ $tdsRateValue }}%):</th>
                                <th class="text-right text-danger" id="tds_amount">- रू 0.00</th>
                            </tr>
                            <tr class="bg-info">
                                <th colspan="3" class="text-right">Net Payable Amount:</th>
                                <th class="text-right" id="net_payable">रू 0.00</th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-right">Amount Received (रू):</th>
                                <th class="text-right">
                                    <input type="number" id="received_amount" class="form-control" step="0.01" placeholder="0.00" style="width: 150px;">
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </form>
        </div>
        <div class="card-footer">
            <button type="button" class="btn btn-secondary" onclick="window.history.back()">Cancel</button>
            <button type="button" id="submit_payment" class="btn btn-success float-right" disabled>Process Payment</button>
        </div>
    </div>
</div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Customer selection
    $('#customer_id').change(function() {
        var customerId = $(this).val();
        if (customerId) {
            loadInvoices(customerId);
        } else {
            resetInvoices();
        }
    });
    
    // Payment method change - show/hide bank/cheque fields
    $('#payment_method').change(function() {
        var method = $(this).val();
        $('#bank_details, #cheque_details').hide();
        
        if (method === 'bank') {
            $('#bank_details').show();
        } else if (method === 'cheque') {
            $('#cheque_details').show();
        }
    });
    
    // TDS checkbox
    $('#tds_applied').change(function() {
        calculateTotal();
    });
    
    // Select all checkbox
    $('#select_all').change(function() {
        $('.invoice_checkbox').prop('checked', $(this).prop('checked'));
        calculateTotal();
    });
    
    // Received amount input
    $('#received_amount').on('input', function() {
        validateReceivedAmount();
    });
    
    // Submit button
    $('#submit_payment').click(function() {
        processPayment();
    });
});

function loadInvoices(customerId) {
    $('#invoices_tbody').html('<tr><td colspan="4" class="text-center text-muted">Loading...</td></tr>');
    
    $.ajax({
        url: "{{ route('admin.payment_receipt.get_unpaid') }}",
        method: "POST",
        data: {
            customer_id: customerId,
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {
            if (response.success && response.invoices.length > 0) {
                displayInvoices(response.invoices);
            } else {
                $('#invoices_tbody').html('<tr><td colspan="4" class="text-center text-muted">No unpaid invoices found</td></tr>');
                $('#submit_payment').prop('disabled', true);
            }
        },
        error: function() {
            $('#invoices_tbody').html('<tr><td colspan="4" class="text-center text-muted">Error loading invoices</td></tr>');
        }
    });
}

function displayInvoices(invoices) {
    var tbody = $('#invoices_tbody');
    tbody.empty();
    
    $.each(invoices, function(index, invoice) {
        var row = '<tr>' +
            '<td><input type="checkbox" class="invoice_checkbox" data-id="' + invoice.id + '" data-amount="' + invoice.total_amount + '"></td>' +
            '<td>' + invoice.receipt_number + '</td>' +
            '<td>' + invoice.created_at + '</td>' +
            '<td class="text-right">रू ' + parseFloat(invoice.total_amount).toFixed(2) + '</td>' +
            '</tr>';
        tbody.append(row);
    });
    
    $('.invoice_checkbox').change(function() {
        calculateTotal();
        updateSelectAll();
    });
    
    calculateTotal();
}

function resetInvoices() {
    $('#invoices_tbody').html('<tr><td colspan="4" class="text-center text-muted">Select a customer to view unpaid invoices</td></tr>');
    $('#total_selected').html('रू 0.00');
    $('#net_payable').html('रू 0.00');
    $('#tds_row').hide();
    $('#received_amount').val('');
    $('#submit_payment').prop('disabled', true);
}

function calculateTotal() {
    var total = 0;
    $('.invoice_checkbox:checked').each(function() {
        total += parseFloat($(this).data('amount'));
    });
    
    var tdsApplied = $('#tds_applied').is(':checked');
    var tdsRate = {{ $tdsRateValue }};
    var tdsAmount = 0;
    var netAmount = total;
    
    if (tdsApplied && total > 0) {
        tdsAmount = (total * tdsRate) / 100;
        netAmount = total - tdsAmount;
        $('#tds_row').show();
        $('#tds_amount').html('- रू ' + tdsAmount.toFixed(2));
    } else {
        $('#tds_row').hide();
    }
    
    $('#total_selected').html('रू ' + total.toFixed(2));
    $('#net_payable').html('रू ' + netAmount.toFixed(2));
    
    var hasSelection = $('.invoice_checkbox:checked').length > 0;
    $('#submit_payment').prop('disabled', !hasSelection);
    
    if (hasSelection) {
        validateReceivedAmount();
    }
}

function validateReceivedAmount() {
    var netPayable = parseFloat($('#net_payable').text().replace('रू ', ''));
    var received = parseFloat($('#received_amount').val());
    
    if (isNaN(received)) {
        $('#received_amount').css('border-color', '#ced4da');
        return;
    }
    
    var diff = received - netPayable;
    
    if (Math.abs(diff) < 0.01) {
        $('#received_amount').css('border-color', '#28a745'); // Green - exact
    } else if (diff > 0) {
        $('#received_amount').css('border-color', '#ffc107'); // Yellow - overpayment
    } else {
        $('#received_amount').css('border-color', '#dc3545'); // Red - short payment
    }
}

function updateSelectAll() {
    var allChecked = $('.invoice_checkbox:checked').length === $('.invoice_checkbox').length;
    $('#select_all').prop('checked', allChecked);
}

function processPayment() {
    var selectedInvoices = [];
    $('.invoice_checkbox:checked').each(function() {
        selectedInvoices.push($(this).data('id'));
    });
    
    if (selectedInvoices.length === 0) {
        Swal.fire('Error', 'Please select at least one invoice', 'error');
        return;
    }
    
    var receivedAmount = parseFloat($('#received_amount').val());
    if (isNaN(receivedAmount) || receivedAmount <= 0) {
        Swal.fire('Error', 'Please enter the received amount', 'error');
        return;
    }
    
    var netPayable = parseFloat($('#net_payable').text().replace('रू ', ''));
    var formData = {
        customer_id: $('#customer_id').val(),
        selected_invoices: selectedInvoices,
        payment_method: $('#payment_method').val(),
        payment_date: $('#payment_date').val(),
        amount: netPayable,
        received_amount: receivedAmount,
        tds_applied: $('#tds_applied').is(':checked') ? 1 : 0,
        bank_name: $('#bank_name').val(),
        bank_account: $('#bank_account').val(),
        cheque_number: $('#cheque_number').val(),
        cheque_date: $('#cheque_date').val(),
        transaction_id: $('#transaction_id').val(),
        notes: $('#notes').val(),
        _token: "{{ csrf_token() }}"
    };
    
    if (!formData.payment_method) {
        Swal.fire('Error', 'Please select payment method', 'error');
        return;
    }
    
    if (formData.payment_method === 'bank' && !formData.bank_name) {
        Swal.fire('Error', 'Please enter bank name', 'error');
        return;
    }
    
    if (formData.payment_method === 'cheque' && !formData.cheque_number) {
        Swal.fire('Error', 'Please enter cheque number', 'error');
        return;
    }
    
    $('#submit_payment').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
    
    $.ajax({
        url: "{{ route('admin.payment_receipt.store') }}",
        method: "POST",
        data: formData,
        success: function(response) {
            if (response.success) {
                Swal.fire('Success!', 'Payment processed! Receipt: ' + response.receipt.receipt_number, 'success')
                    .then(() => {
                        window.location.href = "{{ route('admin.payment_receipt.index') }}";
                    });
            } else {
                Swal.fire('Error', response.message, 'error');
                $('#submit_payment').prop('disabled', false).html('Process Payment');
            }
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Unknown error';
            Swal.fire('Error', msg, 'error');
            $('#submit_payment').prop('disabled', false).html('Process Payment');
        }
    });
}
</script>
@endsection