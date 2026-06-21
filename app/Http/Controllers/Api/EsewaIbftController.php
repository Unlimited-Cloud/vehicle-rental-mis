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
use App\Models\Bank;
use App\Models\CrewBankDetail;
use App\Models\CrewProfile;
use App\Models\Agent;
use App\Models\CommissionStatement;
use App\Models\Payment;
use App\Models\VehicleBooking;
use App\Models\VehicleOwner;
use App\Helpers\NepaliDateHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;

class EsewaIbftController extends Controller
{
    public function __construct(protected EsewaIbftService $esewa) {}

    public function getBanks()
    {
        try {

            $banks = $this->esewa->getAvailableBanks();

            foreach ($banks as $bank) {

                Bank::updateOrCreate(
                    [
                        'bank_name'  => $bank['bank_name'],
                        'swift_code' => $bank['swift_code'],
                    ],
                    [
                        'normalized_name'   => $bank['normalized_name'] ?? strtoupper($bank['bank_name']),
                        'bank_code'         => $bank['bank_code'],
                        'configuration_id'  => $bank['configuration_id'],
                        'is_source_account' => $bank['source_account'],
                        'is_payee_account'  => $bank['payee_account'],
                        'encrypted_id'      => $bank['encrypted_id'],
                    ]
                );
            }

            return response()->json([
                'success'      => true,
                'all_banks'    => Bank::all(),
                'source_banks' => Bank::where('is_source_account', true)->get(),
                'payee_banks'  => Bank::where('is_payee_account', true)->get(),
            ]);
        } catch (Exception $e) {

            Log::error(
                'Failed to fetch eSewa banks: '
                    . $e->getMessage()
                    . ' file ' . $e->getFile()
                    . ' line ' . $e->getLine()
            );

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



    public function getCommissionDetails($agentCode)
    {
        $agent = Agent::with('user')
            ->where('agent_code', $agentCode)
            ->first();

        if (!$agent) {
            return response()->json(['success' => false, 'message' => 'Agent not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'agent_name'         => $agent->user->name ?? 'N/A',
                'agent_code'         => $agent->agent_code,
                'commission_rate'    => $agent->commission_rate ?? 0,
                'has_bank_details'   => !empty($agent->bank_account_number) && !empty($agent->bank_name),
                'bank_name'          => $agent->bank_name,
                'bank_account_name'  => $agent->bank_account_name,
                'bank_account_number' => $agent->bank_account_number,
                'bank_code'          => $agent->bank_code ?? null,
                'wallet_name'        => $agent->wallet_name,
                'wallet_number'      => $agent->wallet_number,
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // TRANSFER COMMISSION (Pay by Bank)
    // -------------------------------------------------------------------------

    public function transferAgentsDashboard(Request $request)
    {
        // Gate::authorize('update_agents');

        $validator = Validator::make($request->all(), [
            'agent_code' => 'required|string|exists:agents,agent_code',
            'booking_id' => 'required|integer|exists:vehicle_bookings,id',
            'remarks'    => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $agent = Agent::where('agent_code', $request->agent_code)->first();

            if (!$agent->bank_account_number || !$agent->bank_name) {
                return response()->json([
                    'success' => false,
                    'message' => 'Agent does not have bank details configured.',
                ], 400);
            }

            $booking          = VehicleBooking::findOrFail($request->booking_id);
            $discountAmount = 0;
            $baseAmount = !empty($booking->sub_total)
                ? $booking->sub_total
                : $booking->rate_per_day;
            if ($booking->discount > 0) {
                if ($booking->discount_amount_type === 'percentage') {
                    $discountAmount = ($baseAmount * $booking->discount) / 100;
                } else {
                    $discountAmount = $booking->discount;
                }
            }

            // Net amount before VAT
            $commissionBase = $baseAmount - $discountAmount;

            $commissionAmount = ($commissionBase * $agent->commission_rate) / 100;

            if ($commissionAmount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Commission amount is zero. Check the agent commission rate.',
                ], 400);
            }

            $payload = [
                'source_bank_code'           => 'PRVUNPKA',
                'source_account_number'      => '9100100008977000001',
                'source_account_name'        => 'Test CE',
                'destination_bank_code'      => $agent->bank_code,
                'destination_account_number' => $agent->bank_account_number,
                'destination_account_name'   => $agent->bank_account_name,
                'amount'                     => $commissionAmount,
                'remarks'                    => $request->remarks ?? "Commission payment for booking #{$booking->id}",
                'narration_one'              => "Agent: {$agent->agent_code}",
                'narration_two'              => "Booking: {$booking->file_no}",
                'vehicle_booking_id'         => $booking->id,
                'agent_code'                 => $agent->agent_code,
                'payment_type'               => 'agent_commission',
                'payment_method'             => 'bank_transfer',
                'created_user_type'          => 'user',
            ];

            Log::info('Agent Commission Transfer Payload', $payload);
            $statement = $this->createAgentCommissionStatementRecord($booking, $agent, '2', $request->remarks);

            $payment = $this->esewa->directSingleTransaction($payload);

            Log::info('Agent Commission Transfer Successful', ['payment' => $payment]);

            $notes   = json_decode($payment->notes, true);
            $message = $notes['esewa_status_message'] ?? 'Transaction processed successfully.';

            if ($payment->status === 'failed') {
                return response()->json([
                    'success'    => false,
                    'message'    => $message,
                    'payment_id' => $payment->id,
                    'status'     => $payment->status,
                    'txn_ref'    => $payment->transaction_reference,
                ], 400);
            }

            // $statement = $this->createAgentCommissionStatementRecord($booking, $agent, $payment->id, $request->remarks);

            return response()->json([
                'success'    => true,
                'message'    => $message,
                'payment_id' => $payment->id,
                'status'     => $payment->status,
                'txn_ref'    => $payment->transaction_reference,
            ]);
        } catch (Exception $e) {
            Log::error('Agent Commission Transfer Failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process transfer: ' . $e->getMessage(),
            ], 500);
        }
    }


    protected function createAgentCommissionStatementRecord($booking, $agent, $paymentId, $remarks = null)
    {
        $existing = CommissionStatement::where('vehicle_booking_id', $booking->id)->first();
        if ($existing) {
            return $existing;
        }

        $baseAmount = (!$booking->sub_total || (float) $booking->sub_total == 0)
            ? $booking->rate_per_day
            : $booking->sub_total;

        $discountAmount = 0;
        if ($booking->discount > 0) {
            $discountAmount = $booking->discount_amount_type === 'percentage'
                ? ($baseAmount * $booking->discount) / 100
                : $booking->discount;
        }

        $commissionBase   = max(0, $baseAmount - $discountAmount);
        $commissionRate   = (float) $agent->commission_rate;
        $commissionAmount = ($commissionBase * $commissionRate) / 100;


        $statement = CommissionStatement::create([
            'statement_number'      => 'CS-' . date('Ymd') . '-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT),
            'payee_type'            => 'agent',
            'payee_code'            => $agent->agent_code,
            'payee_id'              => $agent->id,
            'payment_id'            => $paymentId,
            'vehicle_booking_id'    => $booking->id,
            'period_start'          => $booking->start_date,
            'period_end'            => $booking->end_date ?? $booking->start_date,
            'booking_amount'        => $commissionBase,
            'commission_rate'       => $commissionRate,
            'commission_amount'     => $commissionAmount,
            'tds_rate'              => 0,
            'tds_amount'            => 0,
            'net_paid_amount'       => $commissionAmount,
            'payment_method'        => 'bank_transfer',
            'bank_name'             => $agent->bank_name,
            'bank_account_number'   => $agent->bank_account_number,
            'transaction_reference' => null,
            'payment_date'          => now(),
            'remarks'               => $remarks,
            'status'                => 'generated',
        ]);

        $this->renderAgentCommissionStatementPdf($statement);

        return $statement;
    }


    protected function renderAgentCommissionStatementPdf(CommissionStatement $statement)
    {

        $statement->load('booking.agent.user', 'booking.vehicle');

        $data = [
            'statement'     => $statement,
            'booking'       => $statement->booking,
            'agent'         => $statement->booking->agent ?? null,
            'invoice_date'  => now(),
            'miti_date'     => $this->convertToNepaliDate(now()),
            'printing_time' => now()->format('Y-m-d h:i A'),
        ];

        $pdf = Pdf::loadView('layouts.admin.invoices.commission-statement-pdf', $data);

        $pdf->setPaper('A4', 'portrait');

        // Create folder if it doesn't exist
        $folderPath = public_path('uploads/commission-statements');

        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0755, true);
        }

        $fileName = 'statement-' . $statement->statement_number . '.pdf';
        $fullPath = $folderPath . '/' . $fileName;

        $pdf->save($fullPath);

        $statement->update([
            'pdf_path' => 'uploads/commission-statements/' . $fileName
        ]);

        return view('layouts.admin.invoices.commission-statement-pdf', $data);
    }

    public function viewAgentCommissionStatement($bookingId)
    {
        $statement = CommissionStatement::where('vehicle_booking_id', $bookingId)
            ->firstOrFail();

        if (!$statement->pdf_path) {
            $this->renderAgentCommissionStatementPdf($statement);
            $statement->refresh();
        }

        return response()->download(
            public_path($statement->pdf_path),
            basename($statement->pdf_path)
        );
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




    public function getOwnerPaymentDetails($ownerId)
    {
        $owner = VehicleOwner::find($ownerId);

        if (!$owner) {
            return response()->json([
                'success' => false,
                'message' => 'Owner not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'owner_id'             => $owner->id,
                'owner_name'           => $owner->name,
                'commission_rate'      => $owner->commission_rate ?? 0,

                'has_bank_details'     => !empty($owner->bank_account_number)
                    && !empty($owner->bank_name),

                'bank_name'            => $owner->bank_name,
                'bank_account_name'    => $owner->bank_account_name,
                'bank_account_number'  => $owner->bank_account_number,
                'bank_code'            => $owner->bank_code,

                'wallet_name'          => $owner->wallet_name,
                'wallet_number'        => $owner->wallet_number,
            ]
        ]);
    }



    public function transferOwnersDashboard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'owner_id'   => 'required|exists:vehicle_owners,id',
            'booking_id' => 'required|exists:vehicle_bookings,id',
            'remarks'    => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            $owner = VehicleOwner::findOrFail($request->owner_id);

            if (
                empty($owner->bank_account_number) ||
                empty($owner->bank_name)
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Owner bank details not configured.'
                ], 400);
            }

            $booking = VehicleBooking::with('vehicle')
                ->findOrFail($request->booking_id);


            $baseAmount = !empty($booking->sub_total)
                ? $booking->sub_total
                : $booking->rate_per_day;

            $discountAmount = 0;

            if ($booking->discount > 0) {
                if ($booking->discount_amount_type == 'percentage') {
                    $discountAmount =
                        ($baseAmount * $booking->discount) / 100;
                } else {
                    $discountAmount = $booking->discount;
                }
            }

            $netAmount = max(
                0,
                $baseAmount - $discountAmount
            );

            $taxAmount = $booking->tax_amount ?? 0;

            $amountExcludingTax = max(
                0,
                $netAmount - $taxAmount
            );


            $agentCommission = 0;

            if (!empty($booking->agent_code)) {

                $agent = Agent::where(
                    'agent_code',
                    $booking->agent_code
                )->first();

                if ($agent) {
                    $agentCommission =
                        ($amountExcludingTax * $agent->commission_rate) / 100;
                }
            }


            $platformCommission =
                ($amountExcludingTax * $owner->commission_rate) / 100;


            $ownerPayable =
                $amountExcludingTax
                - $agentCommission
                - $platformCommission;

            if ($ownerPayable <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Owner payable amount is zero.'
                ], 400);
            }


            $alreadyPaid = Payment::where(
                'vehicle_booking_id',
                $booking->id
            )
                ->where('payment_type', 'owner_payout')
                ->where('status', 'completed')
                ->exists();

            if ($alreadyPaid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Owner payment already completed.'
                ], 400);
            }



            $payload = [
                'source_bank_code'           => 'PRVUNPKA',
                'source_account_number'      => '9100100008977000001',
                'source_account_name'        => 'Test CE',

                'destination_bank_code'      => $owner->bank_code,
                'destination_account_number' => $owner->bank_account_number,
                'destination_account_name'   => $owner->bank_account_name,

                'amount'                     => round($ownerPayable, 2),

                'remarks' => $request->remarks
                    ?? "Owner payout for booking #{$booking->id}",

                'narration_one' => "Owner ID: {$owner->id}",
                'narration_two' => "Booking: {$booking->file_no}",

                'vehicle_booking_id' => $booking->id,
                'vehicle_owner_id'   => $owner->id,

                'payment_type'       => 'owner_payout',
                'payment_method'     => 'bank_transfer',
                'created_user_type'  => 'user',
            ];

            Log::info('Owner Payout Payload', $payload);

            $payment = $this->esewa
                ->directSingleTransaction($payload);

            $notes = json_decode($payment->notes, true);

            $message =
                $notes['esewa_status_message']
                ?? 'Transaction processed successfully';

            if ($payment->status === 'failed') {

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'payment_id' => $payment->id,
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'txn_ref' => $payment->transaction_reference,
            ]);
        } catch (Exception $e) {

            Log::error('Owner Payout Failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
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


    public function validateAllBankAccount(Request $request)
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
