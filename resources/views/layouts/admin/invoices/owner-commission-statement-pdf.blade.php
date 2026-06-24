{{-- resources/views/layouts/admin/invoices/owner-commission-statement-pdf.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Owner Payout Statement - {{ $statement->statement_number }}</title>
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

        .totals-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 10px;
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

        .footer-note {
            margin-top: 15px;
            font-size: 9px;
            text-align: center;
        }

        .text-danger {
            color: #dc3545;
        }

        .bg-success-row {
            background-color: #d4edda;
        }

        .bg-warn-row {
            background-color: #fff3cd;
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
                    <div class="title">VEHICLE OWNER PAYOUT STATEMENT</div>
                    <div class="paid-badge">✓ PAYOUT COMPLETED</div>
                    <div class="company-name">ASHIYANA VEHICLE SERVICE PVT. LTD.</div>
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
                <td><b>Owner Name:</b> {{ $owner->name ?? ($owner->bank_account_name ?? 'N/A') }}</td>
                <td class="right"><b>Statement No.:</b> {{ $statement->statement_number }}</td>
            </tr>
            <tr>
                <td><b>Owner ID:</b> {{ $statement->payee_code }}</td>
                <td class="right"><b>Date:</b> {{ $invoice_date->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td><b>Contact:</b> {{ $owner->contact_number ?? 'N/A' }}</td>
                <td class="right"><b>Miti:</b> {{ $miti_date }}</td>
            </tr>
            <tr>
                <td><b>File No.:</b> {{ $booking->file_no ?? 'N/A' }}</td>
                <td class="right"><b>Payment Date:</b> {{ optional($statement->payment_date)->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td><b>Payment Method:</b> {{ ucfirst(str_replace('_', ' ', $statement->payment_method ?? 'N/A')) }}</td>
                <td class="right"><b>Transaction Ref.:</b> {{ $statement->transaction_reference ?? 'N/A' }}</td>
            </tr>
            @if($statement->bank_name)
            <tr>
                <td colspan="2">
                    <b>Bank:</b> {{ $statement->bank_name }}
                    &nbsp;&nbsp;<b>Account No.:</b> {{ $statement->bank_account_number ?? 'N/A' }}
                </td>
            </tr>
            @endif
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th>S.N</th>
                    <th>Booking Ref. (File No.)</th>
                    <th>Vehicle</th>
                    <th>Booking Date</th>
                    <th>Booking Amount (Excl. Tax)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td align="center">1</td>
                    <td align="center">{{ $booking->file_no ?? 'N/A' }}</td>
                    <td>{{ $booking->vehicle->vehicle_name ?? 'N/A' }}</td>
                    <td align="center">{{ optional($booking->created_at)->format('Y-m-d') }}</td>
                    <td align="right">Nrs. {{ number_format($statement->booking_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <table class="totals-table" width="100%">
            <tr>
                <td style="background-color: #f5f5f5;">Booking Amount (Excl. Tax)</td>
                <td align="right">Nrs. {{ number_format($statement->booking_amount, 2) }}</td>
            </tr>
            @if($statement->tax_amount > 0)
            <tr>
                <td>Tax Amount</td>
                <td align="right">Nrs. {{ number_format($statement->tax_amount, 2) }}</td>
            </tr>
            @endif
            @if($statement->agent_commission_amount > 0)
            <tr class="bg-warn-row">
                <td>Agent Commission ({{ $statement->agent_code_ref ?? 'N/A' }})</td>
                <td align="right" class="text-danger">- Nrs. {{ number_format($statement->agent_commission_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="bg-warn-row">
                <td>Platform Commission ({{ number_format($statement->commission_rate, 2) }}%)</td>
                <td align="right" class="text-danger">- Nrs. {{ number_format($statement->commission_amount, 2) }}</td>
            </tr>
            <tr class="bg-success-row">
                <td><b>Net Payout to Owner</b></td>
                <td align="right"><b>Nrs. {{ number_format($statement->net_paid_amount, 2) }}</b></td>
            </tr>
        </table>

        @if($statement->remarks)
        <div style="margin-top:10px; font-size:9px;">
            <b>Remarks:</b> {{ $statement->remarks }}
        </div>
        @endif

        <table width="100%" class="signature">
            <tr>
                <td style="border: none;"><span class="sign-line">Owner Signature</span></td>
                <td style="border: none; text-align: center;"><span class="sign-line">Prepared By</span></td>
                <td style="border: none; text-align: right;"><span class="sign-line sign-long">For: ASHIYANA VEHICLE SERVICE PVT. LTD.</span></td>
            </tr>
            <tr>
                <td style="border: none; padding-top: 5px; font-size: 9px; color: #444;">(Vehicle Owner)</td>
                <td style="border: none; text-align: center; padding-top: 5px; font-size: 9px;">(Authorized staff)</td>
                <td style="border: none; text-align: right; padding-top: 5px; font-size: 9px;">Authorized Signatory</td>
            </tr>
        </table>

        <div class="footer-note">
            ** This is a computer generated payout statement – valid with authorized signature **
            <br>
            Printed: {{ $printing_time }}
        </div>
    </div>
</body>

</html>