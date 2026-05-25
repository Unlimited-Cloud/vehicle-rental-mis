<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\KhaltiPayment;
use App\Models\Payment;
use App\Models\VehicleBooking;
use App\Models\VehicleReceipt;
use App\Services\ProformaService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class KhaltiPaymentController extends Controller
{

    protected $service;

    public function __construct(ProformaService $service)
    {
        $this->service = $service;
    }

    public function initiateAttendancePayment(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendance,id'
        ]);

        DB::beginTransaction();

        try {

            $attendance = Attendance::with([
                'crew.user',
                'khaltiPayment'
            ])->findOrFail($request->attendance_id);

            $existingPayment = Payment::where('attendance_id', $attendance->id)
                ->where('status', 'completed')
                ->first();

            if ($existingPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance allowance already paid.'
                ], 422);
            }

            $crew = $attendance->crew;
            $user = $crew->user;

            $amount = $attendance->allowances;

            if (!$amount || $amount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid allowance amount.'
                ], 422);
            }

            $merchantTransactionId = 'ATTK-' . strtoupper(Str::random(10));

            // Create payment record
            $payment = Payment::create([
                'attendance_id' => $attendance->id,
                'vehicle_booking_id' => $attendance->booking_id,
                'crew_id' => $attendance->crew_id,
                'amount' => $amount,
                'payment_method' => 'online',
                'payment_type' => 'attendance',
                'payment_date' => now(),
                'status' => 'pending',
                'created_by' => Auth::id(),
                'notes' => 'Attendance allowance payment via Khalti'
            ]);

            $payload = [
                "return_url" => route('admin.khalti.confirm'),
                "website_url" => config('app.url'),
                "amount" => $amount * 100,
                "purchase_order_id" => $merchantTransactionId,
                "purchase_order_name" => "Attendance Allowance Payment",
                "customer_info" => [
                    "name" => $user->name ?? 'Crew Member',
                    "email" => $user->email ?? '',
                    "phone" => $crew->contact_number ?? ''
                ]
            ];
            Log::info('Initiating Khalti payment with payload: ', $payload);

            $url = env('KHALTI_API_URL') ?? "https://dev.khalti.com/api/v2/" . 'epayment/initiate/';

            Log::info('Initiating Khalti payment with payload: ', ['url' => $url, 'payload' => $payload]);

            // dd($url, $payload);

            $khaltiKey = env('KHALTI_KEY') ?? "0dfc7e70c51b4edab0f7d49f031ed0db";


            $response = Http::withHeaders([
                'Authorization' => 'key ' . $khaltiKey,
            ])->post(
                $url,
                $payload
            );
            Log::info('Khalti response: ', ['response' => $response->body()]);

            $data = $response->json();

            if (!isset($data['pidx'])) {

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => $data['detail'] ?? 'Failed to initiate Khalti payment',
                    'response' => $data
                ], 422);
            }

            // Save Khalti metadata
            KhaltiPayment::create([
                'attendance_id' => $attendance->id,
                'payment_id' => $payment->id,
                'payment_type' => 'attendance',
                'crew_id' => $attendance->crew_id,
                'booking_id' => $attendance->booking_id,
                'merchant_transaction_id' => $merchantTransactionId,
                'pidx' => $data['pidx'],
                'amount' => $amount,
                'user_name' => $user->name ?? '',
                'user_email' => $user->email ?? '',
                'user_mobile' => $crew->contact_number ?? '',
                'status' => 'Initiated',
                'khalti_init_response' => json_encode($data),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'payment_url' => $data['payment_url']
            ]);
        } catch (\Exception $e) {
            Log::error("KhaltiPaymentController initiateAttendancePayment error", [
                "file" => $e->getFile(),
                "line" => $e->getLine(),
                "message" => $e->getMessage(),
            ]);
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function initiatePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:vehicle_bookings,id',
            'customer_id' => 'required|exists:customers,customer_uuid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {

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

            $merchantTransactionId =
                $request->transaction_id ?? 'ASH:' . strtoupper(Str::random(8));

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
                'notes' => 'Vehicle rental payment via Khalti'
            ]);

            $payload = [
                "return_url" => route('khalti.confirm'),
                "website_url" => config('app.url'),
                "amount" => $booking->total_amount * 100,
                "purchase_order_id" => $merchantTransactionId,
                "purchase_order_name" => "Vehicle Rental Payment",
                "customer_info" => [
                    "name" => $customers->name ?? 'Guest User',
                    "email" => $customers->email,
                    "phone" => $request->mobile ?? $customers->phone
                ]
            ];

            $url = rtrim(
                env('KHALTI_API_URL', 'https://dev.khalti.com/api/v2/'),
                '/'
            ) . '/epayment/initiate/';

            Log::info('Initiating Khalti payment', [
                'url' => $url,
                'payload' => $payload
            ]);

            $khaltiKey = env('KHALTI_KEY') ?? "0dfc7e70c51b4edab0f7d49f031ed0db";

            $response = Http::withHeaders([
                'Authorization' => 'key ' . $khaltiKey,
            ])->post($url, $payload);

            Log::info('Khalti response', [
                'response' => $response->body()
            ]);

            if (!$response->successful()) {

                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Failed to initiate Khalti payment.',
                    'response' => $response->json()
                ], 500);
            }

            $data = $response->json();

            if (isset($data['pidx'])) {

                KhaltiPayment::create([
                    'booking_id' => $booking->id,
                    'payment_type' => 'booking',
                    'payment_id' => $payment->id,
                    'pidx' => $data['pidx'],
                    'merchant_transaction_id' => $merchantTransactionId,
                    'amount' => $booking->total_amount,
                    'user_name' => $customers->name ?? 'Guest User',
                    'user_email' => $customers->email,
                    'user_mobile' => $request->mobile ?? $customers->phone,
                    'status' => 'Initiated',
                    'khalti_init_response' => json_encode($data),
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error("KhaltiPaymentController initiatePayment error", [
                "file" => $e->getFile(),
                "line" => $e->getLine(),
                "message" => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    public function confirmPayment(Request $request)
    {

        $request->validate([
            'pidx' => 'required'
        ]);

        Log::info('Confirming Khalti payment with pidx: ' . $request->pidx);

        $payment = KhaltiPayment::where('pidx', $request->pidx)->firstOrFail();

        $url = rtrim(
            env('KHALTI_API_URL', 'https://dev.khalti.com/api/v2/'),
            '/'
        ) . '/epayment/lookup/';

        $khaltiKey = env('KHALTI_KEY') ?? "0dfc7e70c51b4edab0f7d49f031ed0db";

        $response = Http::withHeaders([
            'Authorization' => 'key ' . $khaltiKey,
        ])->post($url, [
            'pidx' => $request->pidx
        ]);

        Log::info('Khalti lookup response: ', ['response' => $response->body()]);

        $data = $response->json();

        DB::transaction(function () use ($data, $payment) {

            if ($data['status'] === 'Completed') {

                $fees = isset($data['fee']) ? $data['fee'] / 100 : 0;
                $amount = isset($data['total_amount']) ? $data['total_amount'] / 100 : 0;
                $total_amount = $amount + $fees;

                // Update payment
                $payment->update([
                    'status' => 'Completed',
                    'txn_id' => $data['transaction_id'] ?? null,
                    'total_amount' => $total_amount,
                    'fees' => $fees,
                ]);

                // Update booking
                $booking = $payment->booking;

                $booking->update([
                    'payment_status' => 1
                ]);

                if ($payment->payment_type == 'attendance') {
                    $attendanceId = $payment->attendance_id;

                    Payment::updateOrCreate(
                        [
                            'attendance_id' => $attendanceId,
                        ],
                        [
                            'amount' => $payment->amount,
                            'transaction_reference' => $data['transaction_id'] ?? null,
                            'payment_date' => now(),
                            'status' => 'completed',
                        ]
                    );

                    Attendance::where('id', $attendanceId)->update([
                        'payment_status' => 'paid',
                        'payment_remarks' => 'By Khalti',
                    ]);
                } else {


                    $paymentId = $payment->payment_id;


                    Payment::updateOrCreate(
                        [
                            'id' => $paymentId,
                        ],
                        [
                            'transaction_reference' => $data['transaction_id'] ?? null,
                            'payment_date' => now(),
                            'status' => 'completed',
                        ]
                    );

                    // Create Receipt
                    $this->service->finalizeReceipt($booking->file_no, 'wallet', "khalti", $payment->user_mobile);
                }
            } else {

                // Failed / Pending
                $payment->update([
                    'status' => $data['status']
                ]);

                $payment->booking->update([
                    'payment_status' => 'canceled'
                ]);
            }
        });

        $redirectUrl = null;

        if ($payment->payment_type == 'attendance') {
            $redirectUrl = route('admin.attendance.index');
        }

        return $redirectUrl
            ? redirect($redirectUrl)->with('success', 'Payment completed successfully')
            : response()->json([
                'status' => 'success',
                'message' => 'Transaction updated',
            ], 200);
    }





    public function refundPayment(Request $request)
    {
        // This method can be implemented to handle payment confirmation from Khalti

        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required',

        ], [
            'transaction_id.required' => 'transaction_id is required',

        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 403);
        }
        $success = [];
        $khaltiPayment = KhaltiPayment::where('txn_id', $request->transaction_id)->first();

        if ($khaltiPayment) {
            $success['transaction'] = $khaltiPayment;
        }

        if ($request->transaction_id) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => env('KHALTI_API_URL') . '/api/merchant-transaction/' . $request->transaction_id . '/refund/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                        "mobile": "' . $khaltiPayment->user_mobile . '",
                        "amount": ' . $request->amount . '
                    }

                    ',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: key ' . env('KHALTI_KEY'),
                    'Content-Type: application/json',
                ),
            ));

            $response = curl_exec($curl);
            //dd($response);
            curl_close($curl);
            $khaltiResponse = json_decode($response);
            $success['khaltiResponse'] = $khaltiResponse;

            if ($khaltiResponse) {
                $khaltiPayment->status = $khaltiResponse->status;

                $khaltiPayment->save();

                $booking = VehicleBooking::where('pidx', $khaltiPayment->pidx)->first();
                if ($booking) {
                    $booking->is_refunded = true;
                    $booking->refunded_amount = $request->refunded_amount;
                    $booking->refund_reason = $request->refund_reason;
                    $booking->refunded_at = now();
                    $booking->refunded_by = auth()->id();
                    $booking->gateway_refund_id = $request->transaction_id;
                    $booking->payment_status = 'refunded';
                    $booking->save();
                }
            }


            //return $response;
            $success['message'] = "Transaction refunded";
        }



        return response()->json($success, 200);
    }
}
