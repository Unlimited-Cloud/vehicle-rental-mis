<?php

namespace App\Exports;

use App\Models\PetrolPumpTransaction;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PetrolPumpTransactionExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths
{
    protected $request;
    protected $rowNumber = 0;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Fetch filtered data
     */
    public function collection()
    {
        $query = PetrolPumpTransaction::with('petrolPump');

        if ($this->request->petrol_pump_id) {
            $query->where('petrol_pump_id', $this->request->petrol_pump_id);
        }

        if ($this->request->transaction_type) {
            $query->where('transaction_type', $this->request->transaction_type);
        }

        if ($this->request->invoice_number) {
            $query->where('invoice_number', 'like', '%' . $this->request->invoice_number . '%');
        }

        if ($this->request->from_date) {
            $query->whereDate('transaction_date', '>=', $this->request->from_date);
        }

        if ($this->request->to_date) {
            $query->whereDate('transaction_date', '<=', $this->request->to_date);
        }

        return $query->orderBy('transaction_date', 'desc')->get();
    }

    /**
     * Excel Headings
     */
    public function headings(): array
    {
        return [
            'S.No',
            'Invoice No',
            'Date',
            'Petrol Pump',
            'Transaction Type',
            'Amount',
            'Balance',
            'Fuel Quantity',
            'Fuel Type',
            'Payment Method',
            'Status',
            'Remarks'
        ];
    }

    /**
     * Map each row
     */
    public function map($transaction): array
    {
        return [
            ++$this->rowNumber,
            $transaction->invoice_number,
            optional($transaction->transaction_date)->format('d-m-Y'),
            $transaction->petrolPump->name ?? '',
            ucfirst($transaction->transaction_type),
            $transaction->amount,
            $transaction->balance,
            $transaction->fuel_quantity,
            $transaction->fuel_type,
            $transaction->payment_method,
            ucfirst($transaction->status),
            $transaction->remarks,
        ];
    }

    /**
     * Style heading row
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Column Widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 18,
            'C' => 15,
            'D' => 25,
            'E' => 18,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 18,
            'K' => 15,
            'L' => 30,
        ];
    }
}
