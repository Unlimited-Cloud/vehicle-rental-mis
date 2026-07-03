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
        $query = VehicleBooking::whereNull('deleted_at')->with([
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
            'Start Time',
            'Customer',
            'File No',
            'Trip Route',
            'Rate',
            'Total Amount',
            'Vehicle',
            'Driver',
            'Helper',
            'No. of People',
            'Status',
            'Movement',
            'Customer Phone',
            'Start KM',
            'End KM',
            'Total Consumed (KM)',
            'Fuel (Ltr)',
            'Notes',
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
            // Main columns matching table order
            \Carbon\Carbon::parse($booking->start_date)->format('d-m-Y'),
            \Carbon\Carbon::parse($booking->start_time)->format('h:i A'),
            $booking->customer->name ?? ($booking->passenger_name ?? 'N/A'),
            $booking->file_no ?? 'N/A',
            $booking->tripRoute->title ?? 'N/A',
            $booking->rate_per_day ?? 0,
            $booking->total_amount ?? 0,
            $booking->vehicle->vehicle_name ?? 'N/A',
            $booking->driver->user->name ?? 'Not Assigned',
            $booking->helper->user->name ?? 'Not Assigned',
            $booking->no_of_people ?? 'N/A',
            ucfirst($booking->status),
            $hasMovement ? 'Yes' : 'No',

            // Additional columns after status
            $booking->customer->phone ?? 'N/A',
            $booking->vehicleMoment->start_km ?? 'N/A',
            $booking->vehicleMoment->end_km ?? 'N/A',
            $totalKm ?? 'N/A',
            $booking->approx_fuel_litre ?? 'N/A',
            $booking->notes ?? 'N/A',
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
            'A' => 20,  // Start Date (AD/BS)
            'B' => 15,  // Start Time
            'C' => 25,  // Customer
            'D' => 20,  // File No
            'E' => 35,  // Trip Route
            'F' => 15,  // Rate
            'G' => 20,  // Total Amount
            'H' => 20,  // Vehicle
            'I' => 20,  // Driver
            'J' => 20,  // Helper
            'K' => 15,  // Movement
            'L' => 15,  // Status
            'M' => 15,  // No. of People
            'N' => 20,  // Customer Phone
            'O' => 15,  // Start KM
            'P' => 15,  // End KM
            'Q' => 20,  // Total Consumed (KM)
            'R' => 15,  // Fuel (Ltr)
            'S' => 30,  // Notes
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
                $lastColumn = 'S'; // Now 19 columns (A to S)

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
                // Date column (A) - center
                $sheet->getStyle('A2:A' . $lastRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Time column (B) - center
                $sheet->getStyle('B2:B' . $lastRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Number/Currency columns (F, G, O, P, Q, R)
                foreach (['F', 'G', 'O', 'P', 'Q', 'R'] as $column) {
                    $sheet->getStyle($column . '2:' . $column . $lastRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                // Movement column (K) - center
                $sheet->getStyle('K2:K' . $lastRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Format Rate and Total Amount columns (F, G) as currency
                foreach (['F', 'G'] as $column) {
                    $sheet->getStyle($column . '2:' . $column . $lastRow)
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                }

                // Movement column (K) - color coding
                for ($row = 2; $row <= $lastRow; $row++) {
                    $movementStatus = $sheet->getCell('K' . $row)->getValue();

                    if ($movementStatus === 'Yes') {
                        // Green color for movement done
                        $sheet->getStyle('K' . $row)
                            ->getFont()
                            ->getColor()
                            ->setARGB('FF28A745');
                    } elseif ($movementStatus === 'No') {
                        // Red color for no movement
                        $sheet->getStyle('K' . $row)
                            ->getFont()
                            ->getColor()
                            ->setARGB('FFDC3545');
                    }
                }

                // Status column (L) - color code based on status
                for ($row = 2; $row <= $lastRow; $row++) {
                    $status = $sheet->getCell('L' . $row)->getValue();
                    $color = $this->getStatusColor($status);

                    if ($color) {
                        $sheet->getStyle('L' . $row)
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
