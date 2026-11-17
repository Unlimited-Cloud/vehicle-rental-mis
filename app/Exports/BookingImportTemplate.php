<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Downloadable import template.
 * Sample rows have a ref starting with "EXAMPLE" - the importer ignores them,
 * so they can be left in or deleted.
 */
class BookingImportTemplate implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new BookingTemplateSheet(),
            new ItineraryTemplateSheet(),
            new InstructionsTemplateSheet(),
        ];
    }
}

class BookingTemplateSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function title(): string
    {
        return 'Bookings';
    }

    public function headings(): array
    {
        return [
            'ref',
            'customer',
            'passenger',
            'file_no',
            'vehicle',
            'driver',
            'helper',
            'signage_information',
            'from_destination',
            'to_destination',
            'no_of_people',
            'start_date',
            'start_time',
            'end_date',
            'end_time',
            'status',
            'pricetype',
            'amount',
            'discount_type',
            'discount',
            'vat',
            'paid_amount',
            'payment_method',
            'payment_status',
            'payment_date',
            'payment_note',
            'notes',
        ];
    }

    public function array(): array
    {
        return [
            // dynamic: amount comes from the Itineraries sheet
            [
                'EXAMPLE-1',
                'Customer Name',
                'Mr. John Smith',
                'F-1001',
                'Vehicle Name',
                'Driver Name',
                '',
                'Welcome John',
                'Tribhuvan Airport',
                'Pokhara',
                4,
                '2026-12-01',
                '09:00',
                '2026-12-03',
                '18:00',
                'confirmed',
                'dynamic',
                '',
                'amount',
                0,
                'No',
                5000,
                'cash',
                '',
                '2026-11-20',
                'Advance',
                ''
            ],
            // static: amount typed here, no itinerary rows needed
            [
                'EXAMPLE-2',
                'Customer Name',
                'Ms. Jane Doe',
                'F-1002',
                'Vehicle Name',
                '',
                '',
                '',
                'Thamel',
                'Nagarkot',
                2,
                '2026-12-05',
                '07:00',
                '2026-12-05',
                '',
                'pending',
                'static',
                12000,
                'percentage',
                10,
                'Yes',
                0,
                '',
                '',
                '',
                '',
                'Sunrise trip'
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

class ItineraryTemplateSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function title(): string
    {
        return 'Itineraries';
    }

    public function headings(): array
    {
        return ['ref', 'itinerary_date', 'from_destination', 'to_destination', 'est_km', 'est_hours', 'overnight', 'notes'];
    }

    public function array(): array
    {
        return [
            ['EXAMPLE-1', '2026-12-01', 'Kathmandu', 'Pokhara', 200, 7, 'Yes', 'Day 1'],
            ['EXAMPLE-1', '2026-12-02', 'Pokhara', 'Sarangkot', 30, 3, 'Yes', 'Day 2'],
            ['EXAMPLE-1', '2026-12-03', 'Pokhara', 'Kathmandu', 200, 7, 'No', 'Day 3'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

class InstructionsTemplateSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function title(): string
    {
        return 'Instructions';
    }

    public function headings(): array
    {
        return ['Sheet', 'Column', 'Required', 'Format / allowed values', 'Notes'];
    }

    public function array(): array
    {
        return [
            ['Bookings', 'ref', 'Yes', 'Any unique text, e.g. B1, B2', 'Links a booking to its rows on the Itineraries sheet. Must be unique. Refs starting with EXAMPLE are ignored.'],
            ['Bookings', 'customer', 'Yes*', 'Name, email or phone of an existing customer', '*Not needed for customer-role users. Customer must already exist.'],
            ['Bookings', 'passenger', 'No', 'Text', ''],
            ['Bookings', 'file_no', 'No', 'Text', 'If given, a booking with the same file_no + vehicle + customer + start_date is skipped (prevents double import).'],
            ['Bookings', 'vehicle', 'Yes', 'Vehicle name exactly as in the system', 'Vehicle must already exist.'],
            ['Bookings', 'driver', 'No', 'Driver name exactly as in the system', 'Must already exist.'],
            ['Bookings', 'helper', 'No', 'Helper name exactly as in the system', 'Must already exist.'],
            ['Bookings', 'signage_information', 'No', 'Text', ''],
            ['Bookings', 'from_destination', 'No', 'Text', 'Pickup'],
            ['Bookings', 'to_destination', 'No', 'Text', 'Dropout'],
            ['Bookings', 'no_of_people', 'No', 'Whole number', ''],
            ['Bookings', 'start_date', 'Yes', 'YYYY-MM-DD (or an Excel date)', ''],
            ['Bookings', 'start_time', 'Yes', 'HH:MM, 24-hour (or an Excel time)', ''],
            ['Bookings', 'end_date', 'Yes', 'YYYY-MM-DD (or an Excel date)', 'Must not be before start_date.'],
            ['Bookings', 'end_time', 'No', 'HH:MM, 24-hour', 'Blank = end of day (23:59), same as the multiple-booking form.'],
            ['Bookings', 'status', 'No', 'confirmed / pending / cancelled', 'Blank = pending.'],
            ['Bookings', 'pricetype', 'No', 'dynamic / static', 'Blank = dynamic. Dynamic: amount = sum of itinerary prices (calculated from the vehicle type rates). Static: amount is taken from the amount column.'],
            ['Bookings', 'amount', 'Static: Yes', 'Number', 'Used for static. For dynamic it is only a fallback when there are no itinerary rows.'],
            ['Bookings', 'discount_type', 'No', 'amount / percentage', 'Blank = amount.'],
            ['Bookings', 'discount', 'No', 'Number', ''],
            ['Bookings', 'vat', 'No', 'Yes / No', 'Yes = 13% VAT after discount.'],
            ['Bookings', 'paid_amount', 'No', 'Number', 'If more than 0 a payment record is created.'],
            ['Bookings', 'payment_method', 'No', 'cash / bank_transfer / card / online / cheque', 'Blank = cash when a payment is created.'],
            ['Bookings', 'payment_status', 'No', 'pending / paid / partial', 'Blank = worked out from paid_amount and total.'],
            ['Bookings', 'payment_date', 'No', 'YYYY-MM-DD', 'Blank = today.'],
            ['Bookings', 'payment_note', 'No', 'Text', ''],
            ['Bookings', 'notes', 'No', 'Text', ''],
            ['Itineraries', 'ref', 'Yes', 'Same ref as on the Bookings sheet', 'One row per day. Rows are numbered Day 1, 2, 3 in sheet order.'],
            ['Itineraries', 'itinerary_date', 'No', 'YYYY-MM-DD', 'Blank = booking start_date.'],
            ['Itineraries', 'from_destination', 'No', 'Text', ''],
            ['Itineraries', 'to_destination', 'No', 'Text', ''],
            ['Itineraries', 'est_km', 'No', 'Number', ''],
            ['Itineraries', 'est_hours', 'No', 'Number', ''],
            ['Itineraries', 'overnight', 'No', 'Yes / No', ''],
            ['Itineraries', 'notes', 'No', 'Text', 'Est. price is calculated automatically; do not add a price column.'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
