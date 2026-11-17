<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Reads the "Bookings" and "Itineraries" sheets and keeps the raw rows.
 * The actual saving is done by App\Services\BookingImportService.
 *
 * Row keys are preserved, so Excel row number = key + 2 (row 1 is the heading).
 */
class BookingImport implements WithMultipleSheets, SkipsUnknownSheets
{
    public $bookings;
    public $itineraries;

    public function __construct()
    {
        $this->bookings    = collect();
        $this->itineraries = collect();
    }

    public function sheets(): array
    {
        return [
            'Bookings'    => new BookingRowsSheet($this, 'bookings'),
            'Itineraries' => new BookingRowsSheet($this, 'itineraries'),
        ];
    }

    // "Instructions" (or any other) sheet is ignored
    public function onUnknownSheet($sheetName) {}
}

class BookingRowsSheet implements ToCollection, WithHeadingRow
{
    private $parent;
    private $target;

    public function __construct(BookingImport $parent, string $target)
    {
        $this->parent = $parent;
        $this->target = $target;
    }

    public function collection(Collection $rows)
    {
        // drop completely empty rows, keep original keys for row numbers
        $this->parent->{$this->target} = $rows->filter(function ($row) {
            return $row->filter(function ($v) {
                return $v !== null && trim((string) $v) !== '';
            })->isNotEmpty();
        });
    }
}
