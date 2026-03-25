<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Passcode;
use App\Services\CustomerService;
use App\Utilities\VehicleRentalUtilities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CustomerController extends Controller
{
    protected $customerService;

    public function __construct(
        CustomerService $customerService
    ) {
        $this->customerService = $customerService;
    }
    public function register(Request $request)
    {
        $data = $this->customerService->saveCustomer($request);
        return VehicleRentalUtilities::jsonResponse($data);
    }

    public function getProfileByUuid($uuid)
    {
        $data = $this->customerService->getProfileByUuid($uuid);
        return VehicleRentalUtilities::jsonResponse($data);
    }


    public function login(Request $request)
    {
        $customer = Customer::where('email', $request->email)->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Credentials'
            ], 401);
        }

        $token = $customer->createToken('customer-token')->plainTextToken;

        return response()->json([
            'status' => true,
            'token' => $token,
            'customer' => $customer
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ]);
    }


    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:customers,email'
        ]);

        $otp = rand(100000, 999999);

        $passcode = Passcode::where('email', $request->email)->first();

        $customerId = Customer::where('email', $request->email)->first()->id;

        if ($passcode) {
            // Rate limit: max 5 requests
            if ($passcode->request_count >= 5 && now()->diffInMinutes($passcode->requested_at) < 10) {
                return response()->json([
                    'status' => false,
                    'message' => 'Too many requests. Try again later.'
                ], 429);
            }

            $passcode->update([
                'passcode' => $otp,
                'user_id' => $customerId,
                'requested_at' => now(),
                'request_count' => $passcode->request_count + 1,
                'attempt_count' => 0,
                'locked_until' => null
            ]);
        } else {
            Passcode::create([
                'email' => $request->email,
                'user_id' => $customerId,
                'passcode' => $otp,
                'requested_at' => now(),
                'request_count' => 1,
                'attempt_count' => 0
            ]);
        }

        Mail::raw("Your OTP is: $otp", function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Password Reset OTP');
        });

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully'
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:customers,email',
            'otp' => 'required'
        ]);

        $passcode = Passcode::where('email', $request->email)->first();

        if (!$passcode) {
            return response()->json([
                'status' => false,
                'message' => 'OTP not found'
            ], 400);
        }

        // Check lock
        if ($passcode->locked_until && now()->lessThan($passcode->locked_until)) {
            return response()->json([
                'status' => false,
                'message' => 'Too many attempts. Try later.'
            ], 423);
        }

        // Expiry (10 mins)
        if (now()->diffInMinutes($passcode->requested_at) > 10) {
            return response()->json([
                'status' => false,
                'message' => 'OTP expired'
            ], 400);
        }

        // Check OTP
        if ($passcode->passcode != $request->otp) {

            $attempts = $passcode->attempt_count + 1;

            $updateData = ['attempt_count' => $attempts];

            // Lock after 5 attempts
            if ($attempts >= 5) {
                $updateData['locked_until'] = now()->addMinutes(10);
            }

            $passcode->update($updateData);

            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP'
            ], 400);
        }

        // Reset attempts on success
        $passcode->update([
            'attempt_count' => 0,
            'locked_until' => null
        ]);

        return response()->json([
            'status' => true,
            'message' => 'OTP verified successfully'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:customers,email',
            'otp' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);

        $passcode = Passcode::where('email', $request->email)->first();

        if (!$passcode || $passcode->passcode != $request->otp) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP'
            ], 400);
        }

        // Expiry check
        if (now()->diffInMinutes($passcode->requested_at) > 10) {
            return response()->json([
                'status' => false,
                'message' => 'OTP expired'
            ], 400);
        }

        Customer::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        // Delete passcode after use
        $passcode->delete();

        return response()->json([
            'status' => true,
            'message' => 'Password reset successful'
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:customers,email',
            'password' => 'required|min:6|confirmed'
        ]);

        // Get token from Bearer header
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status' => false,
                'message' => 'Token not provided'
            ], 401);
        }


        // Update password
        Customer::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Password reset successful'
        ]);
    }
}
