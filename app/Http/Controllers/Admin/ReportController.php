<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleBooking;
use App\Models\VehicleService;
use App\Models\Vehicle;
use App\Models\VehicleRepair;
use App\Models\VehicleTyreChange;
use App\Models\PetrolPumpTransaction;
use App\Models\TripRoute;
use App\Models\Customer;
use App\Models\CrewProfile;
use App\Models\VehicleMoment;
use App\Models\VehicleReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dateRange = $request->input('date_range', 'this_month');
        $vehicleType = $request->input('vehicle_type', 'all');
        $vehicleId = $request->input('vehicle_id');

        // Parse date range
        [$startDate, $endDate] = $this->parseDateRange($dateRange);

        // Get all vehicles for filter
        $vehicles = Vehicle::where('status', '1')
            ->when($vehicleType != 'all', function ($query) use ($vehicleType) {
                return $query->where('vehicle_type', $vehicleType);
            })
            ->get();

        // Calculate reports
        $profitabilityReport = $this->getProfitabilityPerVehicle($startDate, $endDate, $vehicleId);
        $revenueReport = $this->getRevenueReport($startDate, $endDate, $vehicleId);
        $fuelMaintenanceReport = $this->getFuelAndMaintenanceReport($startDate, $endDate, $vehicleId);
        $discountAnalysis = $this->getDiscountAnalysis($startDate, $endDate);
        $clientUsageReport = $this->getClientUsageReport($startDate, $endDate);
        $fuelAnalytics = $this->getFuelUsageAnalytics($startDate, $endDate, $vehicleId);
        // Summary statistics
        $summary = $this->getSummaryStats($startDate, $endDate, $vehicleId);

        $movementReport = $this->getMovementReport($startDate, $endDate, $vehicleId);
        $receiptReport = $this->getReceiptReport($startDate, $endDate, $vehicleId);
        $bookingsWithoutMovement = $this->getBookingsWithoutMovement($startDate, $endDate, $vehicleId);
        $movementsWithoutReceipt = $this->getMovementsWithoutReceipt($startDate, $endDate, $vehicleId);
        $receiptsWithoutFullPayment = $this->getReceiptsWithoutFullPayment($startDate, $endDate, $vehicleId);

        return view('layouts.admin.reports.index', compact(
            'profitabilityReport',
            'revenueReport',
            'fuelMaintenanceReport',
            'fuelAnalytics',
            'discountAnalysis',
            'clientUsageReport',
            'movementReport',
            'receiptReport',
            'bookingsWithoutMovement',
            'movementsWithoutReceipt',
            'receiptsWithoutFullPayment',
            'summary',
            'vehicles',
            'startDate',
            'endDate',
            'vehicleType',
            'vehicleId',
            'dateRange'
        ));
    }

    public function exportPdf(Request $request)
    {
        try {
            $dateRange = $request->input('date_range', 'this_month');
            $vehicleType = $request->input('vehicle_type', 'all');
            $vehicleId = $request->input('vehicle_id');

            [$startDate, $endDate] = $this->parseDateRange($dateRange);

            // Get all vehicles for filter (needed for the view)
            $vehicles = Vehicle::where('status', '1')
                ->when($vehicleType != 'all', function ($query) use ($vehicleType) {
                    return $query->where('vehicle_type', $vehicleType);
                })
                ->get();


            $profitabilityReport = $this->getProfitabilityPerVehicle($startDate, $endDate, $vehicleId);
            $revenueReport = $this->getRevenueReport($startDate, $endDate, $vehicleId);
            $fuelMaintenanceReport = $this->getFuelAndMaintenanceReport($startDate, $endDate, $vehicleId);
            $discountAnalysis = $this->getDiscountAnalysis($startDate, $endDate);
            $clientUsageReport = $this->getClientUsageReport($startDate, $endDate);
            $summary = $this->getSummaryStats($startDate, $endDate, $vehicleId);


            // Share data with view
            $data = compact(
                'profitabilityReport',
                'revenueReport',
                'fuelMaintenanceReport',
                'discountAnalysis',
                'clientUsageReport',
                'summary',
                'startDate',
                'endDate',
                'vehicles',
                'vehicleType',
                'vehicleId',
                'dateRange'
            );

            // Load view and generate PDF
            $pdf = Pdf::loadView('layouts.admin.reports.pdf', $data);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true
            ]);

            // Download the PDF
            return $pdf->download('report_' . Carbon::now()->format('Ymd_His') . '.pdf');
        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export to Excel (CSV format)
     */
    public function exportExcel(Request $request)
    {
        try {
            $dateRange = $request->input('date_range', 'this_month');
            $vehicleType = $request->input('vehicle_type', 'all');
            $vehicleId = $request->input('vehicle_id');

            [$startDate, $endDate] = $this->parseDateRange($dateRange);

            $profitabilityReport = $this->getProfitabilityPerVehicle($startDate, $endDate, $vehicleId);

            $filename = 'profitability_report_' . Carbon::now()->format('Ymd_His') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            $callback = function () use ($profitabilityReport, $startDate, $endDate) {
                $file = fopen('php://output', 'w');

                // Add UTF-8 BOM for special characters
                fputs($file, "\xEF\xBB\xBF");

                // Add report header info
                fputcsv($file, ['Profitability Report']);
                fputcsv($file, ['Date Range:', Carbon::parse($startDate)->format('Y-m-d'), 'to', Carbon::parse($endDate)->format('Y-m-d')]);
                fputcsv($file, ['Generated On:', Carbon::now()->format('Y-m-d H:i:s')]);
                fputcsv($file, []); // Empty row

                // Add headers
                fputcsv($file, [
                    'Vehicle',
                    'Type',
                    'Total Revenue',
                    'Fuel Cost',
                    'Maintenance Cost',
                    'Crew Salary',
                    'Total Cost',
                    'Net Profit',
                    'Profit Margin (%)'
                ]);

                // Add data rows
                foreach ($profitabilityReport as $report) {
                    fputcsv($file, [
                        $report['vehicle_name'],
                        ucfirst($report['vehicle_type']),
                        $report['total_revenue'],
                        $report['fuel_cost'],
                        $report['maintenance_cost'],
                        $report['crew_salary'],
                        $report['total_cost'],
                        $report['net_profit'],
                        $report['profit_margin']
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            \Log::error('Excel Export Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate Excel file: ' . $e->getMessage());
        }
    }

    /**
     * Export client usage report to CSV
     */
    public function exportClientReport(Request $request)
    {
        try {
            $dateRange = $request->input('date_range', 'this_month');
            [$startDate, $endDate] = $this->parseDateRange($dateRange);

            $clientUsageReport = $this->getClientUsageReport($startDate, $endDate);

            $filename = 'client_usage_report_' . Carbon::now()->format('Ymd_His') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($clientUsageReport) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");

                fputcsv($file, ['Client Usage Report']);
                fputcsv($file, ['Generated On:', Carbon::now()->format('Y-m-d H:i:s')]);
                fputcsv($file, []);

                fputcsv($file, [
                    'Client Name',
                    'Email',
                    'Phone',
                    'Total Bookings',
                    'Total Spent',
                    'Average Booking Value',
                    'Vehicle Types Used',
                    'Last Booking Date'
                ]);

                foreach ($clientUsageReport['clients'] as $client) {
                    fputcsv($file, [
                        $client['client_name'],
                        $client['client_email'],
                        $client['client_phone'],
                        $client['total_bookings'],
                        $client['total_spent'],
                        $client['average_booking_value'],
                        $client['vehicle_types_used'],
                        $client['last_booking_date'] ?? 'N/A'
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            \Log::error('Client Report Export Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate client report: ' . $e->getMessage());
        }
    }


    private function getProfitabilityPerVehicle($startDate, $endDate, $vehicleId = null)
    {
        $vehicles = Vehicle::when($vehicleId, function ($query) use ($vehicleId) {
            return $query->where('id', $vehicleId);
        })->get();

        $report = [];

        foreach ($vehicles as $vehicle) {
            // Total revenue from bookings
            $totalRevenue = VehicleBooking::where('vehicle_id', $vehicle->id)
                ->whereBetween('start_date', [$startDate, $endDate])
                ->where('status', '=', 'confirmed')
                ->sum('total_amount');

            // Fuel cost
            $fuelCost = PetrolPumpTransaction::where('vehicle_id', $vehicle->id)
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->where('transaction_type', 'credit')
                ->sum('amount');

            // Maintenance costs
            $serviceCost = VehicleService::where('vehicle_id', $vehicle->id)
                ->whereBetween('service_date', [$startDate, $endDate])
                ->sum('service_amount');

            $repairCost = VehicleRepair::where('vehicle_id', $vehicle->id)
                ->whereBetween('repair_date', [$startDate, $endDate])
                ->sum('repair_amount');

            $tyreCost = VehicleTyreChange::where('vehicle_id', $vehicle->id)
                ->whereBetween('change_date', [$startDate, $endDate])
                ->sum('amount');

            $maintenanceCost = $serviceCost + $repairCost + $tyreCost;

            // Crew salary (currently 0 as per requirement)
            $crewSalary = 0;

            $totalCost = $fuelCost + $maintenanceCost + $crewSalary;
            $netProfit = $totalRevenue - $totalCost;
            $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

            $report[] = [
                'vehicle_id' => $vehicle->id,
                'vehicle_name' => $vehicle->vehicle_name,
                'vehicle_type' => $vehicle->vehicle_type,
                'total_revenue' => $totalRevenue,
                'formatted_revenue' => '₹ ' . number_format($totalRevenue, 2),
                'fuel_cost' => $fuelCost,
                'formatted_fuel_cost' => '₹ ' . number_format($fuelCost, 2),
                'maintenance_cost' => $maintenanceCost,
                'formatted_maintenance_cost' => '₹ ' . number_format($maintenanceCost, 2),
                'crew_salary' => $crewSalary,
                'formatted_crew_salary' => '₹ ' . number_format($crewSalary, 2),
                'total_cost' => $totalCost,
                'formatted_total_cost' => '₹ ' . number_format($totalCost, 2),
                'net_profit' => $netProfit,
                'formatted_net_profit' => $netProfit >= 0 ? '₹ ' . number_format($netProfit, 2) : '-₹ ' . number_format(abs($netProfit), 2),
                'profit_margin' => round($profitMargin, 2),
                'profit_class' => $netProfit >= 0 ? 'text-success' : 'text-danger'
            ];
        }

        return $report;
    }


    private function getRevenueReport($startDate, $endDate, $vehicleId = null)
    {
        $baseQuery = VehicleBooking::whereBetween('start_date', [$startDate, $endDate]);

        if ($vehicleId) {
            $baseQuery->where('vehicle_id', $vehicleId);
        }

        // Clone query for status counts
        $statusCounts = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Main revenue query (excluding cancelled)
        $query = (clone $baseQuery)
            ->where('status', '=', 'confirmed')
            ->with('vehicle');


        $bookings = $query->get();

        $totalRevenue = $bookings->sum('total_amount');
        $totalBookings = $baseQuery->count();

        // Revenue by vehicle type
        $revenueByType = $bookings->groupBy('vehicle.vehicle_type')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('total_amount')
            ];
        });

        // Daily revenue trend
        $dailyRevenue = $bookings->groupBy(function ($booking) {
            return Carbon::parse($booking->start_date)->format('Y-m-d');
        })->map(function ($group) {
            return $group->sum('total_amount');
        })->sortKeys();

        return [
            'total_revenue' => $totalRevenue,
            'total_bookings' => $totalBookings,
            'revenue_by_type' => $revenueByType,
            'daily_revenue' => $dailyRevenue,
            'confirmed_bookings' => $statusCounts['confirmed'] ?? 0,
            'pending_bookings' => $statusCounts['pending'] ?? 0,
            'cancelled_bookings' => $statusCounts['cancelled'] ?? 0,
            'formatted_total_revenue' => '₹ ' . number_format($totalRevenue, 2),
        ];
    }

    /**
     * Get fuel and maintenance report
     */
    private function getFuelAndMaintenanceReport($startDate, $endDate, $vehicleId = null)
    {
        // Fuel purchases
        $fuelQuery = PetrolPumpTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->where('transaction_type', 'credit');

        if ($vehicleId) {
            $fuelQuery->where('vehicle_id', $vehicleId);
        }

        $fuelPurchases = $fuelQuery->get();
        $totalFuelCost = $fuelPurchases->sum('amount');
        $totalFuelQuantity = $fuelPurchases->sum('fuel_quantity');

        // Service costs
        $serviceQuery = VehicleService::whereBetween('service_date', [$startDate, $endDate]);
        if ($vehicleId) {
            $serviceQuery->where('vehicle_id', $vehicleId);
        }
        $services = $serviceQuery->get();
        $totalServiceCost = $services->sum('service_amount');

        // Repair costs
        $repairQuery = VehicleRepair::whereBetween('repair_date', [$startDate, $endDate]);
        if ($vehicleId) {
            $repairQuery->where('vehicle_id', $vehicleId);
        }
        $repairs = $repairQuery->get();
        $totalRepairCost = $repairs->sum('repair_amount');

        // Tyre costs
        $tyreQuery = VehicleTyreChange::whereBetween('change_date', [$startDate, $endDate]);
        if ($vehicleId) {
            $tyreQuery->where('vehicle_id', $vehicleId);
        }
        $tyreChanges = $tyreQuery->get();
        $totalTyreCost = $tyreChanges->sum('amount');

        $totalMaintenance = $totalServiceCost + $totalRepairCost + $totalTyreCost;
        $totalExpenses = $totalFuelCost + $totalMaintenance;

        return [
            'fuel' => [
                'total_cost' => $totalFuelCost,
                'total_quantity' => $totalFuelQuantity,
                'formatted_cost' => '₹ ' . number_format($totalFuelCost, 2),
                'avg_price_per_liter' => $totalFuelQuantity > 0 ? $totalFuelCost / $totalFuelQuantity : 0,
                'transactions' => $fuelPurchases
            ],
            'maintenance' => [
                'service_cost' => $totalServiceCost,
                'repair_cost' => $totalRepairCost,
                'tyre_cost' => $totalTyreCost,
                'total' => $totalMaintenance,
                'formatted_service_cost' => '₹ ' . number_format($totalServiceCost, 2),
                'formatted_repair_cost' => '₹ ' . number_format($totalRepairCost, 2),
                'formatted_tyre_cost' => '₹ ' . number_format($totalTyreCost, 2),
                'formatted_total' => '₹ ' . number_format($totalMaintenance, 2)
            ],
            'total_expenses' => $totalExpenses,
            'formatted_total_expenses' => '₹ ' . number_format($totalExpenses, 2)
        ];
    }

    /**
     * Get fuel usage analytics by pump and vehicle
     */
    private function getFuelUsageAnalytics($startDate, $endDate, $vehicleId = null)
    {
        // Fuel usage by pump (petrol pump)
        $fuelByPump = PetrolPumpTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->where('transaction_type', 'credit')
            ->when($vehicleId, function ($query) use ($vehicleId) {
                return $query->where('vehicle_id', $vehicleId);
            })
            ->select(
                'petrol_pump_id',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(fuel_quantity) as total_quantity'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->with('petrolPump')
            ->groupBy('petrol_pump_id')
            ->get()
            ->map(function ($item) {
                return [
                    'pump_name' => $item->petrolPump->name ?? 'Unknown Pump',
                    'total_amount' => $item->total_amount,
                    'formatted_amount' => '₹ ' . number_format($item->total_amount, 2),
                    'total_quantity' => $item->total_quantity,
                    'transaction_count' => $item->transaction_count,
                    'avg_price' => $item->total_quantity > 0 ? $item->total_amount / $item->total_quantity : 0
                ];
            });

        // Fuel usage by vehicle
        $fuelByVehicle = PetrolPumpTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->where('transaction_type', 'credit')
            ->when($vehicleId, function ($query) use ($vehicleId) {
                return $query->where('vehicle_id', $vehicleId);
            })
            ->select(
                'vehicle_id',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(fuel_quantity) as total_quantity'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->with('vehicle')
            ->groupBy('vehicle_id')
            ->get()
            ->map(function ($item) {
                return [
                    'vehicle_name' => $item->vehicle->vehicle_name ?? 'Unknown Vehicle',
                    'vehicle_type' => $item->vehicle->vehicle_type ?? 'N/A',
                    'total_amount' => $item->total_amount,
                    'formatted_amount' => '₹ ' . number_format($item->total_amount, 2),
                    'total_quantity' => $item->total_quantity,
                    'transaction_count' => $item->transaction_count,
                    'avg_price' => $item->total_quantity > 0 ? $item->total_amount / $item->total_quantity : 0
                ];
            });

        // Monthly fuel trend
        $monthlyFuelTrend = PetrolPumpTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->where('transaction_type', 'credit')
            ->when($vehicleId, function ($query) use ($vehicleId) {
                return $query->where('vehicle_id', $vehicleId);
            })
            ->select(
                DB::raw("DATE_FORMAT(transaction_date, '%Y-%m') as month"),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(fuel_quantity) as total_quantity')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Fuel by fuel type
        $fuelByType = PetrolPumpTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->where('transaction_type', 'credit')
            ->when($vehicleId, function ($query) use ($vehicleId) {
                return $query->where('vehicle_id', $vehicleId);
            })
            ->select('fuel_type', DB::raw('SUM(amount) as total_amount'), DB::raw('SUM(fuel_quantity) as total_quantity'))
            ->groupBy('fuel_type')
            ->get()
            ->map(function ($item) {
                return [
                    'fuel_type' => ucfirst($item->fuel_type ?? 'Other'),
                    'total_amount' => $item->total_amount,
                    'formatted_amount' => '₹ ' . number_format($item->total_amount, 2),
                    'total_quantity' => $item->total_quantity
                ];
            });

        return [
            'fuel_by_pump' => $fuelByPump,
            'fuel_by_vehicle' => $fuelByVehicle,
            'monthly_fuel_trend' => $monthlyFuelTrend,
            'fuel_by_type' => $fuelByType
        ];
    }

    private function getDiscountAnalysis($startDate, $endDate)
    {
        $bookings = VehicleBooking::whereBetween('start_date', [$startDate, $endDate])
            ->where('status', 'confirmed')
            ->with(['vehicle', 'tripRoute'])
            ->orderBy('start_date', 'desc') // ascending order; use 'desc' for descending
            ->get();

        $analysis = [];
        $totalDiscountAmount = 0;
        $totalBookingsWithDiscount = 0;

        foreach ($bookings as $booking) {
            $expectedAmount = 0;

            // Only compare rate_per_day with tripRoute vehicle price
            if ($booking->tripRoute && $booking->vehicle) {
                $priceField = $booking->vehicle->vehicle_type . '_price';
                if (isset($booking->tripRoute->$priceField)) {
                    $expectedAmount = $booking->tripRoute->$priceField;
                }
            }

            $actualAmount = $booking->rate_per_day;
            $discountGiven = $expectedAmount > 0 ? $expectedAmount - $actualAmount : 0;
            $discountPercentage = $expectedAmount > 0 ? ($discountGiven / $expectedAmount) * 100 : 0;

            if ($discountGiven > 0) {
                $totalDiscountAmount += $discountGiven;
                $totalBookingsWithDiscount++;
            }

            $analysis[] = [
                'booking_id' => $booking->id,
                'vehicle_name' => $booking->vehicle->vehicle_name ?? 'N/A',
                'customer_name' => $booking->customer->name ?? 'N/A',
                'start_date' => $booking->start_date,
                'expected_amount' => $expectedAmount,
                'actual_amount' => $actualAmount,
                'discount_given' => $discountGiven,
                'discount_percentage' => round($discountPercentage, 2),
                'trip_route' => $booking->tripRoute->title ?? 'Custom Trip'
            ];
        }

        $firstExpectedAmount = $analysis[0]['expected_amount'] ?? 0;

        return [
            'bookings' => $analysis,
            'total_discount_amount' => $totalDiscountAmount,
            'total_bookings_with_discount' => $totalBookingsWithDiscount,
            'average_discount_percentage' => ($totalBookingsWithDiscount > 0 && $firstExpectedAmount > 0)
                ? (($totalDiscountAmount / $totalBookingsWithDiscount) / $firstExpectedAmount) * 100
                : 0,
            'formatted_total_discount' => '₹ ' . number_format($totalDiscountAmount, 2),
        ];
    }
    /**
     * Get client usage report
     */
    private function getClientUsageReport($startDate, $endDate)
    {
        $clients = Customer::where('status', 'active')
            ->with(['bookings' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->where('status', '=', 'confirmed');
            }])
            ->get();

        $clientReport = [];

        foreach ($clients as $client) {
            $bookings = $client->bookings;
            $totalBookings = $bookings->count();
            $totalSpent = $bookings->sum('total_amount');
            $averageBookingValue = $totalBookings > 0 ? $totalSpent / $totalBookings : 0;

            // Get vehicle types used
            $vehicleTypes = $bookings->pluck('vehicle.vehicle_type')->unique()->filter()->values();

            $clientReport[] = [
                'client_id' => $client->id,
                'client_name' => $client->full_name,
                'client_email' => $client->email,
                'client_phone' => $client->phone,
                'total_bookings' => $totalBookings,
                'total_spent' => $totalSpent,
                'formatted_total_spent' => '₹ ' . number_format($totalSpent, 2),
                'average_booking_value' => $averageBookingValue,
                'formatted_average' => '₹ ' . number_format($averageBookingValue, 2),
                'vehicle_types_used' => $vehicleTypes->implode(', '),
                'last_booking_date' => $bookings->max('start_date')
            ];
        }

        // Sort by total spent descending
        usort($clientReport, function ($a, $b) {
            return $b['total_spent'] <=> $a['total_spent'];
        });

        $totalClients = count($clientReport);
        $totalClientSpent = array_sum(array_column($clientReport, 'total_spent'));
        $totalClientBookings = array_sum(array_column($clientReport, 'total_bookings'));

        return [
            'clients' => $clientReport,
            'total_clients' => $totalClients,
            'total_spent' => $totalClientSpent,
            'total_bookings' => $totalClientBookings,
            'average_per_client' => $totalClients > 0 ? $totalClientSpent / $totalClients : 0,
            'formatted_total_spent' => '₹ ' . number_format($totalClientSpent, 2),
            'formatted_average_per_client' => '₹ ' . number_format($totalClients > 0 ? $totalClientSpent / $totalClients : 0, 2)
        ];
    }

    /**
     * Get summary statistics
     */
    private function getSummaryStats($startDate, $endDate, $vehicleId = null)
    {
        $baseQuery = VehicleBooking::whereBetween('start_date', [$startDate, $endDate]);

        if ($vehicleId) {
            $baseQuery->where('vehicle_id', $vehicleId);
        }

        // Clone query for status counts
        $statusCounts = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Main revenue query 
        $query = (clone $baseQuery)
            ->where('status', '=', 'confirmed')
            ->with('vehicle');


        $bookings = $query->get();

        $totalRevenue = $bookings->sum('total_amount');
        $totalBookings = $bookings->count();

        // Fuel cost
        $fuelQuery = PetrolPumpTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->where('transaction_type', 'credit');
        if ($vehicleId) {
            $fuelQuery->where('vehicle_id', $vehicleId);
        }
        $fuelCost = $fuelQuery->sum('amount');

        // Maintenance cost
        $serviceCost = VehicleService::when($vehicleId, function ($q) use ($vehicleId) {
            return $q->where('vehicle_id', $vehicleId);
        })->whereBetween('service_date', [$startDate, $endDate])->sum('service_amount');

        $repairCost = VehicleRepair::when($vehicleId, function ($q) use ($vehicleId) {
            return $q->where('vehicle_id', $vehicleId);
        })->whereBetween('repair_date', [$startDate, $endDate])->sum('repair_amount');

        $tyreCost = VehicleTyreChange::when($vehicleId, function ($q) use ($vehicleId) {
            return $q->where('vehicle_id', $vehicleId);
        })->whereBetween('change_date', [$startDate, $endDate])->sum('amount');

        $maintenanceCost = $serviceCost + $repairCost + $tyreCost;
        $totalExpenses = $fuelCost + $maintenanceCost;
        $netProfit = $totalRevenue - $totalExpenses;

        return [
            'total_revenue' => $totalRevenue,
            'formatted_revenue' => '₹ ' . number_format($totalRevenue, 2),
            'total_bookings' => $totalBookings,
            'total_fuel_cost' => $fuelCost,
            'formatted_fuel_cost' => '₹ ' . number_format($fuelCost, 2),
            'total_maintenance_cost' => $maintenanceCost,
            'formatted_maintenance_cost' => '₹ ' . number_format($maintenanceCost, 2),
            'total_expenses' => $totalExpenses,
            'formatted_expenses' => '₹ ' . number_format($totalExpenses, 2),
            'net_profit' => $netProfit,
            'confirmed_bookings' => $statusCounts['confirmed'] ?? 0,
            'pending_bookings' => $statusCounts['pending'] ?? 0,
            'cancelled_bookings' => $statusCounts['cancelled'] ?? 0,
            'formatted_profit' => $netProfit >= 0 ? '₹ ' . number_format($netProfit, 2) : '-₹ ' . number_format(abs($netProfit), 2),
            'profit_margin' => $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 2) : 0,
            'avg_booking_value' => $totalBookings > 0 ? $totalRevenue / $totalBookings : 0,
            'formatted_avg_booking' => '₹ ' . number_format($totalBookings > 0 ? $totalRevenue / $totalBookings : 0, 2)
        ];
    }

    /**
     * Parse date range from request
     */
    private function parseDateRange($range)
    {
        switch ($range) {
            case 'today':
                $start = Carbon::today();
                $end = Carbon::today();
                break;
            case 'yesterday':
                $start = Carbon::yesterday();
                $end = Carbon::yesterday();
                break;
            case 'this_week':
                $start = Carbon::now()->startOfWeek();
                $end = Carbon::now()->endOfWeek();
                break;
            case 'last_week':
                $start = Carbon::now()->subWeek()->startOfWeek();
                $end = Carbon::now()->subWeek()->endOfWeek();
                break;
            case 'this_month':
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
                break;
            case 'last_month':
                $start = Carbon::now()->subMonth()->startOfMonth();
                $end = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'this_quarter':
                $start = Carbon::now()->startOfQuarter();
                $end = Carbon::now()->endOfQuarter();
                break;
            case 'this_year':
                $start = Carbon::now()->startOfYear();
                $end = Carbon::now()->endOfYear();
                break;
            case 'last_year':
                $start = Carbon::now()->subYear()->startOfYear();
                $end = Carbon::now()->subYear()->endOfYear();
                break;
            default:
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
        }

        return [$start, $end];
    }




    //vehicle moments report
    /**
     * Get MOVEMENT statistics
     */
    private function getMovementReport($startDate, $endDate, $vehicleId = null)
    {
        $query = VehicleMoment::whereBetween('created_at', [$startDate, $endDate])
            ->with('booking'); // eager load bookings



        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $movements = $query->get();

        // Sum total_amount from related bookings
        $totalAmount = $movements->sum(function ($movement) {
            return $movement->booking ? $movement->booking->total_amount : 0;
        });

        $totalMovements = $movements->count();



        return [
            'total_movements' => $totalMovements,
            'total_amount' => $totalAmount,
            'formatted_amount' => '₹ ' . number_format($totalAmount, 2),
            'movements' => $movements
        ];
    }

    /**
     * Get RECEIPT statistics
     */
    private function getReceiptReport($startDate, $endDate, $vehicleId = null)
    {
        $query = VehicleReceipt::whereBetween('created_at', [$startDate, $endDate]);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $receipts = $query->get();

        $totalReceipts = $receipts->count();
        $totalBookingAmount = $receipts->sum('total_amount'); // Amount from booking
        $totalPaidAmount = $receipts->sum('amount'); // Amount paid by users
        $totalPendingAmount = $totalBookingAmount - $totalPaidAmount;

        // Receipts by payment method
        $receiptsByPaymentMethod = $receipts->groupBy('payment_method')->map(function ($group) {
            return [
                'count' => $group->count(),
                'paid_amount' => $group->sum('amount'),
                'booking_amount' => $group->sum('total_amount')
            ];
        });

        return [
            'total_receipts' => $totalReceipts,
            'total_booking_amount' => $totalBookingAmount,
            'formatted_booking_amount' => '₹ ' . number_format($totalBookingAmount, 2),
            'total_paid_amount' => $totalPaidAmount,
            'formatted_paid_amount' => '₹ ' . number_format($totalPaidAmount, 2),
            'total_pending_amount' => $totalPendingAmount,
            'formatted_pending_amount' => '₹ ' . number_format($totalPendingAmount, 2),
            'receipts_by_payment_method' => $receiptsByPaymentMethod,
            'receipts' => $receipts
        ];
    }

    /**
     * Get bookings without MOVEMENT
     */
    private function getBookingsWithoutMovement($startDate, $endDate, $vehicleId = null)
    {
        $query = VehicleBooking::whereBetween('start_date', [$startDate, $endDate])
            ->where('status', 'confirmed')
            ->whereDoesntHave('vehicleMoment');

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $bookings = $query->with(['vehicle', 'customer'])->get();

        // Calculate total_amount once
        $totalAmount = $bookings->sum('total_amount');

        return [
            'count' => $bookings->count(),
            'total_amount' => $totalAmount,
            'formatted_amount' => '₹ ' . number_format($totalAmount, 2),
            'bookings' => $bookings
        ];
    }

    /**
     * Get MOVEMENTS without INVOICE/RECEIPT
     */
    private function getMovementsWithoutReceipt($startDate, $endDate, $vehicleId = null)
    {
        $query = VehicleMoment::with(['vehicle', 'booking'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('booking')
            ->whereDoesntHave('booking.receipts'); // booking has no receipts

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $movements = $query->get();

        // Sum total_amount from related bookings
        $totalAmount = $movements->sum(function ($movement) {
            return $movement->booking ? $movement->booking->total_amount : 0;
        });



        return [
            'count' => $movements->count(),
            'total_amount' => $totalAmount,
            'formatted_amount' => '₹ ' . number_format($totalAmount, 2),
            'movements' => $movements
        ];
    }
    /**
     * Get RECEIPTS/INVOICES without PAYMENT (partial or no payment)
     */
    private function getReceiptsWithoutFullPayment($startDate, $endDate, $vehicleId = null)
    {
        $query = VehicleReceipt::whereBetween('created_at', [$startDate, $endDate])
            ->whereRaw('amount < total_amount'); // Not fully paid

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $receipts = $query->with(['vehicle', 'booking', 'customer'])->get();

        $totalPendingAmount = $receipts->sum(function ($receipt) {
            return $receipt->total_amount - $receipt->amount;
        });

        return [
            'count' => $receipts->count(),
            'total_booking_amount' => $receipts->sum('total_amount'),
            'total_paid_amount' => $receipts->sum('amount'),
            'total_pending_amount' => $totalPendingAmount,
            'formatted_booking_amount' => '₹ ' . number_format($receipts->sum('total_amount'), 2),
            'formatted_paid_amount' => '₹ ' . number_format($receipts->sum('amount'), 2),
            'formatted_pending_amount' => '₹ ' . number_format($totalPendingAmount, 2),
            'receipts' => $receipts
        ];
    }
}
