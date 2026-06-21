<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\BookingLog;
use App\Models\Customer;
use App\Models\EsewaPayment;
use App\Models\Payment;
use App\Models\VehicleBooking;
use App\Models\VehicleReceipt;
use App\Services\ProformaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class EsewaPaymentController extends Controller
{
    protected $service;

    public function __construct(ProformaService $service)
    {
        $this->service = $service;
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

        $secret = "8gBm/:&EnhH.1/q";

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

    public function generateSignature(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:vehicle_bookings,id',
            'customer_id' => 'required|exists:customers,customer_uuid'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $booking = VehicleBooking::find($request->booking_id);

        if (!$booking) {
            return response()->json([
                'status' => false,
                'message' => 'Booking not found.'
            ], 422);
        }


        $customers = Customer::where('customer_uuid', $request->customer_id)->first();

        if (!$customers) {
            return response()->json([
                'status' => false,
                'message' => 'Customer not found.'
            ], 422);
        }
        $secret = "8gBm/:&EnhH.1/q";

        $transaction_uuid = uniqid();

        $data = "total_amount={$booking->total_amount},transaction_uuid={$transaction_uuid},product_code=EPAYTEST";


        // Create payment record
        $payment = Payment::create([
            'vehicle_booking_id' => $request->booking_id,
            'amount' => $booking->total_amount,
            'payment_method' => 'online',
            'payment_type' => 'booking',
            'payment_date' => now(),
            'status' => 'pending',
            'created_by' => $customers->id,
            'created_user_type' => 'customer',
            'notes' => 'Vehicle rental payment via Esewa'
        ]);

        $hash = base64_encode(hash_hmac('sha256', $data, $secret, true));

        // Save payment
        EsewaPayment::create([
            'transaction_uuid' => $transaction_uuid,
            'amount' => $booking->total_amount,
            'status' => 'PENDING',
            'booking_id' => $booking->id,
            'payment_id' => $payment->id,
            'payment_type' => 'booking',
        ]);

        return response()->json([
            'success' => true,
            'signature' => $hash,
            'transaction_uuid' => $transaction_uuid,
            'amount' => $booking->total_amount,
            'success_url' => route('esewa.success'),
            'failure_url' => '',
        ]);
    }



    public function success(Request $request)
    {
        $data = json_decode(base64_decode($request->data), true);

        Log::info('Esewa Payment Response', $data);
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
                        'direction' => 'in',
                        'gateway' => "esewa",
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

                    $mainPayment = Payment::updateOrCreate(
                        [
                            'id' => $payment->payment_id
                        ],
                        [
                            'vehicle_booking_id' => $payment->booking_id,
                            'amount' => $payment->amount,
                            'payment_method' => 'online',
                            'payment_type' => 'booking',
                            'notes' => 'Vehicle rental payment via Esewa',
                            'transaction_reference' => $payment->transaction_uuid,
                            'payment_date' => now(),
                            'status' => 'completed',
                        ]
                    );

                    // Update booking
                    $booking->update([
                        'payment_status' => 1
                    ]);
                    BookingLog::create([
                        'booking_id' => $payment->booking_id,
                        'status' => 'paid',
                        'remarks' => 'Booking paid by customer via Esewa',
                    ]);

                    // Create Receipt
                    $this->service->finalizeReceipt($booking->file_no, 'wallet', "esewa", $booking->customer->name);
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
                    'status' => 'success',
                    'message' => 'Payment Successful',
                ], 200)
            )
            : (
                $redirectUrl
                ? redirect($redirectUrl)->with('error', 'Payment Failed')
                : response()->json([
                    'status' => 'error',
                    'message' => 'Payment Failed'
                ], 400)
            );
    }
}
