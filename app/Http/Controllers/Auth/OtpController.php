<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Passcode;
use App\Models\PasscodeSetup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\LoginOtpMail;
use App\Models\User;

class OtpController extends Controller
{
    public function resend(Request $request)
    {
        $email = session('otp_email');

        if (!$email) {
            return redirect()->route('login')
                ->withErrors(['otp' => 'Session expired. Please login again.']);
        }

        $setup = PasscodeSetup::firstOrFail();

        $last = Passcode::where('email', $email)->latest()->first();

        if ($last && $last->locked_until && now()->lt($last->locked_until)) {
            return back()->withErrors([
                'otp' => 'Too many attempts. Try again later.'
            ]);
        }

        $windowStart = now()->subMinutes($setup->window_minutes);

        $recent = Passcode::where('email', $email)
            ->where('requested_at', '>=', $windowStart)
            ->latest()
            ->first();

        $requestCount = $recent ? $recent->request_count + 1 : 1;

        if ($requestCount > $setup->max_requests) {
            return back()->withErrors([
                'otp' => 'OTP resend limit reached. Try again later.'
            ]);
        }

        $otp = random_int(100000, 999999);

        Passcode::create([
            'user_id'       => $last?->user_id,
            'email'         => $email,
            'passcode'      => $otp,
            'requested_at'  => now(),
            'request_count' => $requestCount,
            'attempt_count' => 0,
            'locked_until'  => null,
        ]);

        Mail::to($email)->send(new LoginOtpMail($otp));

        return back()->with('success', 'New OTP sent to your email.');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6'
        ]);

        $email = session('otp_email');

        if (!$email) {
            return redirect()->route('otp.show')
                ->withErrors(['otp' => 'Session expired. Request OTP again.']);
        }

        $setup = PasscodeSetup::firstOrFail();

        $passcode = Passcode::where('email', $email)
            ->latest()
            ->firstOrFail();

        if ($passcode->locked_until && now()->lt($passcode->locked_until)) {
            return back()->withErrors([
                'otp' => 'Account temporarily locked. Try later.'
            ]);
        }

        if (now()->diffInMinutes($passcode->requested_at) > $setup->otp_valid_minutes) {
            return back()->withErrors([
                'otp' => 'OTP expired. Request again.'
            ]);
        }

        if ($passcode->attempt_count + 1 >= $setup->max_attempts) {
            $passcode->update([
                'attempt_count' => $passcode->attempt_count + 1,
                'locked_until'  => now()->addMinutes($setup->window_minutes),
            ]);

            return back()->withErrors([
                'otp' => 'Too many wrong attempts. Try again later.'
            ]);
        }

        if (!hash_equals((string) $passcode->passcode, $request->otp)) {
            $passcode->increment('attempt_count');
            return back()->withErrors([
                'otp' => 'Invalid OTP.'
            ]);
        }

        Auth::loginUsingId($passcode->user_id);
        $userDetail = User::where('id',$passcode->user_id)->first();
        session(['user' => $userDetail]);

        $passcode->update([
            'request_count' => 0,
        ]);

        session()->forget('otp_email');

        return redirect()->route('dashboard');
    }

    public function show()
    {
        $email = session('otp_email');

        if (!$email) {
            return redirect()->route('login')
                ->withErrors(['otp' => 'Session expired. Please login again.']);
        }

        return view('auth.login'); // or create a dedicated OTP view if you prefer
    }
}
