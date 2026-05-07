<?php

namespace App\Exports;

use App\Models\VehicleBooking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Http\Request;

class VehicleBookingExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $request;
    protected $rowNumber = 0;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = VehicleBooking::with([
            'vehicle',
            'customer',
            'driver.user',
            'helper.user',
            'tripRoute',
            'vehicleMoment'
        ]);

        // Apply filters if present
        if ($this->request->vehicle_id) {
            $query->where('vehicle_id', $this->request->vehicle_id);
        }

        if ($this->request->customer_id) {
            $query->where('customer_id', $this->request->customer_id);
        }

        if ($this->request->driver_id) {
            $query->where('driver_id', $this->request->driver_id);
        }

        // Date range filters if needed
        if ($this->request->from_date) {
            $query->whereDate('start_date', '>=', $this->request->from_date);
        }

        if ($this->request->to_date) {
            $query->whereDate('end_date', '<=', $this->request->to_date);
        }

        return $query->orderBy('start_date', 'desc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Start Date',
            'File No',
            'No. of People',
            'Customer Name',
            'Customer Phone',
            'Vehicle',
            'Driver',
            'Helper',
            'Route',
            'Total Amount',
            'Start KM',
            'End KM',
            'Total Consumed (KM)',
            'Fuel (Ltr)',
            'Status',
            'Notes',
            'Movement Done'
        ];
    }

    /**
     * @param mixed $booking
     * @return array
     */
    public function map($booking): array
    {
        $this->rowNumber++;

        // Calculate total KM
        $totalKm = null;
        $hasMovement = false;
        if ($booking->vehicleMoment) {
            $hasMovement = true;
            if ($booking->vehicleMoment->start_km && $booking->vehicleMoment->end_km) {
                $totalKm = $booking->vehicleMoment->end_km - $booking->vehicleMoment->start_km;
            }
        }

        return [
            \Carbon\Carbon::parse($booking->start_date)->format('d-m-Y'),
            $booking->file_no ?? 'N/A',
            $booking->no_of_people ?? 'N/A',
            $booking->customer->name ?? 'N/A',
            $booking->customer->phone ?? 'N/A',
            $booking->vehicle->vehicle_name ?? 'N/A',
            $booking->driver->user->name ?? 'Not Assigned',
            $booking->helper->user->name ?? 'Not Assigned',
            $booking->tripRoute->title ?? 'N/A',
            $booking->total_amount ?? 0, // Total Amount after Route
            $booking->vehicleMoment->start_km ?? 'N/A',
            $booking->vehicleMoment->end_km ?? 'N/A',
            $totalKm ?? 'N/A',
            $booking->approx_fuel_litre ?? 'N/A',
            ucfirst($booking->status),
            $booking->notes ?? 'N/A',
            $hasMovement ? '✓' : '✗',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold with background color
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => Color::COLOR_WHITE]],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2C3E50'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20,  // Start Date
            'B' => 35,  // File No
            'C' => 15,  // No. of People
            'D' => 25,  // Customer Name
            'E' => 20,  // Customer Phone
            'F' => 15,  // Vehicle
            'G' => 20,  // Driver
            'H' => 20,  // Helper
            'I' => 35,  // Route
            'J' => 20,  // Total Amount
            'K' => 15,  // Start KM
            'L' => 15,  // End KM
            'M' => 20,  // Total Consumed (KM)
            'N' => 15,  // Fuel (Ltr)
            'O' => 15,  // Status
            'P' => 30,  // Notes
            'Q' => 18,  // Movement Done
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $lastRow = $sheet->getHighestRow();
                $lastColumn = 'Q'; // Now 17 columns (A to Q)

                // Auto-size columns (as fallback)
                foreach (range('A', $lastColumn) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(false);
                }

                // Style the data rows
                $sheet->getStyle('A2:' . $lastColumn . $lastRow)->applyFromArray([
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FFDDDDDD'],
                        ],
                    ],
                ]);

                // Style specific columns
                // Date column (A)
                $sheet->getStyle('A2:A' . $lastRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Number/Currency columns (C, J, K, L, M, N)
                foreach (['C', 'J', 'K', 'L', 'M', 'N'] as $column) {
                    $sheet->getStyle($column . '2:' . $column . $lastRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                // Format Total Amount column (J) as currency
                $sheet->getStyle('J2:J' . $lastRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                // Movement Done column (Q) - center alignment and color coding
                $sheet->getStyle('Q2:Q' . $lastRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Color code the movement status
                for ($row = 2; $row <= $lastRow; $row++) {
                    $movementStatus = $sheet->getCell('Q' . $row)->getValue();

                    if ($movementStatus === '✓') {
                        // Green color for movement done
                        $sheet->getStyle('Q' . $row)
                            ->getFont()
                            ->getColor()
                            ->setARGB('FF28A745');
                    } elseif ($movementStatus === '✗') {
                        // Red color for no movement
                        $sheet->getStyle('Q' . $row)
                            ->getFont()
                            ->getColor()
                            ->setARGB('FFDC3545');
                    }
                }

                // Status column (O) - color code based on status
                for ($row = 2; $row <= $lastRow; $row++) {
                    $status = $sheet->getCell('O' . $row)->getValue();
                    $color = $this->getStatusColor($status);

                    if ($color) {
                        $sheet->getStyle('O' . $row)
                            ->getFont()
                            ->getColor()
                            ->setARGB($color);
                    }
                }

                // Style header row
                $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF2C3E50'],
                    ],
                    'font' => ['color' => ['argb' => 'FFFFFFFF']],
                ]);

                // Add filter to header row
                $sheet->setAutoFilter('A1:' . $lastColumn . '1');

                // Freeze the header row
                $sheet->freezePane('A2');
            },
        ];
    }

    /**
     * Get color for status
     */
    private function getStatusColor($status)
    {
        $colors = [
            'Confirmed' => 'FF28A745',
            'Pending' => 'FFFFC107',
            'Cancelled' => 'FFDC3545',
            'Completed' => 'FF17A2B8',
        ];

        return $colors[$status] ?? null;
    }
}
