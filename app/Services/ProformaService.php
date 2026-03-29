<?php

namespace App\Services;

use App\Models\ProformaInvoice;
use Illuminate\Support\Facades\DB;


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
}
