<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\EsewaPayment;
use App\Models\Payment;
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


    public function generateAttendanceSignature(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendance,id'
        ]);

        $attendance = Attendance::with([
            'crew.user'
        ])->findOrFail($request->attendance_id);

        // Duplicate check
        $existingPayment = Payment::where('attendance_id', $attendance->id)
            ->where('status', 'completed')
            ->first();

        if ($existingPayment) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance already paid'
            ], 422);
        }

        $amount = $attendance->allowances;

        $transaction_uuid = 'ATTE-' . strtoupper(Str::random(10));

        $secret = env('ESEWA_SECRET');

        $data = "total_amount={$amount},transaction_uuid={$transaction_uuid},product_code=EPAYTEST";

        $signature = base64_encode(
            hash_hmac('sha256', $data, $secret, true)
        );

        EsewaPayment::create([
            'transaction_uuid' => $transaction_uuid,
            'amount' => $amount,
            'status' => 'PENDING',
            'attendance_id' => $attendance->id,
            'booking_id' => $attendance->booking_id,
            'crew_id' => $attendance->crew_id,
            'payment_type' => 'attendance',
            'esewa_response' => null
        ]);

        return response()->json([
            'success' => true,
            'signature' => $signature,
            'transaction_uuid' => $transaction_uuid,
            'amount' => $amount,
            'success_url' => route('admin.esewa.success'),
            'failure_url' => route('admin.attendance.index'),
        ]);
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
                'status' => ucfirst(strtolower($data['status'])),
                'esewa_response' => json_encode($data)
            ]);

            if ($payment->payment_type == 'attendance') {

                $mainPayment = Payment::updateOrCreate(
                    [
                        'attendance_id' => $payment->attendance_id
                    ],
                    [
                        'crew_id' => $payment->crew_id,
                        'vehicle_booking_id' => $payment->booking_id,
                        'amount' => $payment->amount,
                        'payment_method' => 'online',
                        'payment_type' => 'attendance',
                        'notes' => 'Attendance allowance payment via Esewa',
                        'transaction_reference' => $payment->transaction_uuid,
                        'payment_date' => now(),
                        'status' => 'completed',
                    ]
                );

                $payment->update([
                    'payment_id' => $mainPayment->id
                ]);

                Attendance::where('id', $payment->attendance_id)
                    ->update([
                        'payment_status' => 'paid',
                        'payment_remarks' => 'By Esewa',
                    ]);
            } else {

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
                } else {

                    $booking->update([
                        'payment_status' => 'canceled'
                    ]);
                }
            }
        });

        $redirectUrl = null;

        if ($payment->payment_type == 'attendance') {
            $redirectUrl = route('admin.attendance.index');
        }

        return $data['status'] === 'COMPLETE'
            ? (
                $redirectUrl
                ? redirect($redirectUrl)->with('success', 'Payment Successful')
                : response()->json([
                    'message' => 'Payment Successful'
                ])
            )
            : (
                $redirectUrl
                ? redirect($redirectUrl)->with('error', 'Payment Failed')
                : response()->json([
                    'message' => 'Payment Failed'
                ], 400)
            );
    }
}
