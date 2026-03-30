{{-- resources/views/invoices/vehicle-invoice.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $receipt->receipt_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            padding: 20px;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .company-details {
            font-size: 10px;
            margin-top: 5px;
        }

        .invoice-title {
            text-align: center;
            margin: 15px 0;
            font-size: 16px;
            font-weight: bold;
        }

        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 10px;
            background: #f9f9f9;
        }

        .customer-info {
            margin-bottom: 15px;
            padding: 10px;
            background: #f9f9f9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            width: 300px;
            margin-left: auto;
            margin-bottom: 15px;
        }

        .totals table {
            width: 100%;
        }

        .totals td {
            border: none;
            padding: 5px;
        }

        .remarks {
            margin-top: 15px;
            padding: 10px;
            background: #f9f9f9;
            font-size: 11px;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            text-align: center;
        }

        .prepared-by {
            margin-top: 20px;
            text-align: right;
            font-size: 11px;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }

        .print-btn {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .print-btn:hover {
            background: #45a049;
        }
    </style>
</head>

<body>
    <button class="print-btn no-print" onclick="window.print()">Print Invoice</button>

    <div class="invoice-container">
        <div class="header">
            <div class="company-name">{{ $invoice_data['company_name'] ?? 'Ashivana Vehicle Service Pvt. Ltd.' }}</div>
            <div class="company-details">
                {{ $invoice_data['company_address'] ?? 'Jwagal-10 Lalitpur, Nepal' }}<br>
                {{ $invoice_data['company_phone'] ?? '602439925' }} |
                {{ $invoice_data['company_email'] ?? 'e-account@ashivana.com.np' }}
            </div>
        </div>

        <div class="invoice-title">TAX INVOICE</div>

        <div class="invoice-info">
            <div>
                <strong>Invoice No.:</strong> {{ $receipt->receipt_number }}<br>
                <strong>Date:</strong> {{ $invoice_date->format('d/m/Y') }}<br>
                <strong>Miti:</strong> {{ $miti_date }}
            </div>
            <div>
                <strong>Bill Type:</strong> Credit
            </div>
        </div>

        <div class="customer-info">
            <strong>Customer Name:</strong> {{ $customer ? $customer->name : 'N/A' }}<br>
            <strong>PAN / VAT No.:</strong> {{ $customer ? $customer->pan_number : '' }}<br>
            <strong>Customer Address:</strong> {{ $customer ? $customer->address : '' }}<br>
            <strong>Tour Details:</strong> {{ $file_no }}<br>
            <strong>Bill Type:</strong> Credit
        </div>

        <table>
            <thead>
                <tr>
                    <th>S.N</th>
                    <th>HS CODE</th>
                    <th>Particular</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $index => $booking)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>Transportation Services</td>
                    <td>
                        @php
                        $routeName = $booking->tripRoute ? $booking->tripRoute->name : 'Transportation Service';
                        $vehicleName = $booking->vehicle ? $booking->vehicle->vehicle_name : 'Vehicle';
                        $date = $booking->start_date ? $booking->start_date->format('jS M') : '';
                        @endphp
                        {{ $routeName }} By {{ $vehicleName }} On {{ $date }}
                    </td>
                    <td>1.0 PAX</td>
                    <td class="text-right">{{ number_format($booking->sub_total, 2) }}</td>
                    <td class="text-right">{{ number_format($booking->sub_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td><strong>Basic Amount:</strong></td>
                    <td class="text-right">{{ number_format($bookings->sum('sub_total'), 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Discount:</strong></td>
                    <td class="text-right">{{ number_format($bookings->sum('discount'), 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Taxable value:</strong></td>
                    <td class="text-right">{{ number_format($bookings->sum('sub_total') - $bookings->sum('discount'), 2) }}</td>
                </tr>
                <tr>
                    <td><strong>VAT 13 %:</strong></td>
                    <td class="text-right">{{ number_format($bookings->sum('tax'), 2) }}</td>
                </tr>
                <tr style="border-top: 2px solid #ddd;">
                    <td><strong>Net Amount:</strong></td>
                    <td class="text-right"><strong>{{ number_format($bookings->sum('sub_total') - $bookings->sum('discount') + $bookings->sum('tax'), 2) }}</strong></td>
                </tr>
            </table>
        </div>

        <div class="remarks">
            <strong>Remarks:</strong> In Words : {{ $invoice_data['amount_in_words'] ?? $receipt->total_amount }}<br>
            <strong>Prepared By:</strong> {{ $invoice_data['prepared_by'] ?? 'BIR' }}<br>
            <strong>For:</strong> {{ $invoice_data['company_name'] ?? 'ASHIVANA VEHICLE SERVICE PVT.LTD.' }}
        </div>

        <div class="footer">
            <strong>Printing Date & Time:</strong> {{ now()->format('m/d/Y h:i:s A') }}
        </div>

        <div class="prepared-by">
            Authorized Signatory
        </div>
    </div>
</body>

</html>