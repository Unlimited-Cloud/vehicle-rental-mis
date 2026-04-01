<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ASHIYANA VEHICLE SERVICE - Reports Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #2c3e50;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        
        .container {
            width: 100%;
            padding: 0 15px;
            margin: 0 auto;
        }
        
        /* Company Header */
        .company-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #d4af37;
            position: relative;
        }
        
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #1a3e60;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        
        .company-tagline {
            font-size: 11px;
            color: #7f8c8d;
            letter-spacing: 1px;
        }
        
        .report-title {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin: 15px 0 5px;
        }
        
        .report-date {
            font-size: 10px;
            color: #95a5a6;
            margin-bottom: 5px;
        }
        
        /* Summary Cards */
        .summary-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            gap: 15px;
        }
        
        .summary-card {
            flex: 1;
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
            border-top: 3px solid;
        }
        
        .summary-card h3 {
            margin: 0 0 5px;
            font-size: 20px;
            font-weight: bold;
        }
        
        .summary-card p {
            margin: 0;
            font-size: 10px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .card-info { border-top-color: #3498db; }
        .card-danger { border-top-color: #e74c3c; }
        .card-success { border-top-color: #27ae60; }
        .card-warning { border-top-color: #f39c12; }
        
        /* Section Styles */
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background: #1a3e60;
            color: white;
            padding: 8px 12px;
            margin: 15px 0 12px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 5px;
            page-break-after: avoid;
        }
        
        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        
        table th {
            background: #ecf0f1;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #bdc3c7;
            font-size: 10px;
        }
        
        table td {
            padding: 6px;
            border: 1px solid #bdc3c7;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-success {
            color: #27ae60;
            font-weight: bold;
        }
        
        .text-danger {
            color: #e74c3c;
            font-weight: bold;
        }
        
        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            border-radius: 10px;
            font-weight: bold;
        }
        
        .badge-info { background: #3498db; color: white; }
        .badge-success { background: #27ae60; color: white; }
        .badge-warning { background: #f39c12; color: #333; }
        .badge-danger { background: #e74c3c; color: white; }
        .badge-primary { background: #1a3e60; color: white; }
        
        /* Progress Bar */
        .progress {
            background: #ecf0f1;
            height: 5px;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 3px;
        }
        
        .progress-bar {
            height: 100%;
            background: #27ae60;
        }
        
        /* Info Box */
        .info-box {
            background: #f8f9fa;
            padding: 10px;
            margin-bottom: 12px;
            border-radius: 6px;
            border-left: 4px solid;
        }
        
        .info-box-primary { border-left-color: #3498db; }
        .info-box-success { border-left-color: #27ae60; }
        .info-box-warning { border-left-color: #f39c12; }
        .info-box-danger { border-left-color: #e74c3c; }
        
        .info-box-label {
            font-weight: bold;
            display: block;
            font-size: 10px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-box-value {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        /* Page Break - FIXED */
        .page-break {
            page-break-before: always;
            margin-top: 20px;
        }
        
        /* Ensure first page doesn't have extra break */
        .first-page {
            page-break-before: avoid;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #95a5a6;
            position: relative;
            bottom: 0;
            width: 100%;
        }
        
        /* Signature Section */
       .signature-table {
            width: 100%;
            margin-top: 50px;
            border: none;
            border-collapse: collapse;

        }

        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
              border: none;
        }

            .signature-line {
                margin-top: 40px;
                font-size: 10px;
            }
        
        /* Watermark */
        .watermark {
            position: fixed;
            bottom: 50px;
            right: 30px;
            opacity: 0.03;
            font-size: 60px;
            font-weight: bold;
            color: #1a3e60;
            transform: rotate(-30deg);
            pointer-events: none;
            z-index: 999;
        }
        
        /* Page Number */
        .page-number {
            text-align: center;
            font-size: 9px;
            color: #95a5a6;
            margin-top: 8px;
        }
        
        @page {
            margin: 20mm 15mm;
            size: A4;
        }
              @page :left {
                margin-left: 10mm;
                margin-right: 10mm;
                }

                @page :right {
                margin-left: 10mm;
                margin-right:10mm;
                }
        
        /* Ensure no extra spacing */
        .no-break {
            page-break-inside: avoid;
        }
  
    </style>
</head>
<body>
    <div class="watermark">ASHIYANA</div>
    
    <!-- Header Section - Page 1 -->
    <div class="company-header">
        <div class="company-name">ASHIYANA VEHICLE SERVICE PVT. LTD</div>
        <div class="company-tagline">Your Trusted Partner in Vehicle Management</div>
        <div class="report-title">MANAGEMENT REPORTS DASHBOARD</div>
        <div class="report-date">
            Generated on: {{ now()->format('F d, Y h:i A') }} | 
            Period: {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}
        </div>
    </div>

    <!-- Executive Summary Cards - Page 1 -->
    <div class="section">
        <div class="summary-grid">
            <div class="summary-card card-info">
                <h3>{{ $summary['formatted_revenue'] ?? '₹ 0' }}</h3>
                <p>Total Revenue</p>
            </div>
            <div class="summary-card card-danger">
                <h3>{{ $summary['formatted_expenses'] ?? '₹ 0' }}</h3>
                <p>Total Expenses</p>
            </div>
            <div class="summary-card card-success">
                <h3>{{ $summary['formatted_profit'] ?? '₹ 0' }}</h3>
                <p>Net Profit</p>
            </div>
            <div class="summary-card card-warning">
                <h3>{{ $summary['total_bookings'] ?? 0 }}</h3>
                <p>Total Bookings</p>
            </div>
        </div>
        
        <div class="summary-grid">
            <div class="summary-card card-danger">
                <h3>{{ $summary['formatted_fuel_cost'] ?? '₹ 0' }}</h3>
                <p>Fuel Cost</p>
            </div>
            <div class="summary-card card-warning">
                <h3>{{ $summary['formatted_maintenance_cost'] ?? '₹ 0' }}</h3>
                <p>Maintenance Cost</p>
            </div>
        </div>
    </div>

    <!-- Page 1: Profitability Report -->
    <div class="section">
        <div class="section-title">
            📊 PROFITABILITY ANALYSIS PER VEHICLE
        </div>
        
         <table>
            <thead>
                 <tr>
                    <th width="20%">Vehicle</th>
                    <th width="10%">Type</th>
                    <th width="15%">Revenue</th>
                    <th width="15%">Fuel Cost</th>
                    <th width="15%">Maintenance</th>
                    <th width="10%">Crew Salary</th>
                    <th width="15%">Total Cost</th>
                    <th width="15%">Net Profit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($profitabilityReport ?? [] as $report)
                <tr>
                    <td><strong>{{ $report['vehicle_name'] ?? 'N/A' }}</strong></td>
                    <td><span class="badge badge-info">{{ ucfirst($report['vehicle_type'] ?? 'N/A') }}</span></td>
                    <td class="text-right">{{ $report['formatted_revenue'] ?? '₹ 0' }}</td>
                    <td class="text-right">{{ $report['formatted_fuel_cost'] ?? '₹ 0' }}</td>
                    <td class="text-right">{{ $report['formatted_maintenance_cost'] ?? '₹ 0' }}</td>
                    <td class="text-right">{{ $report['formatted_crew_salary'] ?? '₹ 0' }}</td>
                    <td class="text-right"><strong>{{ $report['formatted_total_cost'] ?? '₹ 0' }}</strong></td>
                    <td class="text-right {{ $report['profit_class'] ?? '' }}">
                        <strong>{{ $report['formatted_net_profit'] ?? '₹ 0' }}</strong>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">No data available</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        @php
            $totalProfit = 0;
            $profitableVehicles = 0;
            $lossVehicles = 0;
            
            if(isset($profitabilityReport) && is_array($profitabilityReport)) {
                $totalProfit = array_sum(array_column($profitabilityReport, 'net_profit'));
                $profitableVehicles = count(array_filter($profitabilityReport, function($r) { 
                    return isset($r['net_profit']) && $r['net_profit'] > 0; 
                }));
                $lossVehicles = count(array_filter($profitabilityReport, function($r) { 
                    return isset($r['net_profit']) && $r['net_profit'] < 0; 
                }));
            }
        @endphp
        
        <div class="info-box info-box-success">
            <span class="info-box-label">Profitability Summary</span>
            <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                <div><strong>Total Profit:</strong> {{ $totalProfit >= 0 ? '₹ ' . number_format($totalProfit, 2) : '-₹ ' . number_format(abs($totalProfit), 2) }}</div>
                <div><strong>Profitable Vehicles:</strong> {{ $profitableVehicles }}</div>
                <div><strong>Vehicles in Loss:</strong> {{ $lossVehicles }}</div>
            </div>
        </div>
    </div>

    <!-- Page 2: Revenue & Expenses -->
    <div class="page-break"></div>
    
    <div class="section">
        <div class="section-title">
            💰 REVENUE SUMMARY
        </div>
        
        <div class="summary-grid">
            <div class="info-box info-box-primary">
                <span class="info-box-label">Total Revenue</span>
                <span class="info-box-value">{{ $revenueReport['formatted_total_revenue'] ?? '₹ 0' }}</span>
            </div>
            <div class="info-box info-box-success">
                <span class="info-box-label">Total Bookings</span>
                <span class="info-box-value">{{ $revenueReport['total_bookings'] ?? 0 }}</span>
            </div>
        </div>
        
        <table>
            <thead>
                 <tr>
                    <th>Vehicle Type</th>
                    <th class="text-center">Bookings</th>
                    <th class="text-right">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalRevenue = $revenueReport['total_revenue'] ?? 0;
                @endphp
                @forelse(($revenueReport['revenue_by_type'] ?? []) as $type => $data)
                <tr>
                    <td><strong>{{ ucfirst($type) }}</strong></td>
                    <td class="text-center">{{ $data['count'] ?? 0 }}</td>
                    <td class="text-right">₹ {{ number_format($data['total'] ?? 0, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center">No revenue data available</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="section">
        <div class="section-title">
            ⛽ FUEL & MAINTENANCE EXPENSES
        </div>
        
        <div class="summary-grid">
            <div class="info-box info-box-danger">
                <span class="info-box-label">Fuel Cost</span>
                <span class="info-box-value">{{ $fuelMaintenanceReport['fuel']['formatted_cost'] ?? '₹ 0' }}</span>
                <div style="font-size: 9px; margin-top: 5px;">
                    Quantity: {{ number_format($fuelMaintenanceReport['fuel']['total_quantity'] ?? 0, 2) }} L | 
                    Avg Price: ₹ {{ number_format($fuelMaintenanceReport['fuel']['avg_price_per_liter'] ?? 0, 2) }}/L
                </div>
            </div>
            <div class="info-box info-box-warning">
                <span class="info-box-label">Maintenance Cost</span>
                <span class="info-box-value">{{ $fuelMaintenanceReport['maintenance']['formatted_total'] ?? '₹ 0' }}</span>
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
                @php
                    $totalMaintenance = $fuelMaintenanceReport['maintenance']['total'] ?? 0;
                @endphp
                <tr>
                    <td><strong>Service Cost</strong></td>
                    <td class="text-right">{{ $fuelMaintenanceReport['maintenance']['formatted_service_cost'] ?? '₹ 0' }}</td>
                    <td class="text-center">
                        {{ $totalMaintenance > 0 ? round(($fuelMaintenanceReport['maintenance']['service_cost'] ?? 0) / $totalMaintenance * 100, 2) : 0 }}%
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ $totalMaintenance > 0 ? (($fuelMaintenanceReport['maintenance']['service_cost'] ?? 0) / $totalMaintenance) * 100 : 0 }}%"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><strong>Repair Cost</strong></td>
                    <td class="text-right">{{ $fuelMaintenanceReport['maintenance']['formatted_repair_cost'] ?? '₹ 0' }}</td>
                    <td class="text-center">
                        {{ $totalMaintenance > 0 ? round(($fuelMaintenanceReport['maintenance']['repair_cost'] ?? 0) / $totalMaintenance * 100, 2) : 0 }}%
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ $totalMaintenance > 0 ? (($fuelMaintenanceReport['maintenance']['repair_cost'] ?? 0) / $totalMaintenance) * 100 : 0 }}%"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><strong>Tyre Change Cost</strong></td>
                    <td class="text-right">{{ $fuelMaintenanceReport['maintenance']['formatted_tyre_cost'] ?? '₹ 0' }}</td>
                    <td class="text-center">
                        {{ $totalMaintenance > 0 ? round(($fuelMaintenanceReport['maintenance']['tyre_cost'] ?? 0) / $totalMaintenance * 100, 2) : 0 }}%
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ $totalMaintenance > 0 ? (($fuelMaintenanceReport['maintenance']['tyre_cost'] ?? 0) / $totalMaintenance) * 100 : 0 }}%"></div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <div class="info-box info-box-danger">
            <span class="info-box-label">Total Operating Expenses</span>
            <span class="info-box-value">{{ $fuelMaintenanceReport['formatted_total_expenses'] ?? '₹ 0' }}</span>
            <div style="font-size: 9px; margin-top: 5px;">
                Fuel: {{ $fuelMaintenanceReport['fuel']['formatted_cost'] ?? '₹ 0' }} | 
                Maintenance: {{ $fuelMaintenanceReport['maintenance']['formatted_total'] ?? '₹ 0' }}
            </div>
        </div>
    </div>

    <!-- Page 3: Client Usage Report -->
    {{-- <div class="page-break"></div> --}}
    
    <div class="section">
        <div class="section-title">
            👥 CLIENT USAGE REPORT
        </div>
        
        @php
            $totalClients = $clientUsageReport['total_clients'] ?? 0;
            $totalClientSpent = $clientUsageReport['total_spent'] ?? 0;
            $averagePerClient = $totalClients > 0 ? $totalClientSpent / $totalClients : 0;
        @endphp
        
        <div class="summary-grid">
            <div class="info-box info-box-primary">
                <span class="info-box-label">Total Active Clients</span>
                <span class="info-box-value">{{ $totalClients }}</span>
            </div>
            <div class="info-box info-box-success">
                <span class="info-box-label">Total Client Spending</span>
                <span class="info-box-value">{{ $clientUsageReport['formatted_total_spent'] ?? '₹ 0' }}</span>
            </div>
            <div class="info-box info-box-warning">
                <span class="info-box-label">Average per Client</span>
                <span class="info-box-value">₹ {{ number_format($averagePerClient, 2) }}</span>
            </div>
        </div>
        
        @if(!empty($clientUsageReport['clients']) && count($clientUsageReport['clients']) > 0)
        <table>
            <thead>
                 <tr>
                    <th width="18%">Client Name</th>
                    <th width="18%">Email</th>
                    <th width="12%">Phone</th>
                    <th width="8%">Bookings</th>
                    <th width="12%">Total Spent</th>
                    <th width="12%">Average</th>
                    <th width="12%">Vehicle Types</th>
                    <th width="8%">Last Booking</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clientUsageReport['clients'] as $client)
                <tr>
                    <td><strong>{{ $client['client_name'] ?? 'N/A' }}</strong></td>
                    <td>{{ $client['client_email'] ?? 'N/A' }}</td>
                    <td>{{ $client['client_phone'] ?? 'N/A' }}</td>
                    <td class="text-center"><span class="badge badge-info">{{ $client['total_bookings'] ?? 0 }}</span></td>
                    <td class="text-right"><strong>{{ $client['formatted_total_spent'] ?? '₹ 0' }}</strong></td>
                    <td class="text-right">{{ $client['formatted_average'] ?? '₹ 0' }}</td>
                    <td>
                        @if(!empty($client['vehicle_types_used']))
                            @foreach(explode(', ', $client['vehicle_types_used']) as $type)
                                <span class="badge badge-primary">{{ trim($type) }}</span>
                            @endforeach
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="text-center">
                        {{ isset($client['last_booking_date']) && $client['last_booking_date'] 
                            ? \Carbon\Carbon::parse($client['last_booking_date'])->format('d M Y') 
                            : 'N/A' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <p class="text-center" style="padding: 30px;">No client data available for the selected period</p>
        @endif
    </div>

    <!-- Page 4: Discount Analysis -->
    {{-- <div class="page-break"></div> --}}
    
    <div class="section">
        <div class="section-title">
            🏷️ DISCOUNT ANALYSIS - Expected vs Actual Rates
        </div>
        
        @php
            $totalDiscountAmount = $discountAnalysis['total_discount_amount'] ?? 0;
            $totalBookingsWithDiscount = $discountAnalysis['total_bookings_with_discount'] ?? 0;
            $avgDiscountPerBooking = $totalBookingsWithDiscount > 0 ? $totalDiscountAmount / $totalBookingsWithDiscount : 0;
            $bookingsCount = count($discountAnalysis['bookings'] ?? []);
        @endphp
        
        <div class="summary-grid">
            <div class="info-box info-box-warning">
                <span class="info-box-label">Total Discount Given</span>
                <span class="info-box-value">{{ $discountAnalysis['formatted_total_discount'] ?? '₹ 0' }}</span>
            </div>
            <div class="info-box info-box-primary">
                <span class="info-box-label">Bookings with Discount</span>
                <span class="info-box-value">{{ $totalBookingsWithDiscount }}</span>
            </div>
            <div class="info-box info-box-info">
                <span class="info-box-label">Avg Discount per Booking</span>
                <span class="info-box-value">₹ {{ number_format($avgDiscountPerBooking, 2) }}</span>
            </div>
        </div>
        
        @if(!empty($discountAnalysis['bookings']) && count($discountAnalysis['bookings']) > 0)
        <table>
            <thead>
                 <tr>
                    <th width="8%">ID</th>
                    <th width="12%">Vehicle</th>
                    <th width="15%">Customer</th>
                    <th width="10%">Date</th>
                    <th width="20%">Trip Route</th>
                    <th width="10%">Expected</th>
                    <th width="10%">Actual</th>
                    <th width="10%">Discount</th>
                    <th width="5%">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($discountAnalysis['bookings'] as $booking)
                <tr>
                    <td class="text-center">#{{ $booking['booking_id'] ?? 'N/A' }}</td>
                    <td>{{ $booking['vehicle_name'] ?? 'N/A' }}</td>
                    <td>{{ $booking['customer_name'] ?? 'N/A' }}</td>
                    <td class="text-center">{{ isset($booking['start_date']) ? \Carbon\Carbon::parse($booking['start_date'])->format('Y-m-d') : 'N/A' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($booking['trip_route'] ?? 'N/A', 35) }}</td>
                    <td class="text-right">₹ {{ number_format($booking['expected_amount'] ?? 0, 2) }}</td>
                    <td class="text-right">₹ {{ number_format($booking['actual_amount'] ?? 0, 2) }}</td>
                    <td class="text-right text-danger"><strong>-₹ {{ number_format($booking['discount_given'] ?? 0, 2) }}</strong></td>
                    <td class="text-center">
                        <span class="badge badge-warning">{{ $booking['discount_percentage'] ?? 0 }}%</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        @php
            $firstBookingExpected = $discountAnalysis['bookings'][0]['expected_amount'] ?? 1;
            $avgDiscountPercent = ($totalBookingsWithDiscount > 0 && $firstBookingExpected > 0) ? 
                ($totalDiscountAmount / $totalBookingsWithDiscount) / $firstBookingExpected * 100 : 0;
            $maxDiscount = !empty($discountAnalysis['bookings']) ? max(array_column($discountAnalysis['bookings'], 'discount_given')) : 0;
        @endphp
        
        <div class="info-box info-box-warning" style="margin-top: 10px;">
            <span class="info-box-label">Discount Insights</span>
            <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                <div><strong>Average Discount %:</strong> {{ round($avgDiscountPercent, 2) }}%</div>
                <div><strong>Highest Discount:</strong> ₹ {{ number_format($maxDiscount, 2) }}</div>
                <div><strong>Discount Rate:</strong> {{ $totalBookingsWithDiscount }} / {{ $bookingsCount }}</div>
            </div>
        </div>
        @else
            <p class="text-center" style="padding: 30px;">No discount data available for the selected period</p>
        @endif
    </div>

    

    <!-- Signature & Footer Section - Last Page -->
  <table class="signature-table mt-4">
    <tr>
        <td class="signature-box">
            <div class="signature-line">
                _________________________<br>
                Authorized Signatory<br>
                ASHIYANA VEHICLE SERVICE
            </div>
        </td>
        <td class="signature-box">
            <div class="signature-line">
                _________________________<br>
                Management<br>
                ASHIYANA VEHICLE SERVICE
            </div>
        </td>
    </tr>
</table>

    <div class="footer">
        <p>ASHIYANA VEHICLE SERVICE PVT. LTD |  +977-1-1234567 |  info@ashiyanavehicle.com |  Kathmandu, Nepal</p>
        <p>This is a computer-generated report and does not require a physical signature. Generated on {{ now()->format('F d, Y h:i A') }}</p>
        <p>© {{ date('Y') }} ASHIYANA VEHICLE SERVICE PVT. LTD. All Rights Reserved.</p>
    </div>
    
    {{-- <div class="page-number">
        Page {PAGE_NUM} of {PAGE_COUNT}
    </div> --}}
</body>
</html>