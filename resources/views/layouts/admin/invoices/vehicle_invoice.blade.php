<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice</title>

    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
        }

        .container {
            width: 100%;
        }

        .header {
            width: 100%;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }

        .header-table {
            width: 100%;
        }

        .header-left {
            width: 20%;
        }

        .header-center {
            width: 60%;
            text-align: center;
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

        .company-name {
            font-weight: bold;
            text-transform: uppercase;
        }

        .line {
            border-top: 1px solid #000;
            margin: 5px 0;
        }

        .info-table {
            width: 100%;
            margin-top: 5px;
        }

        .info-table td {
            padding: 2px;
        }

        .right {
            text-align: right;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        table.items th,
        table.items td {
            border: 1px solid #000;
            padding: 4px;
            font-size: 10px;
        }

        table.items th {
            text-align: center;
        }

        .totals {
            width: 35%;
            float: right;
            margin-top: 10px;
        }

        .totals td {
            padding: 3px;
        }

        .footer {
            margin-top: 20px;
            font-size: 10px;
        }

        .signature {
            margin-top: 40px;
        }

        .totals {
    width: 35%;
    float: right;
    margin-top: 10px;
    border-collapse: collapse;
}

.totals td {
    border: 1px solid #000;
    padding: 4px;
    font-size: 10px;
}

    </style>
</head>

<body>
<div class="container">

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td class="header-left">
                {{-- LOGO --}}
               <img src="{{ public_path('uploads/logo.png') }}" height="90" alt="Logo">
            </td>

            <td class="header-center">
                <div class="title">Invoice</div>
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

    <!-- CUSTOMER + INVOICE INFO -->
    <table class="info-table">
        <tr>
            <td>
                <b>Customer Name</b> : {{ $customer->name ?? '' }}
            </td>

            <td class="right">
                <b>Invoice No.</b> : {{ $receipt_number }}
            </td>
        </tr>

        <tr>
            <td>
                <b>PAN / VAT No.</b> : {{ $customer->pan_number ?? '' }}
            </td>

            <td class="right">
                <b>Date</b> : {{ $invoice_date }}
            </td>
        </tr>

        <tr>
            <td>
                <b>Customer Address</b> : {{ $customer->address ?? '' }}
            </td>

            <td class="right">
                <b>Miti</b> : {{ $miti_date }}
            </td>
        </tr>

        <tr>
            <td>
                <b>Tour Details</b> : {{ $file_no }}
            </td>

            <td class="right">
                <b>Bill Type</b> : Credit
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <b>Mode of Payment :</b> Cash/Cheque/Credit/Other
            </td>
        </tr>
    </table>

    <!-- ITEMS -->
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

@foreach($items as $item)
<tr>
    <td align="center">{{ $item['sn'] }}</td>
    <td>{{ $item['hs_code'] }}</td>
    <td>{{ $item['particular'] }}</td>
    <td align="center">{{ $item['qty'] }} {{ $item['qty_type'] }}</td>
    <td align="right">{{ number_format($item['rate'],2) }}</td>
    <td align="right">{{ number_format($item['amount'],2) }}</td>
</tr>
@endforeach

{{-- EMPTY SPACE --}}
<tr>
    <td colspan="6" style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 14px;">&nbsp;</td>
</tr>


{{-- PRINTING DATE ROW --}}
{{-- TOTALS & PRINTING DATE --}}
<tr>
    <!-- LEFT SIDE: Printing Date & In Words -->
    <td colspan="4" rowspan="5" style="padding: 4px; vertical-align: top;">
        <div><b>Printing Date & Time :</b> {{ $printing_time }}</div>
        <div style="margin-top:5px;"><b>In Words :</b> {{ $amount_in_words }}</div>
    </td>

    <!-- RIGHT SIDE: Basic Amount -->
    <td style="border: 1px solid #000; padding: 4px;">Basic Amount</td>
    <td style="border: 1px solid #000; padding: 4px;" align="right">{{ number_format($sub_total,2) }}</td>
</tr>
<tr>
    <!-- RIGHT SIDE: Discount -->
    <td style="border: 1px solid #000; padding: 4px;">Discount</td>
    <td style="border: 1px solid #000; padding: 4px;" align="right">{{ number_format($discount,2) }}</td>
</tr>
<tr>
    <!-- RIGHT SIDE: Taxable Value -->
    <td style="border: 1px solid #000; padding: 4px;">Taxable Value</td>
    <td style="border: 1px solid #000; padding: 4px;" align="right">{{ number_format($sub_total - $discount,2) }}</td>
</tr>
<tr>
    <!-- RIGHT SIDE: VAT -->
    <td style="border: 1px solid #000; padding: 4px;">Vat {{ $vat_percentage }} %</td>
    <td style="border: 1px solid #000; padding: 4px;" align="right">{{ number_format($tax,2) }}</td>
</tr>
<tr>
    <!-- RIGHT SIDE: Net Amount -->
    <td style="border: 1px solid #000; padding: 4px;"><b>Net Amount</b></td>
    <td style="border: 1px solid #000; padding: 4px;" align="right"><b>{{ number_format($net_amount,2) }}</b></td>
</tr>

</tbody>
    </table>

   

    <div style="clear: both;"></div>


    <!-- SIGNATURE -->
    <table width="100%" class="signature" style="margin-top:50px;">
        <tr>
            <td>
                <span style="border-top: 1px solid #000; display: inline-block; padding-bottom: 2px; min-width: 80px;">Received By</span>
            </td>
            <td align="center">
                <span style="border-top: 1px solid #000; display: inline-block; padding-bottom: 2px; min-width: 80px;">Prepared By</span>
            </td>
            <td align="right">
                <span style="border-top: 1px solid #000; display: inline-block; padding-bottom: 2px; min-width: 150px;">
                    For: ASHIYANA VEHICLE SERVICE PVT. LTD.
                </span>
            </td>
        </tr>
    </table>

</div>
</body>
</html>