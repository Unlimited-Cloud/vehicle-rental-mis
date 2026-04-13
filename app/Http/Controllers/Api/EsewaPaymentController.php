<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EsewaPayment;
use App\Models\VehicleReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class EsewaPaymentController extends Controller
{
    public function generateSignature(Request $request)
    {
        $request->validate([
            'total_amount' => 'required|numeric',
            'booking_id' => 'required|exists:vehicle_bookings,id'
        ]);

        $secret = env('ESEWA_SECRET');

        $transaction_uuid = uniqid();

        $data = "total_amount={$request->total_amount},transaction_uuid={$transaction_uuid},product_code=NP-ES-SIGHTSEEING";

        $hash = base64_encode(hash_hmac('sha256', $data, $secret, true));

        // Save payment
        EsewaPayment::create([
            'transaction_uuid' => $transaction_uuid,
            'amount' => $request->total_amount,
            'status' => 'PENDING',
            'booking_id' => $request->booking_id
        ]);

        return [
            'signature' => $hash,
            'transaction_uuid' => $transaction_uuid
        ];
    }

    public function success(Request $request)
    {
        $data = json_decode(base64_decode($request->data), true);

        $payment = EsewaPayment::where('transaction_uuid', $data['transaction_uuid'])->first();

        if (!$payment) {
            return "Invalid transaction";
        }

        DB::transaction(function () use ($data, $payment) {

            // Update payment record
            $payment->update([
                'status' => $data['status'],
                'esewa_response' => json_encode($data)
            ]);

            $booking = $payment->booking;

            if ($data['status'] === 'COMPLETE') {

                // Update booking
                $booking->update([
                    'payment_status' => 1
                ]);

                // Create receipt
                VehicleReceipt::create([
                    'vehicle_booking_id' => $booking->id,
                    'vehicle_id' => $booking->vehicle_id,
                    'customer_id' => $booking->customer_id,
                    'amount' => $payment->amount,
                    'payment_method' => 'Esewa',
                    'remarks' => 'Paid via Esewa',
                    'paid' => 1,
                    'receipt_number' => 'RCPT-' . strtoupper(Str::random(6)),
                    'file_no' => $booking->file_no
                ]);

                // Optional service
                // BookingService::completeBooking($booking);

            } else {

                $booking->update([
                    'payment_status' => 'canceled'
                ]);
            }
        });

        return $data['status'] === 'COMPLETE'
            ? "Payment Successful"
            : "Payment Failed";
    }
}
