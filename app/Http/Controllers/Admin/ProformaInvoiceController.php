<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EstimateBill;
use App\Models\ProformaInvoice;
use App\Models\VehicleBooking;
use App\Models\VehicleMoment;
use App\Models\VehicleReceipt;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\VehicleRepositoryInterface;
use App\Repositories\Interfaces\MasterRepositoryInterface;

class ProformaInvoiceController extends Controller
{
    protected $vehicleRepository;
    protected $masterRepository;

    private $currentUserId;

    private $currentUserCustomerId;

    private $currentUserIsCustomer;

    public function __construct(
        VehicleRepositoryInterface $vehicleRepository,
        MasterRepositoryInterface $masterRepository
    ) {
        $this->vehicleRepository = $vehicleRepository;
        $this->masterRepository = $masterRepository;

        $this->middleware(function ($request, $next) {
            $this->currentUserId = Auth::user()->id;
            $this->currentUserCustomerId = Auth::user()->customer_id;
            $this->currentUserIsCustomer = !empty(Auth::user()->customer_id) ? 'Y' : 'N';
            return $next($request);
        });
    }
    public function index()
    {
        Gate::authorize('index_bills_proforma_invoice');
        $invoices = $this->currentUserIsCustomer == 'Y' ? $this->vehicleRepository->getVehicleProformaByCustomerId($this->currentUserCustomerId) : $this->vehicleRepository->getAllVehicleProforma();

        return view('layouts.admin.invoices.index', compact('invoices'));
    }



    public function indexReceipt()
    {
        Gate::authorize('index_bills_receipt');
        $receipts = $this->currentUserIsCustomer == 'Y' ? $this->vehicleRepository->getVehicleReceiptsByCustomerId($this->currentUserCustomerId) : $this->vehicleRepository->getAllVehicleReceipts();

        return view('layouts.admin.invoices.index-receipt', compact('receipts'));
    }


    public function indexEstimate()
    {
        Gate::authorize('index_bills_receipt');
        $estimates = $this->currentUserIsCustomer == 'Y' ? $this->vehicleRepository->getVehicleEstimateByCustomerId($this->currentUserCustomerId) : $this->vehicleRepository->getAllVehicleEstimate();

        return view('layouts.admin.invoices.index-estimate', compact('estimates'));
    }



    public function downloadProforma($id)
    {
        $invoice = ProformaInvoice::findOrFail($id);

        if (!$invoice->pdf_path) {
            return redirect()->back()->with('error', 'PDF file not found in database.');
        }

        // Check in public/uploads/invoices path
        $filePath = public_path($invoice->pdf_path);

        if (!File::exists($filePath)) {
            return redirect()->back()->with('error', 'PDF file not found on server.');
        }

        // Return file download
        return response()->download($filePath, $invoice->invoice_number . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function downloadInvoice($id)
    {
        $receipt = VehicleReceipt::findOrFail($id);

        if (!$receipt->pdf_path) {
            return redirect()->back()->with('error', 'PDF file not found in database.');
        }

        // Check in public/uploads/invoices path
        $filePath = public_path($receipt->pdf_path);

        if (!File::exists($filePath)) {
            return redirect()->back()->with('error', 'PDF file not found on server.');
        }

        // Return file download
        return response()->download($filePath, $receipt->receipt_number . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }


    public function downloadEstimate($id)
    {
        $receipt = EstimateBill::findOrFail($id);

        if (!$receipt->pdf_path) {
            return redirect()->back()->with('error', 'PDF file not found in database.');
        }

        // Check in public/uploads/invoices path
        $filePath = public_path($receipt->pdf_path);

        if (!File::exists($filePath)) {
            return redirect()->back()->with('error', 'PDF file not found on server.');
        }

        // Return file download
        return response()->download($filePath, $receipt->estimate_number . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }



    public function generateFinalProforma($file_no)
    {
        // Fetch all bookings with the same file_no
        $bookings = VehicleBooking::with(['vehicle', 'customer', 'tripRoute'])
            ->where('file_no', $file_no)
            ->get();

        if ($bookings->isEmpty()) {
            return response()->json(['error' => 'No bookings found for this file number'], 404);
        }

        // Get customer details from first booking
        $customer = $bookings->first()->customer;

        // Calculate totals
        $sub_total = $bookings->sum('sub_total');
        $discount = $bookings->sum('discount');
        $tax = $bookings->sum('tax');
        $total_amount = $sub_total - $discount + $tax;

        // Generate receipt number
        $invoice_number = $this->generateProformaNumber();

        // Create receipt record
        $receipt = ProformaInvoice::create([
            'vehicle_booking_id' => null, // Multiple bookings, so null
            'vehicle_moment_id' => null,
            'vehicle_id' => null,
            'customer_id' => $customer ? $customer->id : null,
            'invoice_number' => $invoice_number,
            'invoice_type' => 'credit',
            'sub_total' => $sub_total,
            'discount' => $discount,
            'tax' => $tax,
            'total_amount' => $total_amount,
        ]);

        // Prepare data for view
        $data = [
            'receipt' => $receipt,
            'bookings' => $bookings,
            'customer' => $customer,
            'file_no' => $file_no,
            'invoice_date' => now(),
            'miti_date' => $this->convertToNepaliDate(now()),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('invoices.proforma-invoice', $data);
        $pdfPath = 'invoices/proforma-' . $invoice_number . '.pdf';
        $pdf->save(storage_path('app/public/' . $pdfPath));

        // Update receipt with PDF path
        $receipt->update(['pdf_path' => $pdfPath]);

        // Return view for preview
        return view('layouts.admin.invoices.proforma_pdf', $data);
    }




    public function generateFinalInvoice($file_no)
    {
        // Fetch all bookings with the same file_no
        $bookings = VehicleBooking::with(['vehicle', 'customer', 'tripRoute'])
            ->where('file_no', $file_no)
            ->get();

        if ($bookings->isEmpty()) {
            return response()->json(['error' => 'No bookings found for this file number'], 404);
        }

        // Get customer details from first booking
        $customer = $bookings->first()->customer;

        // Calculate totals
        $sub_total = $bookings->sum('sub_total');
        $discount = $bookings->sum('discount');
        $tax = $bookings->sum('tax');
        $total_amount = $sub_total - $discount + $tax;

        // Generate receipt number
        $receipt_number = $this->generateReceiptNumber();

        // Create receipt record
        $receipt = VehicleReceipt::create([
            'vehicle_booking_id' => null, // Multiple bookings, so null
            'vehicle_moment_id' => null,
            'vehicle_id' => null,
            'customer_id' => $customer ? $customer->id : null,
            'receipt_number' => $receipt_number,
            'invoice_type' => 'credit',
            'sub_total' => $sub_total,
            'discount' => $discount,
            'tax' => $tax,
            'total_amount' => $total_amount,
        ]);

        // Prepare data for view
        $data = [
            'receipt' => $receipt,
            'bookings' => $bookings,
            'customer' => $customer,
            'file_no' => $file_no,
            'invoice_date' => now(),
            'miti_date' => $this->convertToNepaliDate(now()),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('invoices.vehicle-invoice', $data);
        $pdfPath = 'invoices/invoice-' . $receipt_number . '.pdf';
        $pdf->save(storage_path('app/public/' . $pdfPath));

        // Update receipt with PDF path
        $receipt->update(['pdf_path' => $pdfPath]);

        // Return view for preview
        return view('layouts.admin.invoices.vehicle_invoice', $data);
    }


    public function generateSingleInvoice($booking_id)
    {
        $booking = VehicleBooking::with(['vehicle', 'customer', 'tripRoute'])
            ->findOrFail($booking_id);

        $receipt_number = $this->generateReceiptNumber();

        $receipt = VehicleReceipt::create([
            'vehicle_booking_id' => $booking->id,
            'vehicle_moment_id' => null,
            'vehicle_id' => $booking->vehicle_id,
            'customer_id' => $booking->customer_id,
            'receipt_number' => $receipt_number,
            'invoice_type' => 'credit',
            'start_datetime' => $booking->start_date,
            'end_datetime' => $booking->end_date,
            'hours' => $booking->no_of_hours,
            'days' => $booking->start_date && $booking->end_date ?
                $booking->start_date->diffInDays($booking->end_date) : null,
            'rate_per_day' => $booking->rate_per_day,
            'sub_total' => $booking->sub_total,
            'discount' => $booking->discount,
            'tax' => $booking->tax,
            'total_amount' => $booking->sub_total - $booking->discount + $booking->tax,
        ]);

        $data = [
            'receipt' => $receipt,
            'bookings' => collect([$booking]),
            'customer' => $booking->customer,
            'file_no' => $booking->file_no,
            'invoice_date' => now(),
            'miti_date' => $this->convertToNepaliDate(now()),
        ];

        $pdf = Pdf::loadView('invoices.vehicle-invoice', $data);
        $pdfPath = 'invoices/invoice-' . $receipt_number . '.pdf';
        $pdf->save(storage_path('app/public/' . $pdfPath));

        $receipt->update(['pdf_path' => $pdfPath]);

        return view('invoices.vehicle-invoice', $data);
    }

    private function generateReceiptNumber()
    {
        $year = date('y');
        $month = date('m');
        $lastReceipt = VehicleReceipt::whereYear('created_at', date('Y'))
            ->orderBy('id', 'desc')
            ->first();

        if ($lastReceipt) {
            $lastNumber = intval(substr($lastReceipt->receipt_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "ASB-{$month}{$year}-{$newNumber}";
    }

    private function generateProformaNumber()
    {
        $year = date('y');
        $month = date('m');
        $lastReceipt = ProformaInvoice::whereYear('created_at', date('Y'))
            ->orderBy('id', 'desc')
            ->first();

        if ($lastReceipt) {
            $lastNumber = intval(substr($lastReceipt->invoice_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "PF-{$month}{$year}-{$newNumber}";
    }


    public function finalizeReceipt(Request $request)
    {
        $receipt = VehicleReceipt::findOrFail($request->id);

        // Update payment details
        $receipt->update([
            'payment_method' => $request->payment_method,
            'check_no'       => $request->check_no,
            'check_date'     => $request->check_date,
            'bank_name'      => $request->bank_name,
            'bank_account'   => $request->bank_account,
            'amount'         => $request->amount,
            'paid'        => "1",
        ]);

        // Load bookings if needed
        $bookings = [];
        if ($receipt->file_no) {
            $bookings = \App\Models\VehicleBooking::with(['vehicle', 'tripRoute'])
                ->where('file_no', $receipt->file_no)
                ->get();
        }

        // Prepare data for PDF
        $data = [
            'receipt'      => $receipt,
            'bookings'     => $bookings,
            'customer'     => $receipt->customer,
            'invoice_date' => now(),
            'miti_date'    => now()->format('Y-m-d'), // or use Nepali date converter
        ];

        // Generate FINAL RECEIPT PDF
        $pdf = Pdf::loadView('layouts.admin.invoices.final-receipt', $data);

        // Ensure folder exists in public
        $folderPath = public_path('uploads/finalinvoice');
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0755, true);
        }

        $pdfFileName = 'final-' . $receipt->receipt_number . '.pdf';
        $pdfFullPath = $folderPath . '/' . $pdfFileName;

        // Save PDF directly into public folder
        $pdf->save($pdfFullPath);

        // Save relative path in DB
        $receipt->update([
            'receipt_path' => 'uploads/finalinvoice/' . $pdfFileName
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Receipt finalized successfully',
            'path'    => asset($receipt->receipt_path) // full URL if needed
        ]);
    }


    public function downloadReceipt($id)
    {
        $receipt = VehicleReceipt::findOrFail($id);

        if (!$receipt->receipt_path) {
            return redirect()->back()->with('error', 'PDF file not found in database.');
        }

        // Check in public/uploads/invoices path
        $filePath = public_path($receipt->receipt_path);

        if (!File::exists($filePath)) {
            return redirect()->back()->with('error', 'PDF file not found on server.');
        }

        // Return file download
        return response()->download($filePath, $receipt->receipt_number . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function convertNumberToWords($number)
    {
        $number = round($number, 2);
        $decimal = round(($number - floor($number)) * 100);
        $number = floor($number);

        $words = $this->convertToWords($number);

        if ($decimal > 0) {
            $words .= " And {$this->convertToWords($decimal)} Palsa";
        } else {
            $words .= " Only";
        }

        return "Rs. " . ucwords($words);
    }

    private function convertToWords($number)
    {
        $ones = [
            0 => '',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen'
        ];

        $tens = [
            2 => 'Twenty',
            3 => 'Thirty',
            4 => 'Forty',
            5 => 'Fifty',
            6 => 'Sixty',
            7 => 'Seventy',
            8 => 'Eighty',
            9 => 'Ninety'
        ];

        if ($number < 20) {
            return $ones[$number];
        }

        if ($number < 100) {
            return $tens[floor($number / 10)] . ($number % 10 ? ' ' . $ones[$number % 10] : '');
        }

        if ($number < 1000) {
            return $ones[floor($number / 100)] . ' Hundred' . ($number % 100 ? ' ' . $this->convertToWords($number % 100) : '');
        }

        if ($number < 100000) {
            return $this->convertToWords(floor($number / 1000)) . ' Thousand' . ($number % 1000 ? ' ' . $this->convertToWords($number % 1000) : '');
        }

        if ($number < 10000000) {
            return $this->convertToWords(floor($number / 100000)) . ' Lakh' . ($number % 100000 ? ' ' . $this->convertToWords($number % 100000) : '');
        }

        return $this->convertToWords(floor($number / 10000000)) . ' Crore' . ($number % 10000000 ? ' ' . $this->convertToWords($number % 10000000) : '');
    }

    /**
     * Convert to Nepali date (simplified - you may want to use a proper library)
     */
    private function convertToNepaliDate($date)
    {
        // This is a simplified version
        // You should implement proper Nepali date conversion or use a library
        return $date->format('d/m/Y');
    }






    /**
     * Get bookings by file number for preview
     */
    public function getBookingsByFileNo($file_no)
    {
        $bookings = VehicleBooking::with(['vehicle', 'customer', 'tripRoute'])
            ->where('file_no', $file_no)
            ->get();

        if ($bookings->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No bookings found'
            ]);
        }

        $customer = $bookings->first()->customer;
        $sub_total = $bookings->sum('sub_total');
        $discount = $bookings->sum('discount');
        $tax = $bookings->sum('tax');
        $net_amount = $sub_total - $discount + $tax;

        $items = [];
        foreach ($bookings as $index => $booking) {
            $routeName = $booking->tripRoute ? $booking->tripRoute->name : 'Transportation Service';
            $vehicleName = $booking->vehicle ? $booking->vehicle->vehicle_name : 'Vehicle';
            $date = $booking->start_date ? \Carbon\Carbon::parse($booking->start_date)->format('jS M Y') : '';

            $items[] = [
                'sn' => $index + 1,
                'particular' => "{$routeName} By {$vehicleName} On {$date}",
                'qty' => $booking->passenger ?: 1,
                'rate' => $booking->sub_total,
                'amount' => $booking->sub_total,
            ];
        }

        return response()->json([
            'success' => true,
            'file_no' => $file_no,
            'customer_name' => $customer ? $customer->name : 'N/A',
            'customer_pan' => $customer ? $customer->pan_number : '',
            'customer_address' => $customer ? $customer->address : '',
            'items' => $items,
            'sub_total' => $sub_total,
            'discount' => $discount,
            'tax' => $tax,
            'net_amount' => $net_amount,
            'total_bookings' => $bookings->count()
        ]);
    }
}
