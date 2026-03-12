<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProformaInvoice;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ProformaInvoiceController extends Controller
{

    public function index()
    {
        $invoices = ProformaInvoice::with(['vehicle', 'booking'])
            ->latest()
            ->get();

        return view('layouts.admin.invoices.index', compact('invoices'));
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
}
