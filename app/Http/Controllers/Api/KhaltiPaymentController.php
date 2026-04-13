<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KhaltiPayment;
use App\Models\VehicleBooking;
use App\Models\VehicleReceipt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class KhaltiPaymentController extends Controller
{
    public function initiatePayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
            'mobile' => 'required',
            'booking_id' => 'required|exists:vehicle_bookings,id'
        ]);

        $booking = VehicleBooking::findOrFail($request->booking_id);

        $merchantTransactionId = $request->transaction_id ?? 'ASH:' . strtoupper(Str::random(8));

        $payload = [
            "return_url" => route('khalti.confirm'),
            "website_url" => config('app.url'),
            "amount" => $request->amount * 100,
            "purchase_order_id" => $merchantTransactionId,
            "purchase_order_name" => "Vehicle Rental Payment",
            "customer_info" => [
                "name" => $request->name ?? 'Guest User',
                "email" => $request->email,
                "phone" => $request->mobile
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => 'key ' . env('KHALTI_KEY'),
        ])->post(env('KHALTI_API_URL') . '/api/v2/epayment/initiate/', $payload);

        $data = $response->json();

        if (isset($data['pidx'])) {
            KhaltiPayment::create([
                'booking_id' => $booking->id,
                'pidx' => $data['pidx'],
                'merchant_transaction_id' => $merchantTransactionId,
                'amount' => $request->amount,
                'user_name' => $request->name,
                'user_email' => $request->email,
                'user_mobile' => $request->mobile,
                'status' => 'Initiated',
                'khalti_init_response' => json_encode($data),
            ]);
        }

        return response()->json($data);
    }

    public function confirmPayment(Request $request)
    {
        $request->validate([
            'pidx' => 'required'
        ]);

        $payment = KhaltiPayment::where('pidx', $request->pidx)->firstOrFail();

        $response = Http::withHeaders([
            'Authorization' => 'key ' . env('KHALTI_KEY'),
        ])->post(env('KHALTI_API_URL') . '/api/v2/epayment/lookup/', [
            'pidx' => $request->pidx
        ]);

        $data = $response->json();

        DB::transaction(function () use ($data, $payment) {

            if ($data['status'] === 'Completed') {

                // Update payment
                $payment->update([
                    'status' => 'Completed',
                    'txn_id' => $data['transaction_id'] ?? null,
                    'total_amount' => $data['total_amount'] ?? null,
                ]);

                // Update booking
                $booking = $payment->booking;

                $booking->update([
                    'payment_status' => 1
                ]);

                // Create Receipt
                VehicleReceipt::create([
                    'vehicle_booking_id' => $booking->id,
                    'vehicle_id' => $booking->vehicle_id,
                    'customer_id' => $booking->customer_id,
                    'amount' => $payment->amount,
                    'payment_method' => 'Khalti',
                    'remarks' => 'Paid via Khalti',
                    'paid' => 1,
                    'receipt_number' => 'ASB-' . strtoupper(Str::random(6)),
                    'file_no' => $booking->file_no
                ]);
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

        return response()->json([
            'message' => 'Transaction updated'
        ]);
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
