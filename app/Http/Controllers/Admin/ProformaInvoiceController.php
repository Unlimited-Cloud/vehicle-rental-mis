<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $invoices = ProformaInvoice::with(['vehicle', 'booking'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('layouts.admin.invoices.index', compact('invoices'));
    }

    public function indexReceipt()
    {
        Gate::authorize('index_bills_receipt');
        $receipts = $this->currentUserIsCustomer == 'Y' ? $this->vehicleRepository->getVehicleReceiptsByCustomerId($this->currentUserCustomerId) : $this->vehicleRepository->getAllVehicleReceipts();

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
}
