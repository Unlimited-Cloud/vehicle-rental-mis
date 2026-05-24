<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EsewaIbftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Log;

class EsewaIbftController extends Controller
{
    public function __construct(protected EsewaIbftService $esewa) {}

    public function getBanks()
    {

        try {
            $banks = $this->esewa->getAvailableBanks();
            $sourceBanks = collect($banks)->filter(fn($b) => $b['source_account'])->values();
            $payeeBanks  = collect($banks)->filter(fn($b) => $b['payee_account'])->values();

            return response()->json([
                'success'      => true,
                'all_banks'    => $banks,
                'source_banks' => $sourceBanks,
                'payee_banks'  => $payeeBanks,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch eSewa banks: ' . $e->getMessage() . 'file ' . $e->getFile() . ' line ' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch available banks.'
            ], 500);
        }
    }

    public function validateAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_number'      => 'required|string',
            'swift_code'          => 'required|string',
            'account_holder_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $this->esewa->validateAccount(
                $request->account_number,
                $request->swift_code,
                $request->account_holder_name ?? ''
            );

            return response()->json(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            Log::error('Failed to validate account: ' . $e->getMessage() . 'file ' . $e->getFile() . ' line ' . $e->getLine());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function transfer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'destination_bank_code'      => 'required|string',
            'destination_account_number' => 'required|string',
            'destination_account_name'   => 'required|string',
            'amount'                     => 'required|numeric|min:1',
            'remarks'                    => 'nullable|string|max:255',
            'narration_one'              => 'nullable|string|max:255',
            'narration_two'              => 'nullable|string|max:255',
            'vehicle_booking_id'         => 'nullable|integer|exists:vehicle_bookings,id',
            'crew_id'                    => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {

            $payload = [
                'source_bank_code'           => "PRVUNPKA",
                'source_account_number'      => "1234567891011120",
                'source_account_name'        => "Test User",

                // Destination
                'destination_bank_code'      => $request->destination_bank_code,
                'destination_account_number' => $request->destination_account_number,
                'destination_account_name'   => $request->destination_account_name,

                // Amount & meta
                'amount'                     => $request->amount,
                'remarks'                    => $request->remarks ?? 'Payment',
                'narration_one'              => $request->narration_one ?? '',
                'narration_two'              => $request->narration_two ?? '',

                // // Linkage
                // 'vehicle_booking_id'         => $request->vehicle_booking_id,
                // 'crew_id'                    => $request->crew_id,
                // 'created_user_type'          => 'user',
            ];

            Log::info('eSewa Transfer Payload', $payload);

            $payment = $this->esewa->directSingleTransaction($payload);

            Log::info('eSewa IBFT transaction successful', [
                'payment' => $payment
            ]);

            return response()->json([
                'success'         => true,
                'message'         => 'Transfer initiated successfully.',
                'payload'         => $payload,
                'payment'         => $payment,
                'payment_id'      => $payment->id,
                'status'          => $payment->status,
                'transaction_ref' => $payment->transaction_reference,
            ]);
        } catch (Exception $e) {

            Log::error('Failed to do eSewa transaction', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to do eSewa transaction.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
