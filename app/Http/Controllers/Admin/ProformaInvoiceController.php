<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProformaInvoice;
use App\Models\VehicleMoment;
use App\Models\VehicleReceipt;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;

class ProformaInvoiceController extends Controller
{

    public function index()
    {
        Gate::authorize('index_bills_proforma_invoice');
        $invoices = ProformaInvoice::with(['vehicle', 'booking'])
            ->latest()
            ->get();

        return view('layouts.admin.invoices.index', compact('invoices'));
    }

    public function indexReceipt()
    {
        Gate::authorize('index_bills_receipt');
        $receipts = VehicleReceipt::with(['vehicle', 'customer', 'booking'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('layouts.admin.invoices.index-receipt', compact('receipts'));
    }

    public function download($id)
    {
        $invoice = ProformaInvoice::with([
            'vehicle',
            'booking.customer'
        ])->findOrFail($id);

        $pdf = Pdf::loadView(
            'layouts.admin.invoices.proforma_pdf',
            compact('invoice')
        );

        return $pdf->download(
            $invoice->invoice_number . '.pdf'
        );
    }

    public function downloadInvoice($id)
    {
        $receipt = VehicleReceipt::findOrFail($id);

        if (!$receipt->pdf_path) {
            return redirect()->back()->with('error', 'PDF file not found in database.');
        }

        $filePath = public_path($receipt->pdf_path);

        if (!File::exists($filePath)) {
            return redirect()->back()->with('error', 'PDF file not found on server.');
        }

        // Return file download
        return response()->download($filePath, $receipt->receipt_number . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }



    public function generateInvoice($momentId, $type)
    {

        $moment = VehicleMoment::with(['booking', 'vehicle'])
            ->findOrFail($momentId);

        $booking = $moment->booking;

        $start = \Carbon\Carbon::parse($moment->start_datetime);
        $end   = \Carbon\Carbon::parse($moment->end_datetime);

        $hours = $start->diffInHours($end);
        $days  = ceil($hours / 24);

        $rate = $booking->rate_per_day;

        $subTotal = $rate * $days;


        /* DISCOUNT */

        if ($booking->discount_amount_type == 'percentage') {
            $discount = ($subTotal * $booking->discount) / 100;
        } else {
            $discount = $booking->discount;
        }


        /* VAT */

        $tax = 0;

        if ($type == 'vat') {
            $tax = ($subTotal - $discount) * 0.13;
        }

        $total = $subTotal - $discount + $tax;


        $receiptNumber =
            "INV-" . date('Y') . "-" . str_pad(VehicleReceipt::count() + 1, 5, '0', STR_PAD_LEFT);


        $receipt = VehicleReceipt::create([

            'vehicle_booking_id' => $booking->id,
            'vehicle_moment_id' => $moment->id,
            'vehicle_id' => $moment->vehicle_no,
            'customer_id' => $booking->customer_id,
            'receipt_number' => $receiptNumber,
            'invoice_type' => $type,
            'start_datetime' => $moment->start_datetime,
            'end_datetime' => $moment->end_datetime,
            'hours' => $hours,
            'days' => $days,
            'rate_per_day' => $rate,
            'sub_total' => $subTotal,
            'discount' => $discount,
            'tax' => $tax,
            'total_amount' => $total

        ]);


        $pdf = Pdf::loadView('layouts.admin.invoices.receipt', [
            'receipt' => $receipt,
            'booking' => $booking,
            'moment' => $moment,
            'customer' => $booking->customer,
            'vehicle' => $moment->vehicle
        ]);

        $pdf->setPaper('A4', 'portrait');

        /* Folder path */
        $folderPath = public_path('uploads/invoices');

        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0755, true);
        }

        $fileName = $receipt->receipt_number . '.pdf';
        $fullPath = $folderPath . '/' . $fileName;

        $pdf->save($fullPath);

        $receipt->update([
            'pdf_path' => 'uploads/invoices/' . $fileName
        ]);

        return response()->download($fullPath, $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
