<?php

namespace App\Services;

use App\Helpers\NepaliDateHelper;
use App\Models\ProformaInvoice;
use App\Models\VehicleBooking;
use App\Models\VehicleReceipt;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;


class ProformaService
{
    public function createProforma($booking)
    {
        $lastVersion = ProformaInvoice::where('vehicle_booking_id', $booking->id)
            ->max('version');

        $version = $lastVersion ? $lastVersion + 1 : 1;

        // Use database transaction with lock to prevent race conditions
        return DB::transaction(function () use ($booking, $version) {
            // Lock the table to prevent concurrent inserts from reading the same last record
            $lastInvoice = ProformaInvoice::lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();

            // Generate invoice number based on last inserted record
            if ($lastInvoice) {
                // Extract the sequence number from the last invoice
                // Format: PF-YYYY-XXXX
                $parts = explode('-', $lastInvoice->invoice_number);
                $lastNumber = intval(end($parts));
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $invoiceNumber = 'PF-' . date('Y') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $days = \Carbon\Carbon::parse($booking->start_date)
                ->diffInDays(\Carbon\Carbon::parse($booking->end_date)) + 1;

            $subTotal = $booking->rate_per_day * $days;

            // Calculate Discount
            if ($booking->discount_amount_type == 'percentage') {
                $discountAmount = ($subTotal * $booking->discount) / 100;
            } else {
                $discountAmount = $booking->discount;
            }

            // Final Total
            $total = $subTotal - $discountAmount;

            $invoice = ProformaInvoice::create([
                'vehicle_booking_id' => $booking->id,
                'vehicle_id' => $booking->vehicle_id,
                'invoice_number' => $invoiceNumber,
                'from_date' => $booking->start_date,
                'to_date' => $booking->end_date,
                'days' => $days,
                'rate_per_day' => $booking->rate_per_day,
                'sub_total' => $subTotal,
                'discount' => $discountAmount,
                'total_amount' => $total,
                'version' => $version
            ]);

            return $invoice;
        });
    }



    public function generateFinalInvoice($file_no)
    {
        try {
            // Fetch all bookings with the same file_no
            $bookings = VehicleBooking::with(['vehicle', 'customer', 'tripRoute'])
                ->where('file_no', $file_no)
                ->where('status', 'confirmed')
                ->orderBy('start_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();

            if ($bookings->isEmpty()) {
                return response()->json(['error' => 'No bookings found for this file number'], 404);
            }

            // Get customer details from first booking
            $customer = $bookings->first()->customer;

            if (!$customer) {
                return response()->json(['error' => 'Customer not found for these bookings'], 404);
            }

            // Calculate totals
            $sub_total = $bookings->sum('rate_per_day');
            $discount = $bookings->sum('discount');
            $tax = $bookings->sum('tax');
            $total_amount = $sub_total - $discount + $tax;


            // Check if receipt already exists for this file_no
            $exists = VehicleReceipt::where('file_no', $file_no)->exists();

            if ($exists) {
                return response()->json([
                    'error' => 'An invoice already exists for this file number.'
                ], 409);
            }

            // Generate receipt number
            $receipt_number = $this->generateReceiptNumber();

            DB::beginTransaction();

            try {
                // Create receipt record
                $receipt = VehicleReceipt::create([
                    'vehicle_booking_id' => null,
                    'vehicle_moment_id' => null,
                    'vehicle_id' => null,
                    'customer_id' => $customer->id,
                    'receipt_number' => $receipt_number,
                    'invoice_type' => 'vat',
                    'sub_total' => $sub_total,
                    'discount' => $discount,
                    'file_no' => $file_no,
                    'tax' => $tax,
                    'total_amount' => $total_amount,
                    'generated_at' => now(), // Add timestamp if you have this field
                ]);

                // Prepare data for view
                // Prepare data for view
                $data = [
                    'receipt' => $receipt,
                    'bookings' => $bookings,
                    'customer' => $customer,
                    'file_no' => $file_no,
                    'invoice_date' => Carbon::now('Asia/Kathmandu')->format('m/d/Y'),
                    'miti_date' => $this->convertToNepaliDate(now()),
                    'amount_in_words' => $this->convertNumberToWords(round($total_amount,2)),
                    'items' => $this->prepareInvoiceItems($bookings),
                    'sub_total' => $sub_total,
                    'discount' => $discount,
                    'tax' => $tax,
                    'net_amount' => $total_amount,
                    'vat_percentage' => 13,
                    'receipt_number' => $receipt_number,
                    'prepared_by' => 'Automatic',
                    'company_name' => 'ASHIVANA VEHICLE SERVICE PVT.LTD.',
                    'company_address' => 'Jwagal-10 Lalitpur, Nepal',
                    'company_phone' => '602439925',
                    'company_email' => 'e-account@ashivana.com.np',
                    'printing_time' => Carbon::now('Asia/Kathmandu')->format('m/d/Y h:i:s A'),
                ];

                // Generate PDF
                $pdf = PDF::loadView('layouts.admin.invoices.vehicle_invoice', $data);
                $pdf->setPaper('A4', 'portrait');

                // Folder path - consider using storage disk instead of public path for security
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

                // Commit transaction
                DB::commit();

                // Return view for preview
                return view('layouts.admin.invoices.vehicle_invoice', $data);
            } catch (\Exception $e) {
                // Rollback transaction on error
                DB::rollBack();

                // Log the error for debugging
                Log::error('Invoice generation failed: ' . $e->getMessage(), [
                    'file_no' => $file_no,
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'error' => 'Failed to generate invoice: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Unexpected error in generateFinalInvoice: ' . $e->getMessage(), [
                'file_no' => $file_no
            ]);

            return response()->json([
                'error' => 'An unexpected error occurred while generating the invoice'
            ], 500);
        }
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
    public function finalizeReceipt($file_no, $payment_method, $wallet_type, $wallet_number)
    {
        try {
            $receipt = VehicleReceipt::where('file_no', $file_no)->firstOrFail();

            // Update payment details
            $receipt->update([
                'payment_method' => $payment_method,
                'wallet_type'    => $wallet_type,
                'wallet_number'  => $wallet_number,
                'amount'         => $receipt->total_amount,
                'paid'           => "1",
            ]);

            // Load bookings
            $bookings = collect();

            if ($receipt->file_no) {
                $bookings = \App\Models\VehicleBooking::with(['vehicle', 'tripRoute'])
                    ->where('file_no', $receipt->file_no)
                    ->where('status', 'confirmed')
                    ->orderBy('start_date', 'asc')
                    ->orderBy('start_time', 'asc')
                    ->get();
            }

            $items = $this->prepareInvoiceItems($bookings);

            // Prepare data for PDF
            $data = [
                'receipt'      => $receipt,
                'items'        => $items,
                'customer'     => $receipt->customer,
                'invoice_date' => now(),
                'miti_date'    => $this->convertToNepaliDate(now()),
            ];

            // Generate FINAL RECEIPT PDF
            $pdf = Pdf::loadView('layouts.admin.invoices.final-receipt', $data);

            // Ensure folder exists
            $folderPath = public_path('uploads/finalinvoice');

            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0755, true);
            }

            $pdfFileName = 'final-' . $receipt->receipt_number . '.pdf';
            $pdfFullPath = $folderPath . '/' . $pdfFileName;

            $pdf->save($pdfFullPath);

            // Save path
            $receipt->update([
                'receipt_path' => 'uploads/finalinvoice/' . $pdfFileName
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Receipt finalized successfully',
                'path'    => asset($receipt->receipt_path)
            ]);
        } catch (\Exception $e) {

            Log::error('Receipt finalization failed', [
                'file_no'        => $file_no,
                'payment_method' => $payment_method,
                'wallet_type'    => $wallet_type,
                'wallet_number'  => $wallet_number,
                'message'        => $e->getMessage(),
                'file'           => $e->getFile(),
                'line'           => $e->getLine(),
                'trace'          => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to finalize receipt.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }


    private function prepareInvoiceItems($bookings)
    {
        $items = [];
        foreach ($bookings as $index => $booking) {
            $routeName = $booking->tripRoute ? $booking->tripRoute->title : 'Transportation Service';
            $vehicleName = $booking->vehicle ? $booking->vehicle->vehicle_name : 'Vehicle';
            $date = $booking->start_date
                ? \Carbon\Carbon::parse($booking->start_date)->format('jS M Y')
                : '';

            $time = $booking->start_time
                ? \Carbon\Carbon::parse($booking->start_time)->format('h:i A')
                : '';

            // Get the actual service description from booking notes if available
            $description = "{$routeName} By {$vehicleName}";
            if ($date) {
                $description .= " on {$date}";
            }

            if ($time) {
                $description .= " at {$time}";
            }


            $items[] = [
                'sn' => $index + 1,
                'hs_code' => 'Transportation Services',
                'particular' => $description,
                'vehicle_name' => $vehicleName,
                'route_name' => $routeName,
                'date' => $date,
                'qty' => 1,
                'qty_type' => 'PAX',
                'rate' => $booking->rate_per_day,
                'amount' => $booking->rate_per_day,
            ];
        }
        return $items;
    }


    private function convertToNepaliDate($date)
    {
        if (!$date) {
            return '';
        }

        // Ensure it's a string date (Y-m-d)
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
            'जेठ'   => '02',
            'असार'  => '03',
            'साउन'  => '04',
            'भदौ'  => '05',
            'असोज'  => '06',
            'कात्तिक' => '07',
            'मंसिर' => '08',
            'पुस'   => '09',
            'माघ'   => '10',
            'फागुन' => '11',
            'चैत'   => '12',
        ];

        $month = $monthMap[$monthName] ?? '00';

        return "{$day}/{$month}/{$year}";
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
}
