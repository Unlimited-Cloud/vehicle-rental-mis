<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reports Dashboard - {{ now()->format('F d, Y') }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
        }
        .header p {
            margin: 5px 0 0;
            color: #7f8c8d;
            font-size: 11px;
        }
        .summary-box {
            margin-bottom: 20px;
        }
        .summary-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 15px;
        }
        .summary-card {
            flex: 1;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid;
            text-align: center;
        }
        .summary-card h3 {
            margin: 0 0 5px;
            font-size: 20px;
            font-weight: bold;
        }
        .summary-card p {
            margin: 0;
            font-size: 11px;
            color: #666;
        }
        .card-info { border-left-color: #17a2b8; }
        .card-danger { border-left-color: #dc3545; }
        .card-success { border-left-color: #28a745; }
        .card-warning { border-left-color: #ffc107; }
        
        .section-title {
            background: #34495e;
            color: white;
            padding: 8px 12px;
            margin: 20px 0 15px;
            font-size: 16px;
            border-radius: 3px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background: #ecf0f1;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #bdc3c7;
        }
        table td {
            padding: 8px;
            border: 1px solid #bdc3c7;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-success {
            color: #28a745;
        }
        .text-danger {
            color: #dc3545;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 10px;
            border-radius: 3px;
        }
        .badge-info { background: #17a2b8; color: white; }
        .badge-success { background: #28a745; color: white; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-primary { background: #007bff; color: white; }
        
        .progress {
            background: #ecf0f1;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            background: #28a745;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #999;
        }
        .page-break {
            page-break-before: always;
        }
        .info-box {
            background: #f8f9fa;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .info-box-label {
            font-weight: bold;
            display: block;
            font-size: 11px;
            color: #666;
        }
        .info-box-value {
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Vehicle Management Reports Dashboard</h1>
        <p>Generated on: {{ now()->format('F d, Y h:i A') }}</p>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}</p>
    </div>

    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="summary-card card-info">
            <h3>{{ $summary['formatted_revenue'] }}</h3>
            <p>Total Revenue</p>
        </div>
        <div class="summary-card card-danger">
            <h3>{{ $summary['formatted_expenses'] }}</h3>
            <p>Total Expenses</p>
        </div>
        <div class="summary-card card-success">
            <h3>{{ $summary['formatted_profit'] }}</h3>
            <p>Net Profit</p>
        </div>
        <div class="summary-card card-warning">
            <h3>{{ $summary['total_bookings'] }}</h3>
            <p>Total Bookings</p>
        </div>
    </div>

    <!-- Profitability Section -->
    <div class="section-title">Profitability per Vehicle</div>
    <table>
        <thead>
            <tr>
                <th>Vehicle</th>
                <th>Type</th>
                <th class="text-right">Revenue</th>
                <th class="text-right">Fuel Cost</th>
                <th class="text-right">Maintenance</th>
                <th class="text-right">Crew Salary</th>
                <th class="text-right">Total Cost</th>
                <th class="text-right">Net Profit</th>
                <th class="text-center">Profit Margin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($profitabilityReport as $report)
                <tr>
                    <td>{{ $report['vehicle_name'] }}</td>
                    <td>{{ ucfirst($report['vehicle_type']) }}</td>
                    <td class="text-right">{{ $report['formatted_revenue'] }}</td>
                    <td class="text-right">{{ $report['formatted_fuel_cost'] }}</td>
                    <td class="text-right">{{ $report['formatted_maintenance_cost'] }}</td>
                    <td class="text-right">{{ $report['formatted_crew_salary'] }}</td>
                    <td class="text-right">{{ $report['formatted_total_cost'] }}</td>
                    <td class="text-right {{ $report['profit_class'] }}">{{ $report['formatted_net_profit'] }}</td>
                    <td class="text-center">
                        {{ $report['profit_margin'] }}%
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ max(0, $report['profit_margin']) }}%"></div>
                        </div>
                    </td>
            </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">No data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Revenue Section -->
    <div class="section-title">Revenue Summary</div>
    <div class="summary-grid">
        <div class="info-box">
            <span class="info-box-label">Total Revenue</span>
            <span class="info-box-value">{{ $revenueReport['formatted_total_revenue'] }}</span>
        </div>
        <div class="info-box">
            <span class="info-box-label">Total Bookings</span>
            <span class="info-box-value">{{ $revenueReport['total_bookings'] }}</span>
        </div>
        {{-- <div class="info-box">
            <span class="info-box-label">Average Booking Value</span>
            <span class="info-box-value">{{ $revenueReport['formatted_average_booking_value'] }}</span>
        </div> --}}
    </div>

    <table>
        <thead>
            <tr>
                <th>Vehicle Type</th>
                <th class="text-center">Bookings</th>
                <th class="text-right">Revenue</th>
                <th class="text-center">Percentage</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalRevenue = $revenueReport['total_revenue'];
            @endphp
            @foreach($revenueReport['revenue_by_type'] as $type => $data)
                <tr>
                    <td>{{ ucfirst($type) }}</td>
                    <td class="text-center">{{ $data['count'] }}</td>
                    <td class="text-right">₹ {{ number_format($data['total'], 2) }}</td>
                    <td class="text-center">{{ $totalRevenue > 0 ? round(($data['total'] / $totalRevenue) * 100, 2) : 0 }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Fuel & Maintenance Section -->
    <div class="section-title">Fuel & Maintenance Expenses</div>
    <div class="summary-grid">
        <div class="info-box">
            <span class="info-box-label">Fuel Cost</span>
            <span class="info-box-value">{{ $fuelMaintenanceReport['fuel']['formatted_cost'] }}</span>
            <span style="font-size: 10px;">Quantity: {{ number_format($fuelMaintenanceReport['fuel']['total_quantity'], 2) }} L</span>
            <span style="font-size: 10px;">Avg Price: ₹ {{ number_format($fuelMaintenanceReport['fuel']['avg_price_per_liter'], 2) }}/L</span>
        </div>
        <div class="info-box">
            <span class="info-box-label">Maintenance Cost</span>
            <span class="info-box-value">{{ $fuelMaintenanceReport['maintenance']['formatted_total'] }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Maintenance Type</th>
                <th class="text-right">Amount</th>
                <th class="text-center">Percentage</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Service Cost</td>
                <td class="text-right">{{ $fuelMaintenanceReport['maintenance']['formatted_service_cost'] }}</td>
                <td class="text-center">
                    {{ $fuelMaintenanceReport['maintenance']['total'] > 0 ? round(($fuelMaintenanceReport['maintenance']['service_cost'] / $fuelMaintenanceReport['maintenance']['total']) * 100, 2) : 0 }}%
                </td>
            </tr>
            <tr>
                <td>Repair Cost</td>
                <td class="text-right">{{ $fuelMaintenanceReport['maintenance']['formatted_repair_cost'] }}</td>
                <td class="text-center">
                    {{ $fuelMaintenanceReport['maintenance']['total'] > 0 ? round(($fuelMaintenanceReport['maintenance']['repair_cost'] / $fuelMaintenanceReport['maintenance']['total']) * 100, 2) : 0 }}%
                </td>
            </tr>
            <tr>
                <td>Tyre Change Cost</td>
                <td class="text-right">{{ $fuelMaintenanceReport['maintenance']['formatted_tyre_cost'] }}</td>
                <td class="text-center">
                    {{ $fuelMaintenanceReport['maintenance']['total'] > 0 ? round(($fuelMaintenanceReport['maintenance']['tyre_cost'] / $fuelMaintenanceReport['maintenance']['total']) * 100, 2) : 0 }}%
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Discount Analysis Section -->
    <div class="section-title">Discount Analysis</div>
    <div class="summary-grid">
        <div class="info-box">
            <span class="info-box-label">Total Discount Given</span>
            <span class="info-box-value">{{ $discountAnalysis['formatted_total_discount'] }}</span>
        </div>
        {{-- <div class="info-box">
            <span class="info-box-label">Bookings with Discount</span>
            <span class="info-box-value">{{ $discountAnalysis['total_bookings_with_discount'] }}</span>
        </div> --}}
    </div>

    @if(count($discountAnalysis['bookings']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Vehicle</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th class="text-right">Expected</th>
                    <th class="text-right">Actual</th>
                    <th class="text-right">Discount</th>
                    <th class="text-center">Discount %</th>
                </tr>
            </thead>
            <tbody>
                @foreach($discountAnalysis['bookings'] as $booking)
                    <tr>
                        <td>#{{ $booking['booking_id'] }}</td>
                        <td>{{ $booking['vehicle_name'] }}</td>
                        <td>{{ $booking['customer_name'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($booking['start_date'])->format('Y-m-d') }}</td>
                        <td class="text-right">₹ {{ number_format($booking['expected_amount'], 2) }}</td>
                        <td class="text-right">₹ {{ number_format($booking['actual_amount'], 2) }}</td>
                        <td class="text-right text-danger">-₹ {{ number_format($booking['discount_given'], 2) }}</td>
                        <td class="text-center">{{ $booking['discount_percentage'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-center">No discount data available</p>
    @endif

    <!-- Client Usage Section -->
    <div class="section-title page-break">Client Usage Report</div>
    <div class="summary-grid">
        <div class="info-box">
            <span class="info-box-label">Total Clients</span>
            <span class="info-box-value">{{ $clientUsageReport['total_clients'] }}</span>
        </div>
        <div class="info-box">
            <span class="info-box-label">Total Spent by Clients</span>
            <span class="info-box-value">{{ $clientUsageReport['formatted_total_spent'] }}</span>
        </div>
        <div class="info-box">
            <span class="info-box-label">Average per Client</span>
            <span class="info-box-value">{{ $clientUsageReport['formatted_average_per_client'] }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th class="text-center">Bookings</th>
                <th class="text-right">Total Spent</th>
                <th class="text-right">Average</th>
                <th>Last Booking</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clientUsageReport['clients'] as $client)
                <tr>
                    <td>{{ $client['client_name'] }}</td>
                    <td>{{ $client['client_email'] }}</td>
                    <td>{{ $client['client_phone'] }}</td>
                    <td class="text-center">{{ $client['total_bookings'] }}</td>
                    <td class="text-right">{{ $client['formatted_total_spent'] }}</td>
                    <td class="text-right">{{ $client['formatted_average'] }}</td>
                    <td>{{ $client['last_booking_date'] ? \Carbon\Carbon::parse($client['last_booking_date'])->format('Y-m-d') : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No client data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>This is a system generated report from Vehicle Management System</p>
        <p>© {{ date('Y') }} All Rights Reserved</p>
    </div>
</body>
</html>