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
            'helper.user'
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
            'S.No',
            'Vehicle',
            'Customer Name',
            'Customer Email',
            'Customer Phone',
            'Driver',
            'Helper',
            'From Destination',
            'To Destination',
            'Start Date',
            'End Date',
            'Duration (Days)',
            'Start KM',
            'End KM',
            'Total KM',
            'Fuel (Ltr)',
            'No. of People',
            'Status',
            'Notes',
            'Created At'
        ];
    }

    /**
     * @param mixed $booking
     * @return array
     */
    public function map($booking): array
    {
        $this->rowNumber++;

        // Calculate duration
        $start = \Carbon\Carbon::parse($booking->start_date);
        $end = \Carbon\Carbon::parse($booking->end_date);
        $duration = $start->diffInDays($end) + 1;

        // Calculate total KM
        $totalKm = null;
        if ($booking->start_km && $booking->end_km) {
            $totalKm = $booking->end_km - $booking->start_km;
        }

        return [
            $this->rowNumber,
            $booking->vehicle->vehicle_name ?? 'N/A',
            $booking->customer->name ?? 'N/A',
            $booking->customer->email ?? 'N/A',
            $booking->customer->phone ?? 'N/A',
            $booking->driver->user->name ?? 'Not Assigned',
            $booking->helper->user->name ?? 'Not Assigned',
            $booking->from_destination ?? 'N/A',
            $booking->to_destination ?? 'N/A',
            \Carbon\Carbon::parse($booking->start_date)->format('d-m-Y'),
            \Carbon\Carbon::parse($booking->end_date)->format('d-m-Y'),
            $duration,
            $booking->start_km ?? 'N/A',
            $booking->end_km ?? 'N/A',
            $totalKm ?? 'N/A',
            $booking->approx_fuel_litre ?? 'N/A',
            $booking->no_of_people ?? 'N/A',
            ucfirst($booking->status),
            $booking->notes ?? 'N/A',
            \Carbon\Carbon::parse($booking->created_at)->format('d-m-Y H:i:s'),
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
            'A' => 8,   // S.No
            'B' => 20,  // Vehicle
            'C' => 25,  // Customer Name
            'D' => 30,  // Customer Email
            'E' => 18,  // Customer Phone
            'F' => 20,  // Driver
            'G' => 20,  // Helper
            'H' => 25,  // From Destination
            'I' => 25,  // To Destination
            'J' => 15,  // Start Date
            'K' => 15,  // End Date
            'L' => 15,  // Duration
            'M' => 12,  // Start KM
            'N' => 12,  // End KM
            'O' => 12,  // Total KM
            'P' => 12,  // Fuel
            'Q' => 15,  // No. of People
            'R' => 15,  // Status
            'S' => 30,  // Notes
            'T' => 20,  // Created At
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
                $lastColumn = $sheet->getHighestColumn();

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
                // Date columns (J, K, T)
                foreach (['J', 'K', 'T'] as $column) {
                    $sheet->getStyle($column . '2:' . $column . $lastRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Number columns (L, M, N, O, P, Q)
                foreach (['L', 'M', 'N', 'O', 'P', 'Q'] as $column) {
                    $sheet->getStyle($column . '2:' . $column . $lastRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                // Status column (R) - color code based on status
                for ($row = 2; $row <= $lastRow; $row++) {
                    $status = $sheet->getCell('R' . $row)->getValue();
                    $color = $this->getStatusColor($status);

                    if ($color) {
                        $sheet->getStyle('R' . $row)
                            ->getFont()
                            ->getColor()
                            ->setARGB($color);
                    }
                }

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
            'CONFIRMED' => 'FF28A745',
            'PENDING' => 'FFFFC107',
            'CANCELLED' => 'FFDC3545',
            'COMPLETED' => 'FF17A2B8',
        ];

        return $colors[strtoupper($status)] ?? null;
    }
}
