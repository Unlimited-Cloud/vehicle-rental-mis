<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EsewaIbftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Attendance;
use App\Models\CrewBankDetail;
use App\Models\CrewProfile;

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
                'source_account_number'      => "9100100008977000001",  //1234567891011120
                'source_account_name'        => "Test CE",

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
                'vehicle_booking_id'         => $request->vehicle_booking_id,
                'crew_id'                    => $request->crew_id,
                'created_user_type'          => 'user',
            ];

            Log::info('eSewa Transfer Payload', $payload);

            $payment = $this->esewa->directSingleTransaction($payload);

            Log::info('eSewa IBFT transaction successful', [
                'payment' => $payment
            ]);

            $notes   = json_decode($payment->notes, true);
            $message = $notes['esewa_status_message'] ?? 'Transaction processed.';

            if ($payment->status === 'failed') {
                return response()->json([
                    'success'    => false,
                    'message'    => $message,
                    'payment_id' => $payment->id,
                    'status'     => $payment->status,
                    'txn_ref'    => $payment->transaction_reference,
                ], 400);
            }

            if ($payment->status === 'pending') {
                return response()->json([
                    'success'    => true,
                    'message'    => $message,
                    'payment_id' => $payment->id,
                    'status'     => $payment->status,
                    'txn_ref'    => $payment->transaction_reference,
                ]);
            }


            return response()->json([
                'success'    => true,
                'message'    => $message ?? 'Transfer initiated successfully.',
                'payment_id' => $payment->id,
                'status'     => $payment->status,
                'txn_ref'    => $payment->transaction_reference,
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

    public function getTransactionStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'unique_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $this->esewa->getTransactionStatus($request->unique_id);

            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get eSewa transaction status', [
                'message'   => $e->getMessage(),
                'unique_id' => $request->unique_id,
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getTransactionReport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'from_date'        => 'required|date_format:Y-m-d',
            'to_date'          => 'required|date_format:Y-m-d|after_or_equal:from_date',
            'transaction_code' => 'nullable|string',
            'unique_id'        => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $this->esewa->getTransactionReport(
                $request->from_date,
                $request->to_date,
                $request->transaction_code ?? '',
                $request->unique_id        ?? '',
            );

            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get eSewa transaction report', [
                'message'   => $e->getMessage(),
                'from_date' => $request->from_date,
                'to_date'   => $request->to_date,
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    public function transferDashboard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attendance_id'       => 'required|integer|exists:attendance,id',
            'crew_bank_detail_id' => 'required|integer|exists:crew_bank_details,id',
            'remarks'             => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {

            // Find attendance
            $attendance = Attendance::findOrFail($request->attendance_id);

            // Find selected bank detail
            $bankDetail = CrewBankDetail::where('id', $request->crew_bank_detail_id)
                ->where('crew_id', $attendance->crew_id)
                ->first();

            if (!$bankDetail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid crew bank detail selected.'
                ], 400);
            }

            // Amount from attendance allowances
            $amount = $attendance->allowances;

            if ($amount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid allowance amount.'
                ], 400);
            }

            $payload = [

                // Source
                'source_bank_code'      => 'PRVUNPKA',
                'source_account_number' => '9100100008977000001',
                'source_account_name'   => 'Test CE',

                // Destination
                'destination_bank_code'      => $bankDetail->bank_code,
                'destination_account_number' => $bankDetail->account_number,
                'destination_account_name'   => $bankDetail->account_holder_name,

                // Amount
                'amount' => $amount,

                // Meta
                'remarks'        => $request->remarks ?? 'Allowance Payment',
                'narration_one'  => $request->narration_one ?? '',
                'narration_two'  => $request->narration_two ?? '',

                // Linkage
                'vehicle_booking_id' => $attendance->booking_id,
                'attendance_id' => $request->attendance_id,
                'payment_type' => 'attendance',
                'crew_id'            => $attendance->crew_id,
                'created_user_type'  => 'user',
            ];

            Log::info('Dashboard Transfer Payload', $payload);

            $payment = $this->esewa->directSingleTransaction($payload);

            Log::info('Dashboard transfer successful', [
                'payment' => $payment
            ]);

            $notes = json_decode($payment->notes, true);

            $message = $notes['esewa_status_message']
                ?? 'Transaction processed.';

            if ($payment->status === 'failed') {

                return response()->json([
                    'success'    => false,
                    'message'    => $message,
                    'payment_id' => $payment->id,
                    'status'     => $payment->status,
                    'txn_ref'    => $payment->transaction_reference,
                ], 400);
            }

            return response()->json([
                'success'    => true,
                'message'    => $message,
                'payment_id' => $payment->id,
                'status'     => $payment->status,
                'txn_ref'    => $payment->transaction_reference,
            ]);
        } catch (Exception $e) {

            Log::error('Dashboard transfer failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process transfer.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    public function validateBankAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_detail_id'      => 'required|exists:crew_bank_details,id',
            'account_number'      => 'required|string',
            'swift_code'          => 'required|string',
            'account_holder_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Call your existing validation method
            $response = $this->esewa->validateAccount(
                $request->account_number,
                $request->swift_code,
                $request->account_holder_name ?? ''
            );

            // Check if validation was successful based on the response structure
            $isValidated = false;
            $validationMessage = '';
            $validationData = null;

            // Check the response structure
            if (isset($response['Data']['ibft_corporate_account_validation_response'])) {
                $validationResponse = $response['Data']['ibft_corporate_account_validation_response'];

                // Success code is "0" (string) according to your response
                if ($validationResponse['code'] === '0' || $validationResponse['code'] == 0) {
                    $isValidated = true;
                    $validationMessage = $validationResponse['message'] ?? 'Validation successful';
                    $validationData = $validationResponse;
                } else {
                    $validationMessage = $validationResponse['message'] ?? 'Validation failed';
                }
            } else {
                $validationMessage = 'Invalid response format from validation service';
            }

            // If validation successful, update the database
            if ($isValidated) {
                $bankDetail = CrewBankDetail::find($request->bank_detail_id);
                $bankDetail->is_verified = true; // or is_validated based on your column name
                $bankDetail->save();

                return response()->json([
                    'success' => true,
                    'data' => $validationData,
                    'message' => $validationMessage
                ]);
            } else {
                // Validation failed
                return response()->json([
                    'success' => false,
                    'message' => $validationMessage
                ], 400);
            }
        } catch (Exception $e) {
            Log::error('Failed to validate account: ' . $e->getMessage() . ' file ' . $e->getFile() . ' line ' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during validation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBankDetails(Request $request)
    {
        $crew_id = $request->crew_id;

        $bankdetails = CrewBankDetail::where('is_active', 1)
            ->where('crew_id', $crew_id)
            ->get([
                'id',
                'bank_name',
                'bank_code',
                'account_holder_name',
                'account_number',
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Bank details fetched successfully',
            'data' => $bankdetails
        ]);
    }
}
