<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Attendance;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class EsewaIbftService
{
    protected string $authBaseUrl;
    protected string $baseUrl;
    protected string $hmacKey;
    protected string $clientId;
    protected string $username;
    protected string $password;
    protected string $certPath;
    protected string $certPassword;

    public function __construct()
    {
        $url = url('/');
        Log::info("current url", ["url" => $url]);
        if ($url == 'https://rentalsandbox.kathmandusightseeing.com') {
            $auth_base_url = 'https://ceapi-uat.esewa.com.np';
            $base_url = 'https://ceapi-uat.esewa.com.np';
            $hmac_key = 'esewa';
            $client_id = 'CP0002006';
            $user_name = 'esewadirect';
            $pass_word = 'Hello@123';
            $cert_path = "C:/xampp/htdocs/vehicle-rental-mis-sandbox/certs/esewa.pem";
            $cert_password = 'Test@123';
        } else {
            $auth_base_url = 'https://corporate-authentication.esewa.com.np';
            $base_url = 'https://corporateapi.esewa.com.np';
            $hmac_key = 'ZXNld2Fjb3';
            $client_id = 'CP0039005';
            $user_name = 'allen@unlimitedremit.com';
            $pass_word = 'Bailochanapi@123#';
            $cert_path = "C:/xampp/htdocs/vehicle-rental-mis/certs/esewa.pem";
            $cert_password = '(nI$V$%6';
        }
        $this->authBaseUrl = env('ESEWA_AUTH_BASE_URL') ?? $auth_base_url;
        $this->baseUrl = env('ESEWA_BASE_URL') ?? $base_url;
        $this->hmacKey = env('ESEWA_HMAC_KEY') ?? $hmac_key;
        $this->clientId = env('ESEWA_CLIENT_ID') ?? $client_id;
        $this->username = env('ESEWA_USERNAME') ?? $user_name;
        $this->password = env('ESEWA_PASSWORD') ?? $pass_word;
        $this->certPath = env('ESEWA_CERT_PATH') ?? $cert_path;
        $this->certPassword = env('ESEWA_CERT_PASSWORD') ?? $cert_password;
    }

    // ─────────────────────────────────────────────────────────────
    // 1. TOKEN
    // ─────────────────────────────────────────────────────────────
    public function getAccessToken(): string
    {
        return $this->fetchNewToken();
    }

    protected function fetchNewToken()
    {
        Log::info('eSewa token request', [
            'url'       => $this->authBaseUrl . '/api/auth/v1/token',
            'client_id' => $this->clientId,
            // "client_secret" => $this->hmacKey,
            'username'  => $this->username,
            'password' => $this->password,
            // 'cert_path' => $this->certPath,
            // 'cert_exists' => file_exists($this->certPath),
        ]);

        try {

            $response = $this->makeRequest('POST', $this->authBaseUrl . '/api/auth/v1/token', [
                'client_id' => $this->clientId,
                // "client_secret" => $this->hmacKey,
                'username' => $this->username,
                'password' => $this->password,
                // 'grant_type' => 'password',

            ], withAuth: false);

            $token = $response['data']['token'] ?? $response['Data']['token'] ?? null;
            dd($token);

            if (empty($token)) {
                throw new Exception('eSewa token response missing access_token: ' . json_encode($response));
            }

            Log::info('eSewa: new access token fetched', ['client_id' => $this->clientId]);

            return $token;
        } catch (\Exception $e) {
            Log::error("esewaibft fetch token error", [
                "file" => $e->getFile(),
                "line" => $e->getLine(),
                "message" => $e->getMessage(),
            ]);
            throw new Exception('eSewa token response missing access_token: ');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 2. GET AVAILABLE BANKS
    // ─────────────────────────────────────────────────────────────


    public function getAvailableBanks(): array
    {
        $response = $this->makeRequest(
            'GET',
            $this->baseUrl . '/api/fonegateway/ibft/v1/get_available_bank'
        );

        if (($response['Code'] ?? '') !== '0000') {
            throw new Exception(
                'eSewa get_available_bank failed: ' . ($response['Message'] ?? 'Unknown error')
            );
        }

        return $response['Data'];
    }

    // ─────────────────────────────────────────────────────────────
    // 3. VALIDATE ACCOUNT
    // ─────────────────────────────────────────────────────────────

    public function validateAccount(string $accountNumber, string $swiftCode, string $accountHolderName = ''): array
    {
        // Correct HMAC order: swift_code, account_number, account_holder_name
        $identityString = $this->generateIdentityString($swiftCode, $accountNumber, $accountHolderName);

        $payload = [
            'identity_string'     => $identityString,
            'account_number'      => $accountNumber,
            'account_holder_name' => $accountHolderName,
            'swift_code'          => $swiftCode,
        ];

        $response = $this->makeRequest(
            'POST',
            $this->baseUrl . '/api/fonegateway/ibft/v1/validate_account',
            $payload
        );

        if (($response['Code'] ?? '') !== '0000') {
            throw new Exception('eSewa account validation failed: ' . ($response['Message'] ?? 'Unknown error'));
        }

        return $response['Data']['ibft_corporate_account_validation_response'];
    }
    // ─────────────────────────────────────────────────────────────
    // 4. DIRECT SINGLE TRANSACTION
    // ─────────────────────────────────────────────────────────────
    public function directSingleTransaction(array $params): Payment
    {
        $uniqueId = $params['unique_id'] ?? $this->generateUniqueId();
        $identityString = $this->generateIdentityString(
            $params['source_account_number'],
            $params['destination_account_number'],
            $params['destination_account_name']
        );

        $payload = [
            'client_id' => $this->clientId,
            'source_bank_code' => $params['source_bank_code'],
            'source_account_number' => $params['source_account_number'],
            'source_account_name' => $params['source_account_name'],
            'destination_bank_code' => $params['destination_bank_code'],
            'destination_account_number' => $params['destination_account_number'],
            'destination_account_name' => $params['destination_account_name'],
            'amount' => number_format((float) $params['amount'], 2, '.', ''),
            'remarks' => $params['remarks'] ?? '',
            'narration_one' => $params['narration_one'] ?? '',
            'narration_two' => $params['narration_two'] ?? '',
            'unique_id' => $uniqueId,
            'identity_string' => $identityString,
        ];

        Log::info('eSewa IBFT: initiating transaction', [
            'unique_id' => $uniqueId,
            'amount' => $payload['amount'],
            'dest_bank' => $params['destination_bank_code'],
        ]);

        $response = $this->makeRequest(
            'POST',
            $this->baseUrl . '/api/fonegateway/ibft/v1/transaction/direct_single_transaction',
            $payload
        );
        Log::info('eSewa IBFT: transaction response received', [
            'unique_id' => $uniqueId,
            'response' => $response,
            'response_code' => $response['Code'] ?? null,
            'response_message' => $response['Message'] ?? null,
        ]);

        return $this->persistPayment($response, $params, $uniqueId, $payload);
    }

    // ─────────────────────────────────────────────────────────────
    // 5. PERSIST PAYMENT
    // ─────────────────────────────────────────────────────────────

    protected function persistPayment(array $response, array $params, string $uniqueId, array $payload): Payment
    {
        $txn      = $response['Data']['ibft_corporate_transaction_response'] ?? [];
        $code     = $response['Code'] ?? 'XXXX';
        $mapped   = $this->mapTransactionStatus($txn['status'] ?? '', $code);

        $payment = Payment::create([
            'vehicle_booking_id'    => $params['vehicle_booking_id']  ?? null,
            'crew_id'               => $params['crew_id']             ?? null,
            'attendance_id'         => $params['attendance_id']       ?? null,
            'amount'                => $payload['amount'],
            'payment_method'        => 'bank_transfer',
            'gateway' => "bank",
            'payment_type'          => $params['payment_type']        ?? 'booking',
            'transaction_reference' => $txn['transaction_code']       ?? $uniqueId,
            'payment_date'          => now(),
            'unique_id'              => $uniqueId,
            'status'                => $mapped['status'],
            'notes'                 => json_encode([
                'unique_id'                  => $uniqueId,
                'request_id'                 => $txn['request_id']                  ?? null,
                'esewa_status'               => $txn['status']                      ?? null,
                'esewa_status_message'       => $mapped['message'],
                'source_account'             => $txn['source_account']              ?? null,
                'destination_account'        => $txn['destination_account']         ?? null,
                'source_account_status'      => $txn['source_account_status']       ?? null,
                'destination_account_status' => $txn['destination_account_status']  ?? null,
                'transaction_date'           => $txn['transaction_date']            ?? null,
                'message'                    => $response['Message']                ?? null,
                'error_message'              => $txn['error_message']               ?? null,
                'raw_response_code'          => $code,
                'destination_bank_code'      => $params['destination_bank_code'],
                'destination_account_name'   => $params['destination_account_name'],
            ]),
            'created_by'            => auth()->id(),
            'created_user_type'     => $params['created_user_type']   ?? 'system',
        ]);



        if (
            ($params['payment_type'] ?? null) === 'attendance' &&
            !empty($params['attendance_id']) &&
            $mapped['status'] === 'completed'
        ) {
            Attendance::where('id', $params['attendance_id'])
                ->update([
                    'payment_status' => 'paid',
                    'payment_remarks' => 'IBFT Bank Pay',
                    'remarks' => $params['remarks'] ?? null,
                ]);
        }

        Log::info('eSewa IBFT: payment saved', [
            'payment_id' => $payment->id,
            'status'     => $mapped['status'],
            'message'    => $mapped['message'],
            'txn_code'   => $txn['transaction_code'] ?? null,
        ]);

        return $payment;
    }





    public function getTransactionStatus(string $uniqueId)
    {
        $identityString = $this->generateIdentityString($uniqueId, $this->clientId);

        $payload = [
            'identity_string' => $identityString,
            'unique_id'       => $uniqueId,
            'client_id'       => $this->clientId,
        ];

        $response = $this->makeRequest(
            'POST',
            $this->baseUrl . '/api/fonegateway/ibft/v1/transaction/get_direct_transaction_status_by_client_id_and_unique_id',
            $payload
        );

        if (($response['Code'] ?? '') !== '0000') {
            throw new Exception('eSewa get transaction status failed: ' . ($response['Message'] ?? 'Unknown error'));
        }

        return $response['Data'];
    }




    public function getTransactionReport(string $fromDate, string $toDate, string $transactionCode = '', string $uniqueId = '')
    {
        $identityString = $this->generateIdentityString($this->clientId, $fromDate, $toDate);

        $payload = [
            'client_id'            => $this->clientId,
            'transaction_from_date' => $fromDate,
            'transaction_to_date'  => $toDate,
            'transaction_code'     => $transactionCode,
            'batch_id'             => '',
            'unique_id'            => $uniqueId,
            'identity_string'      => $identityString,
        ];

        $response = $this->makeRequest(
            'POST',
            $this->baseUrl . '/api/fonegateway/ibft/v1/transaction_report',
            $payload
        );

        if (($response['Code'] ?? '') !== '0000') {
            throw new Exception('eSewa transaction report failed: ' . ($response['Message'] ?? 'Unknown error'));
        }

        return $response['Data'];
    }


    // ─────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────


    public function generateIdentityString(string ...$fields): string
    {
        $message = implode(',', $fields);
        return hash_hmac('sha256', $message, $this->hmacKey);
    }

    public function generateIdentityStringT(string ...$fields): string
    {
        $message = implode('', $fields);

        // Use plain sha256, NOT hmac
        return hash('sha256', $message);
    }

    protected function generateUniqueId(): string
    {
        return strtoupper(uniqid('TXN', true));
    }

    protected function mapTransactionStatus(string $esewaStatus, string $code): array
    {
        $messages = [
            'RESP000' => 'Transaction completed successfully.',
            'RESP010' => 'Transaction failed due to insufficient balance.',
            'RESP011' => 'Invalid source or destination account.',
            'RESP012' => 'Account is blocked or inactive.',
            'RESP013' => 'Transaction limit exceeded.',
            'RESP014' => 'Duplicate transaction.',
            'PENDING'    => 'Transaction is pending.',
            'PROCESSING' => 'Transaction is being processed.',
        ];

        if ($code === '0000' && $esewaStatus === 'RESP000') {
            return ['status' => 'completed', 'message' => $messages['RESP000']];
        }
        if (in_array($esewaStatus, ['PENDING', 'PROCESSING'])) {
            return ['status' => 'pending', 'message' => $messages[$esewaStatus]];
        }

        return [
            'status'  => 'failed',
            'message' => $messages[$esewaStatus] ?? 'Transaction failed. Please try again.',
        ];
    }

    /**
     * Central HTTP client with certificate + auth header.
     */
    protected function makeRequest(string $method, string $url, array $body = [], bool $withAuth = true): array
    {
        // Use array only if password exists, otherwise single string
        $certOption = empty($this->certPassword)
            ? $this->certPath
            : [$this->certPath, $this->certPassword];

        $client = Http::withOptions([
            'cert'   => $certOption,
            'verify' => true,
        ])->timeout(30);

        if ($withAuth) {
            $client = $client->withToken($this->getAccessToken());
        }

        $response = match (strtoupper($method)) {
            'GET'   => $client->get($url, $body),
            'POST'  => $client->post($url, $body),
            default => throw new Exception("Unsupported HTTP method: $method"),
        };

        if ($response->failed()) {
            Log::error('eSewa API error', [
                'url'    => $url,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new Exception("eSewa API call failed [{$response->status()}]: " . $response->body());
        }

        return $response->json();
    }
}
