<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>

body{
    font-family: DejaVu Sans;
    font-size:12px;
    color:#333;
}

.header{
    width:100%;
    margin-bottom:20px;
}

.logo{
    float:left;
}

.company{
    float:right;
    text-align:right;
}

.clear{
    clear:both;
}

.title{
    text-align:center;
    font-size:22px;
    font-weight:bold;
    margin-top:10px;
    margin-bottom:10px;
}

.meta{
    width:100%;
    margin-top:10px;
}

.meta td{
    padding:4px;
}

.table{
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}

.table th{
    background:#f3f3f3;
    border:1px solid #ccc;
    padding:8px;
}

.table td{
    border:1px solid #ccc;
    padding:8px;
}

.total-table{
    width:40%;
    float:right;
    margin-top:20px;
}

.total-table td{
    border:1px solid #ccc;
    padding:8px;
}

.footer{
    margin-top:80px;
}

.signature{
    width:40%;
    float:right;
    text-align:center;
}

</style>
</head>

<body>

<!-- HEADER -->

<div class="header">

<div class="logo">
<img src="{{ public_path('uploads/logo.png') }}" height="90" alt="Logo">

</div>

<div class="company">

<strong>Kathmandu Sightseeing Pvt Ltd</strong><br>

Kathmandu, Nepal<br>
</div>

<div class="clear"></div>

</div>

<hr>

<div class="title">
PROFORMA INVOICE
</div>


<!-- INVOICE META -->

<table class="meta">

<tr>

<td>
<strong>Invoice No:</strong> {{ $invoice->invoice_number }}
</td>

<td align="right">
<strong>Date:</strong> {{ now()->format('Y-m-d') }}
</td>

</tr>

<tr>

<td>
<strong>Version:</strong> V{{ $invoice->version }}
</td>

<td align="right">
<strong>Booking ID:</strong> #{{ $invoice->vehicle_booking_id }}
</td>

</tr>

</table>


<!-- CUSTOMER -->

<h4>Customer Details</h4>

<table class="table">

<tr>

<td width="25%"><strong>Name</strong></td>

<td>{{ $invoice->booking->customer->name ?? '' }}</td>

</tr>

<tr>

<td><strong>Phone</strong></td>

<td>{{ $invoice->booking->customer->phone ?? '' }}</td>

</tr>

<tr>

<td><strong>Email</strong></td>

<td>{{ $invoice->booking->customer->email ?? '' }}</td>

</tr>

</table>


<!-- TRIP DETAILS -->

<h4>Trip Details</h4>

<table class="table">

<thead>

<tr>

<th>Vehicle</th>
<th>From</th>
<th>To</th>
<th>Start Date</th>
<th>End Date</th>
<th>Days</th>
<th>Rate / Day</th>
<th>Total</th>

</tr>

</thead>

<tbody>

<tr>

<td>{{ $invoice->vehicle->vehicle_name }}</td>

<td>{{ $invoice->booking->from_destination }}</td>

<td>{{ $invoice->booking->to_destination }}</td>

<td>{{ $invoice->from_date }}</td>

<td>{{ $invoice->to_date }}</td>

<td>{{ $invoice->days }}</td>

<td>{{ number_format($invoice->rate_per_day,2) }}</td>

<td>{{ number_format($invoice->sub_total,2) }}</td>

</tr>

</tbody>

</table>



<!-- TOTALS -->

<table class="total-table">

<tr>

<td>Sub Total</td>

<td align="right">{{ number_format($invoice->sub_total,2) }}</td>

</tr>

{{-- <tr>

<td>Tax</td>

<td align="right">{{ number_format($invoice->tax,2) }}</td>

</tr> --}}

<tr>

<td>Discount</td>

<td align="right">{{ number_format($invoice->discount,2) }}</td>

</tr>

<tr>

<td><strong>Grand Total (Without Tax)</strong></td>

<td align="right"><strong>{{ number_format($invoice->total_amount,2) }}</strong></td>

</tr>

</table>

<div class="clear"></div>


<!-- NOTES -->

<div style="margin-top:40px">

<strong>Notes:</strong>

<p>
This is a Proforma Invoice generated for the vehicle rental service.
Final invoice may vary depending on trip adjustments.
</p>

</div>


<!-- SIGNATURE -->

<div class="footer">

<div class="signature">

<br><br>

________________________<br>

Authorized Signature

</div>

</div>


</body>
</html>