{{-- resources/views/invoices/payment-receipt-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt - {{ $receipt->receipt_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 100%;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-left {
            width: 20%;
            vertical-align: top;
        }
        .header-center {
            width: 60%;
            text-align: center;
            vertical-align: top;
        }
        .header-right {
            width: 20%;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin-top: 5px;
        }
        .paid-badge {
            text-align: center;
            color: green;
            font-weight: bold;
            font-size: 14px;
            margin-top: 2px;
            letter-spacing: 2px;
        }
        .company-name {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        .line {
            border-top: 1px solid #000;
            margin: 8px 0;
        }
        .info-table {
            width: 100%;
            margin-top: 8px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 4px 2px;
            border: none;
            vertical-align: top;
        }
        .right {
            text-align: right;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        table.items th,
        table.items td {
            border: 1px solid #000;
            padding: 6px 4px;
            font-size: 10px;
        }
        table.items th {
            text-align: center;
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        .totals-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 10px;
        }
        .totals-table td:first-child {
            font-weight: normal;
        }
        .totals-right {
            text-align: right;
        }
        .signature {
            margin-top: 45px;
            width: 100%;
            border-collapse: collapse;
        }
        .signature td {
            border: none;
            padding-top: 20px;
            font-size: 10px;
            vertical-align: bottom;
        }
        .sign-line {
            border-top: 1px solid #000;
            display: inline-block;
            padding-bottom: 2px;
            min-width: 120px;
        }
        .sign-long {
            min-width: 180px;
        }
        .clearfix {
            clear: both;
        }
        .amount-in-words {
            margin-top: 8px;
            font-size: 9px;
        }
        .footer-note {
            margin-top: 15px;
            font-size: 9px;
            text-align: center;
        }
        .difference-note {
            background-color: #fff3cd;
            padding: 5px;
            margin-top: 10px;
            font-size: 9px;
            text-align: center;
        }
        .text-danger {
            color: #dc3545;
        }
        .text-warning {
            color: #ffc107;
        }
        .bg-light {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
<div class="container">
    <table class="header-table">
        <tr>
            <td class="header-left">
                <img src="{{ public_path('adminlte/logo4.png') }}" style="width:80px; margin-bottom:10px;" alt="Logo">
            </td>
            <td class="header-center">
                <div class="title">PAYMENT RECEIPT</div>
                <div class="paid-badge">✓ PAYMENT RECEIVED</div>
                <div class="company-name">
                    ASHIYANA VEHICLE SERVICE PVT. LTD.
                </div>
                <div>Jwagal-10 Lalitpur, Nepal</div>
                <div>PAN: 602439925</div>
                <div>Email: account@ashiyana.com.np</div>
            </td>
            <td class="header-right"></td>
        </tr>
    </table>

    <div class="line"></div>

    <table class="info-table">
        <tr>
            <td><b>Customer Name:</b> {{ $customer->name ?? 'Walk-in Customer' }}</td>
            <td class="right"><b>Receipt No.:</b> {{ $receipt->receipt_number }}</td>
        </tr>
        <tr>
            <td><b>PAN / VAT No.:</b> {{ $customer->pan_number ?? 'N/A' }}</td>
            <td class="right"><b>Date:</b> {{ $invoice_date->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td><b>Customer Address:</b> {{ $customer->address ?? 'Kathmandu, Nepal' }}</td>
            <td class="right"><b>Miti:</b> {{ $miti_date }}</td>
        </tr>
        <tr>
            <td><b>Payment Method:</b> {{ ucfirst($receipt->payment_method) }}</td>
            <td class="right"><b>Payment Date:</b> {{ $receipt->payment_date->format('Y-m-d') }}</td>
        </tr>
        @if($receipt->payment_method == 'cheque' && $receipt->cheque_number)
        <tr>
            <td><b>Cheque No.:</b> {{ $receipt->cheque_number }}</td>
            <td class="right"><b>Cheque Date:</b> {{ $receipt->cheque_date ? $receipt->cheque_date->format('Y-m-d') : 'N/A' }}</td>
        </tr>
        @endif
        @if($receipt->payment_method == 'bank' && $receipt->bank_name)
        <tr>
            <td><b>Bank Name:</b> {{ $receipt->bank_name }}</td>
            <td class="right"><b>Account No.:</b> {{ $receipt->bank_account_number ?? 'N/A' }}</td>
        </tr>
        @endif
        @if($receipt->transaction_id)
        <tr>
            <td colspan="2"><b>Transaction ID:</b> {{ $receipt->transaction_id }}</td>
        </tr>
        @endif
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>S.N</th>
                <th>HS CODE</th>
                <th>Particular (Invoice Details)</th>
                <th>Qty</th>
                <th>Rate (रू)</th>
                <th>Amount (रू)</th>
            </tr>
        </thead>
        <tbody>
            @php $counter = 1; @endphp
            @forelse($items as $item)
            <tr>
                <td align="center">{{ $counter++ }}</td>
                <td align="center">{{ $item['hs_code'] }}</td>
                <td>{{ $item['particular'] }}</td>
                <td align="center">{{ $item['qty'] }} {{ $item['qty_type'] }}</td>
                <td align="right">{{ number_format($item['rate'], 2) }}</td>
                <td align="right">{{ number_format($item['amount'], 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" align="center">No items available</td>
            </tr>
            @endforelse
            <tr>
                <td colspan="6" style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 12px;">&nbsp;</td>
            </tr>
            
            <!-- Totals Section -->
            <tr>
                <td colspan="4" rowspan="6" style="padding: 6px 4px; vertical-align: top; border: 1px solid #000;">
                    <div><b>Printing Date & Time:</b> {{ $printing_time }}</div>
                    <div style="margin-top:8px;"><b>In Words:</b> {{ $amount_in_words }}</div>
                    @if($receipt->notes)
                    <div style="margin-top:8px;"><b>Notes:</b> {{ $receipt->notes }}</div>
                    @endif
                </td>
                <td style="border: 1px solid #000; padding: 4px;">Total Invoice Amount</td>
                <td style="border: 1px solid #000; padding: 4px;" align="right">{{ number_format($receipt->total_invoice_amount, 2) }}</td>
            </tr>
            @if($receipt->tds_applied)
            <tr>
                <td style="border: 1px solid #000; padding: 4px; background-color: #fff3cd;">TDS Deduction ({{ $receipt->tds_rate }}%)</td>
                <td style="border: 1px solid #000; padding: 4px;" align="right" class="text-danger">- {{ number_format($receipt->tds_deduction, 2) }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 4px;">Net Payable Amount</td>
                <td style="border: 1px solid #000; padding: 4px;" align="right">{{ number_format($receipt->net_paid_amount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td style="border: 1px solid #000; padding: 4px; background-color: #d4edda; font-weight: bold;">Amount Received</td>
                <td style="border: 1px solid #000; padding: 4px;" align="right" class="bg-success text-white">
                    <b>{{ number_format($receipt->received_amount, 2) }}</b>
                </td>
            </tr>
            @if(abs($receipt->difference_amount) > 0.01)
            <tr>
                <td style="border: 1px solid #000; padding: 4px; background-color: #fff3cd;">
                    @if($receipt->difference_amount > 0)
                        Overpayment Amount
                    @else
                        Short Payment Amount
                    @endif
                </td>
                <td style="border: 1px solid #000; padding: 4px;" align="right" class="text-warning">
                    @if($receipt->difference_amount > 0)
                        + {{ number_format($receipt->difference_amount, 2) }}
                    @else
                        {{ number_format($receipt->difference_amount, 2) }}
                    @endif
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    @if(abs($receipt->difference_amount) > 0.01)
    <div class="difference-note">
        <strong>Note:</strong> {{ $receipt->difference_note }}
    </div>
    @endif

    <table width="100%" class="signature">
        <tr>
            <td style="border: none;">
                <span class="sign-line">Received By</span>
            </td>
            <td style="border: none; text-align: center;">
                <span class="sign-line">Prepared By</span>
            </td>
            <td style="border: none; text-align: right;">
                <span class="sign-line sign-long">For: ASHIYANA VEHICLE SERVICE PVT. LTD.</span>
            </td>
        </tr>
        <tr>
            <td style="border: none; padding-top: 5px; font-size: 9px; color: #444;">(Customer/Receiver signature)</td>
            <td style="border: none; text-align: center; padding-top: 5px; font-size: 9px;">(Authorized staff)</td>
            <td style="border: none; text-align: right; padding-top: 5px; font-size: 9px;">Authorized Signatory</td>
        </tr>
    </table>

    <div class="footer-note">
        ** This is a computer generated payment receipt – valid with authorized signature **
        <br>
        @if($receipt->tds_applied)
        <strong>TDS Certificate will be provided separately as per applicable tax laws.</strong>
        @endif
    </div>
</div>
</body>
</html>