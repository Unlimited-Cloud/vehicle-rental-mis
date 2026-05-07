<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Final Receipt</title>

    <style>
        /* === MASTER STYLES (from invoice format) === */
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

        /* Header table structure */
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
            font-size: 14px;
            margin-top: 5px;
        }

        .paid-badge {
            text-align: center;
            color: green;
            font-weight: bold;
            font-size: 13px;
            margin-top: 2px;
            letter-spacing: 1px;
        }

        .company-name {
            font-weight: bold;
            text-transform: uppercase;
        }

        .line {
            border-top: 1px solid #000;
            margin: 8px 0;
        }

        /* Info table (no borders, clean) */
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

        /* Items table with borders (exact invoice style) */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        table.items th,
        table.items td {
            border: 1px solid #000;
            padding: 5px 4px;
            font-size: 10px;
        }

        table.items th {
            text-align: center;
            background-color: #f5f5f5;
        }

        /* Totals block style (used for amounts summary) */
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .totals-table td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 10px;
        }

        .totals-table td:first-child {
            font-weight: normal;
        }

        .totals-right {
            text-align: right;
        }

        /* signature block */
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

        /* helper clear */
        .clearfix {
            clear: both;
        }

        /* additional spacing */
        .mt-2 {
            margin-top: 5px;
        }
        .amount-in-words {
            margin-top: 8px;
            font-size: 10px;
        }
        .footer-note {
            margin-top: 15px;
        }
    </style>
</head>

<body>
<div class="container">

    <!-- ======================== HEADER (identical to invoice style) ======================== -->
    <table class="header-table">
        <tr>
            <td class="header-left">
                {{-- LOGO PLACEHOLDER – dynamic similar to original --}}
                @php
                    // Dummy helper simulation for demo, but will be replaced with actual logic in live env.
                    // For static rendering we keep structure.
                    $logoPath = public_path('adminlte/logo4.png'); 
                @endphp
                {{-- In real blade, use MenuHelper, but for this template we show logo placeholder --}}
                <img src="{{ public_path('adminlte/logo4.png') }}" style="width:80px; margin-bottom:10px;" alt="Logo">
            </td>
            <td class="header-center">
                <div class="title">FINAL RECEIPT</div>
                <div class="paid-badge">✔ PAID</div>
                <div class="company-name">
                    ASHIYANA VEHICLE SERVICE PVT. LTD. FY-2082-83
                </div>
                <div>Jwagal-10 Lalitpur, Nepal</div>
                <div>602439925</div>
                <div>E-account@ashiyana.com.np</div>
            </td>
            <td class="header-right"></td>
        </tr>
    </table>

    <div class="line"></div>

    <!-- ======================== CUSTOMER + RECEIPT INFO (invoice style info-table) ======================== -->
    <table class="info-table">
        <tr>
            <td><b>Customer Name</b> : {{ $receipt->customer->name ?? 'Walk-in Customer' }}</td>
            <td class="right"><b>Receipt No.</b> : {{ $receipt->receipt_number ?? 'RCP-001' }}</td>
        </tr>
        <tr>
            <td><b>PAN / VAT No.</b> : {{ $receipt->customer->pan_number ?? 'N/A' }}</td>
            <td class="right"><b>Date</b> : {{ $invoice_date ?? date('d-m-Y') }}</td>
        </tr>
        <tr>
            <td><b>Customer Address</b> : {{ $receipt->customer->address ?? 'Kathmandu, Nepal' }}</td>
            <td class="right"><b>Miti</b> : {{ $miti_date ?? '2082-07-15' }}</td>
        </tr>
        <tr>
            <td><b>Tour Details</b> : {{ $receipt->file_no ?? $file_no ?? 'TRIP-1001' }}</td>
            <td class="right"><b>Bill Type</b> : {{ ucfirst($receipt->invoice_type ?? 'credit') }}</td>
        </tr>
        <tr>
            <td colspan="2">
                <b>Mode of Payment :</b> {{ ucfirst($receipt->payment_method ?? 'cash') }}
            </td>
        </tr>

        {{-- Conditional fields based on payment method --}}
        @if(($receipt->payment_method ?? '') == 'cheque')
        <tr>
            <td><b>Cheque No :</b> {{ $receipt->check_no ?? 'N/A' }}</td>
            <td class="right"><b>Cheque Date :</b> {{ $receipt->check_date ?? '----' }}</td>
        </tr>
        @endif

        {{-- @if(($receipt->payment_method ?? '') == 'bank')
        <tr>
            <td><b>Bank Name :</b> {{ $receipt->bank_name ?? 'N/A' }}</td>
            <td class="right"><b>Account No :</b> {{ $receipt->bank_account ?? 'N/A' }}</td>
        </tr>
        @endif --}}
    </table>

    <!-- ======================== ITEMS TABLE (exact invoice style + HS CODE & particular with receipt items) ======================== -->
    <table class="items">
        <thead>
            <tr>
                <th>S.N</th>
                <th>HS CODE</th>
                <th>Particular</th>
                <th>Qty</th>
                <th>Rate</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
           @php $counter = 1; @endphp

            @forelse($items as $item)
            <tr>
                <td align="center">{{ $counter++ }}</td>
                <td align="center">{{ $item['hs_code'] }}</td>

                <td>
                    {{ $item['particular'] }}
                </td>

                <td align="center">
                    {{ $item['qty'] }} {{ $item['qty_type'] ?? 'pax' }}
                </td>

                <td align="right">
                    {{ number_format($item['rate'], 2) }}
                </td>

                <td align="right">
                    {{ number_format($item['amount'], 2) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" align="center">No items available</td>
            </tr>
            @endforelse
            {{-- EMPTY SPACE row for consistent spacing (from original invoice style) --}}
            <tr>
                <td colspan="6" style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 12px;">&nbsp;</td>
            </tr>

            {{-- LEFT SIDE: Printing Date & In Words  |  RIGHT SIDE: Totals Block (exactly as invoice format) --}}
            <tr>
                <!-- LEFT COLUMN (colspan=4) contains printing time and amount in words -->
                <td colspan="4" rowspan="5" style="padding: 6px 4px; vertical-align: top; border: 1px solid #000;">
                    <div><b>Printing Date & Time :</b> {{ $printing_time ?? date('Y-m-d h:i A') }}</div>
                    <div style="margin-top:8px;"><b>In Words :</b> {{ $amount_in_words ?? 'Rupees ' . (new \App\Helpers\NumberHelper())->numberToWords($receipt->total_amount ?? 0) ?? 'Zero Only' }}</div>
                </td>
                <!-- Basic Amount -->
                <td style="border: 1px solid #000; padding: 4px;">Basic Amount</td>
                <td style="border: 1px solid #000; padding: 4px;" align="right">{{ number_format($receipt->sub_total ?? $sub_total ?? 11500, 2) }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 4px;">Discount</td>
                <td style="border: 1px solid #000; padding: 4px;" align="right">{{ number_format($receipt->discount ?? $discount ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 4px;">Taxable Value</td>
                <td style="border: 1px solid #000; padding: 4px;" align="right">{{ number_format(($receipt->sub_total ?? 11500) - ($receipt->discount ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 4px;">VAT {{ $vat_percentage ?? 13 }} %</td>
                <td style="border: 1px solid #000; padding: 4px;" align="right">{{ number_format($receipt->tax ?? $tax ?? 1495, 2) }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 4px;"><b>Net Amount</b></td>
                <td style="border: 1px solid #000; padding: 4px;" align="right"><b>{{ number_format($receipt->total_amount ?? $net_amount ?? 12995, 2) }}</b></td>
            </tr>

            {{-- EXTRA ROW: PAID AMOUNT (distinct, matches receipt requirement) but keep within same table for consistency --}}
            <tr>
                <td colspan="4" style="border: none; padding: 0;"></td>
                <td style="border: 1px solid #000; background: #f9f9f9; font-weight: bold;">Paid Amount</td>
                <td style="border: 1px solid #000; text-align: right; background: #f9f9f9;"><b>{{ number_format($receipt->amount ?? $paid_amount ?? 12995, 2) }}</b></td>
            </tr>
        </tbody>
    </table>

    <div class="clearfix"></div>

    <!-- ======================== SIGNATURE SECTION (exactly as invoice) ======================== -->
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

    {{-- Optional additional footer note --}}
    <div class="footer-note" style="font-size: 9px; text-align: center; margin-top: 20px;">
        ** This is a computer generated receipt – valid with authorized signature **
    </div>
</div>
</body>
</html>

