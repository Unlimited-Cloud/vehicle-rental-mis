<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>

body{
    font-family: DejaVu Sans;
    font-size:12px;
}

.container{
    border:1px solid #000;
    padding:12px;
}

.header{
    text-align:center;
}

.header h2{
    margin:0;
}

.invoice-title{
    text-align:center;
    font-size:18px;
    font-weight:bold;
    text-decoration:underline;
    margin-top:5px;
}

.top{
    width:100%;
    margin-top:10px;
}

.top td{
    vertical-align:top;
}

.table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

.table th,
.table td{
    border:1px solid #000;
    padding:6px;
}

.amount-table{
    width:40%;
    border-collapse:collapse;
    float:right;
    margin-top:10px;
}

.amount-table td{
    border:1px solid #000;
    padding:6px;
}

.footer{
    margin-top:70px;
}

.clear{
    clear:both;
}

</style>

</head>

<body>

<div class="container">

<!-- COMPANY HEADER -->

<div class="header">

<h2>Kathmandu Sightseeing Pvt Ltd</h2>

Unlimited Building, Khichapokhari <br>
Opp Pashupati Plaza, Kathmandu, Nepal <br>
Tel: 977-1-5970800

</div>


<div class="invoice-title">

@if($receipt->invoice_type == 'vat')
TAX INVOICE
@else
INVOICE
@endif

</div>


<!-- TOP DETAILS -->

<table class="top">

<tr>

<td width="60%">

<b>Invoice No:</b> {{ $receipt->receipt_number }} <br>

<b>VAT NO:</b> 302598252 <br>

<b>M/S:</b> {{ $customer->name ?? '' }} <br>

<b>Tel:</b> {{ $customer->phone ?? '' }} <br>

<b>Address:</b> {{ $customer->address ?? '_________________' }} <br>

<b>Customer VAT Regd. No:</b> {{ $customer->vat_no ?? '_________________' }} <br>

<b>Mode of Payment:</b> Cash / Cheque / Credit / Others

</td>


<td align="right">

<b>Transaction Date:</b>

{{ \Carbon\Carbon::parse($moment->end_datetime)->format('d M Y') }}

<br><br>

<b>Invoice Issue Date:</b>

{{ \Carbon\Carbon::parse($receipt->created_at)->format('d M Y') }}

</td>

</tr>

</table>


<!-- PARTICULARS TABLE -->

<table class="table">

<tr>

<th width="50">S.N.</th>
<th>Particulars</th>
<th width="120">Rate</th>
<th width="150">Amount</th>

</tr>


<tr>

<td align="center">1</td>

<td>

Vehicle Rental Service <br>

Vehicle: {{ $vehicle->vehicle_name ?? '' }} <br>

Route:
{{ $booking->from_destination }}
→
{{ $booking->to_destination }}

<br>

Rental Period:
{{ \Carbon\Carbon::parse($moment->start_datetime)->format('d M Y H:i') }}
to
{{ \Carbon\Carbon::parse($moment->end_datetime)->format('d M Y H:i') }}

<br>

Total Duration:
{{ $receipt->hours }} Hours ({{ $receipt->days }} Days)

</td>


<td align="right">

{{ number_format($receipt->rate_per_day,2) }}

</td>


<td align="right">

{{ number_format($receipt->sub_total,2) }}

</td>

</tr>

</table>


<!-- WORDS -->

<p style="margin-top:5px;">

<b>In Words NPR:</b>

{{ ucfirst(\App\Helpers\NumberHelper::numberToWords($receipt->total_amount)) }}

Only

</p>


<!-- TOTAL CALCULATION -->

<table class="amount-table">

<tr>
<td>Sub Total</td>
<td align="right">{{ number_format($receipt->sub_total,2) }}</td>
</tr>

<tr>
<td>Discount</td>
<td align="right">{{ number_format($receipt->discount,2) }}</td>
</tr>

<tr>
<td>Taxable Amount</td>
<td align="right">
{{ number_format($receipt->sub_total - $receipt->discount,2) }}
</td>
</tr>

@if($receipt->invoice_type == 'vat')

<tr>
<td>13% VAT</td>
<td align="right">{{ number_format($receipt->tax,2) }}</td>
</tr>

@endif


<tr>

<td><b>Total</b></td>

<td align="right">
<b>NPR {{ number_format($receipt->total_amount,2) }}</b>
</td>

</tr>

</table>


<div class="clear"></div>


<!-- FOOTER -->

<div class="footer">

<p>* E & O.E</p>

<p>
Received By __________________________
</p>


<div style="float:right;text-align:center">

<br><br>

____________________________ <br>
For: Kathmandu Sightseeing Pvt Ltd

</div>


<div class="clear"></div>

</div>


</div>

</body>
</html>