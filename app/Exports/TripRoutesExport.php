<?php

namespace App\Exports;

use App\Models\TripCategory;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TripRoutesExport implements FromArray, WithStyles, WithEvents, ShouldAutoSize
{
    protected $categoryRows = [];
    protected $headerRow = 2;

    public function array(): array
    {
        $data = [];
        $rowIndex = 1;

        // Title row
        $data[] = ['Nepal Tourist Vehicle Association'];
        $rowIndex++;

        // Header row (row 2)
        $data[] = ['S.N.', 'Description of Trips', 'KM', 'Car', 'Hiace/Jeep', 'Coaster', 'Bus'];
        $rowIndex++;

        $categories = TripCategory::with('routes')->get();

        foreach ($categories as $category) {

            // mark category row for styling
            $this->categoryRows[] = $rowIndex;

            // Category row: S.N blank, description = category name
            $data[] = [
                '', // S.N blank
                $category->name,
                '',
                '',
                '',
                '',
                ''
            ];
            $rowIndex++;

            $sn = 1;
            foreach ($category->routes as $route) {
                $data[] = [
                    $sn++,
                    $route->title,
                    $route->km,
                    'Rs ' . number_format($route->car_price),
                    'Rs ' . number_format($route->hiace_price),
                    'Rs ' . number_format($route->coaster_price),
                    'Rs ' . number_format($route->bus_price)
                ];
                $rowIndex++;
            }
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        // Only header row (row 2)
        return [
            $this->headerRow => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F81BD']
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Borders for all data
                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Title styling (row 1)
                $sheet->mergeCells("A1:G1");
                $sheet->getStyle("A1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                // Category row styling (bold, dark gray background, black text)
                foreach ($this->categoryRows as $row) {
                    $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'A6A6A6'], // dark gray
                        ],
                    ]);
                }

                // Freeze header row and title
                $sheet->freezePane('A3'); // freeze top 2 rows
            }
        ];
    }
}
