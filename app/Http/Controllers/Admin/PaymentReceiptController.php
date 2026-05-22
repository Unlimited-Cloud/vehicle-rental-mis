<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\NepaliDateHelper;
use App\Helpers\NumberHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentReceipt;
use App\Models\PaymentReceiptItem;
use App\Models\VehicleReceipt;
use App\Models\Customer;
use App\Models\BasicTable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentReceiptController extends Controller
{
    private $currentUserId;
    private $currentUserCustomerId;
    private $currentUserIsCustomer;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->currentUserId = Auth::user()->id;
            $this->currentUserCustomerId = Auth::user()->customer_id;
            $this->currentUserIsCustomer = !empty(Auth::user()->customer_id) ? 'Y' : 'N';
            return $next($request);
        });
    }

    public function index()
    {
        // Gate::authorize('index_bills_receipt');

        $paymentReceipts = $this->currentUserIsCustomer == 'Y'
            ? PaymentReceipt::where('customer_id', $this->currentUserCustomerId)->orderBy('created_at', 'desc')->get()
            : PaymentReceipt::orderBy('created_at', 'desc')->get();

        return view('layouts.admin.payments_receipts.index', compact('paymentReceipts'));
    }

    public function create()
    {
        // Gate::authorize('create_payment_receipt');

        $customers = Customer::orderBy('name')->get();
        $tdsRate = BasicTable::first()->tds;
        $tdsRateValue = $tdsRate ? floatval($tdsRate) : 1.5;

        return view('layouts.admin.payments_receipts.create', compact('customers', 'tdsRateValue'));
    }

    public function getUnpaidInvoices(Request $request)
    {
        $customerId = $request->customer_id;

        $invoices = VehicleReceipt::where('customer_id', $customerId)
            ->where('paid', 0)
            ->whereNotNull('pdf_path')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'receipt_number', 'total_amount', 'created_at']);

        $totalUnpaidAmount = $invoices->sum('total_amount');

        // Get TDS rate from basic_tables
        $tdsRate = BasicTable::value('tds');
        $tdsRateValue = $tdsRate ? floatval($tdsRate) : 1.5;

        return response()->json([
            'success' => true,
            'invoices' => $invoices,
            'total_unpaid' => $totalUnpaidAmount,
            'tds_rate' => $tdsRateValue
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'selected_invoices' => 'required|array|min:1',
            'selected_invoices.*' => 'exists:vehicle_receipts,id',
            'payment_method' => 'required|in:cash,bank,cheque,wallet',
            'payment_date' => 'required|date',
            'received_amount' => 'required|numeric|min:0',
            'tds_applied' => 'boolean'
        ]);

        DB::beginTransaction();

        try {
            // Get TDS rate
            $tdsRate = BasicTable::first()->tds;
            $tdsRateValue = $tdsRate ? floatval($tdsRate) : 1.5;

            // Get selected invoices
            $invoices = VehicleReceipt::whereIn('id', $request->selected_invoices)
                ->where('paid', 0)
                ->get();

            $totalInvoiceAmount = $invoices->sum('total_amount');
            $receivedAmount = floatval($request->received_amount);

            // Calculate TDS if applied
            $tdsDeduction = 0;
            $netPaidAmount = $totalInvoiceAmount;

            if ($request->tds_applied) {
                // TDS is applied on taxable amount (without VAT? Clarify with business logic)
                // Assuming TDS is on total amount including VAT as per your requirement
                $tdsDeduction = round(($totalInvoiceAmount * $tdsRateValue) / 100, 2);
                $netPaidAmount = $totalInvoiceAmount - $tdsDeduction;
            }

            // Validate that entered amount matches calculated amount
            if (floatval($request->received_amount) != $netPaidAmount) {
                return response()->json([
                    'success' => false,
                    'message' => "Payment amount mismatch. Expected: {$netPaidAmount}, Received: {$request->received_amount}"
                ], 422);
            }

            // Calculate difference
            $differenceAmount = $receivedAmount - $netPaidAmount;
            $differenceNote = null;

            if (abs($differenceAmount) > 0.01) { // If difference exists
                if ($differenceAmount > 0) {
                    $differenceNote = "Overpayment of Rs. " . number_format($differenceAmount, 2);
                } else {
                    $differenceNote = "Short payment of Rs. " . number_format(abs($differenceAmount), 2);
                }
            }

            // Generate receipt number
            $receiptNumber = $this->generatePaymentReceiptNumber();

            // Create payment receipt
            $paymentReceipt = PaymentReceipt::create([
                'receipt_number' => $receiptNumber,
                'customer_id' => $request->customer_id,
                'total_invoice_amount' => $totalInvoiceAmount,
                'tds_deduction' => $tdsDeduction,
                'tds_rate' => $tdsRateValue,
                'net_paid_amount' => $netPaidAmount,
                'received_amount' => $receivedAmount,
                'difference_amount' => $differenceAmount,
                'payment_method' => $request->payment_method,
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account,
                'cheque_number' => $request->cheque_number,
                'cheque_date' => $request->cheque_date,
                'transaction_id' => $request->transaction_id,
                'payment_date' => $request->payment_date,
                'notes' => $request->notes,
                'tds_applied' => $request->tds_applied ? true : false
            ]);

            // Create payment receipt items and update invoice paid status
            foreach ($invoices as $invoice) {
                PaymentReceiptItem::create([
                    'payment_receipt_id' => $paymentReceipt->id,
                    'vehicle_receipt_id' => $invoice->id,
                    'invoice_number' => $invoice->receipt_number,
                    'invoice_amount' => $invoice->total_amount,
                    'paid_amount' => $invoice->total_amount
                ]);

                // Update invoice as paid
                $invoice->update(['paid' => 1]);
            }

            // Generate PDF
            $this->generatePaymentReceiptPDF($paymentReceipt);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment receipt generated successfully',
                'receipt' => $paymentReceipt
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate payment receipt: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generatePaymentReceiptNumber()
    {
        $year = date('y');
        $month = date('m');
        $lastReceipt = PaymentReceipt::whereYear('created_at', date('Y'))
            ->orderBy('id', 'desc')
            ->first();

        if ($lastReceipt) {
            $lastNumber = intval(substr($lastReceipt->receipt_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "PR-{$month}{$year}-{$newNumber}";
    }

    private function generatePaymentReceiptPDF($paymentReceipt)
    {
        $paymentReceipt->load(['customer', 'items.vehicleReceipt']);

        // Prepare items for invoice display
        $items = [];
        foreach ($paymentReceipt->items as $index => $item) {
            $invoice = $item->vehicleReceipt;
            $items[] = [
                'sn' => $index + 1,
                'hs_code' => '998422',
                'particular' => 'Transport Service - Invoice: ' . $item->invoice_number,
                'qty' => 1,
                'qty_type' => 'Service',
                'rate' => $item->invoice_amount,
                'amount' => $item->invoice_amount,
                'invoice_date' => $invoice ? $invoice->created_at->format('Y-m-d') : 'N/A'
            ];
        }

        $data = [
            'receipt' => $paymentReceipt,
            'customer' => $paymentReceipt->customer,
            'items' => $items,
            'invoice_date' => now(),
            'miti_date' => $this->convertToNepaliDate(now()),
            'printing_time' => now()->format('Y-m-d h:i A'),
            'amount_in_words' => $this->convertNumberToWords($paymentReceipt->received_amount),
            'vat_percentage' => 13
        ];

        $pdf = Pdf::loadView('layouts.admin.payments_receipts.payment-receipt', $data);

        $folderPath = public_path('uploads/payment_receipts');
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0755, true);
        }

        $pdfFileName = 'payment_receipt_' . $paymentReceipt->receipt_number . '.pdf';
        $pdfFullPath = $folderPath . '/' . $pdfFileName;
        $pdf->save($pdfFullPath);

        $paymentReceipt->update([
            'pdf_path' => 'uploads/payment_receipts/' . $pdfFileName
        ]);
    }

    private function convertToNepaliDate($date)
    {
        if (!$date) {
            return '';
        }

        $englishDate = $date instanceof \Carbon\Carbon
            ? $date->format('Y-m-d')
            : $date;

        $nepaliDate = NepaliDateHelper::convertToNepali($englishDate);
        $devanagariNumbers = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];
        $englishNumbers   = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $day   = str_replace($devanagariNumbers, $englishNumbers, $nepaliDate['day'] ?? '');
        $monthName = $nepaliDate['month'] ?? '';
        $year  = str_replace($devanagariNumbers, $englishNumbers, $nepaliDate['year'] ?? '');
        $monthMap = [
            'वैशाख' => '01',
            'जेठ' => '02',
            'असार' => '03',
            'साउन' => '04',
            'भदौ' => '05',
            'असोज' => '06',
            'कात्तिक' => '07',
            'मंसिर' => '08',
            'पुस' => '09',
            'माघ' => '10',
            'फागुन' => '11',
            'चैत' => '12',
        ];

        $month = $monthMap[$monthName] ?? '00';
        return "{$day}/{$month}/{$year}";
    }

    private function convertNumberToWords($number)
    {
        $helper = new NumberHelper();
        return $helper->numberToWords(round($number, 2));
    }

    public function download($id)
    {
        $paymentReceipt = PaymentReceipt::findOrFail($id);

        if (!$paymentReceipt->pdf_path || !file_exists(public_path($paymentReceipt->pdf_path))) {
            return redirect()->back()->with('error', 'PDF file not found.');
        }

        return response()->download(
            public_path($paymentReceipt->pdf_path),
            $paymentReceipt->receipt_number . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function show($id)
    {
        $paymentReceipt = PaymentReceipt::with(['customer', 'items.vehicleReceipt'])->findOrFail($id);
        return view('layouts.admin.payments_receipts.show', compact('paymentReceipt'));
    }

    public function getTDSReport()
    {
        Gate::authorize('index_reporting_analytics_tds_report');

        $tdsPayments = PaymentReceipt::where('tds_applied', true)
            ->with('customer')
            ->orderBy('payment_date', 'desc')
            ->get();

        $totalTDSDeducted = $tdsPayments->sum('tds_deduction');
        $totalTDSAmount = $tdsPayments->sum('total_invoice_amount');

        return view('layouts.admin.payments_receipts.tds-report', compact('tdsPayments', 'totalTDSDeducted', 'totalTDSAmount'));
    }
}
