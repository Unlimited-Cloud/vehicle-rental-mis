<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Mail\LoginOtpMail;
use App\Models\Passcode;
use App\Models\PasscodeSetup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
// use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    // use AuthenticatesUsers;

    // /**
    //  * Where to redirect users after login.
    //  *
    //  * @var string
    //  */
    // protected $redirectTo = '/dashboard';

    // /**
    //  * Create a new controller instance.
    //  *
    //  * @return void
    //  */
    // public function __construct()
    // {
    //     $this->middleware('guest')->except('logout');
    //     $this->middleware('auth')->only('logout');
    // }


    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Check if user exists with these credentials
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'Invalid credentials'
            ])->withInput();
        }

        // Check OTP request limits
        $setup = PasscodeSetup::firstOrFail();
        $windowStart = now()->subMinutes($setup->window_minutes);

        $recent = Passcode::where('email', $user->email)
            ->where('requested_at', '>=', $windowStart)
            ->latest()
            ->first();

        $requestCount = $recent ? $recent->request_count + 1 : 1;

        if ($requestCount > $setup->max_requests) {
            return back()->withErrors([
                'otp' => 'OTP request limit reached. Try again later.'
            ]);
        }

        // Generate and save OTP
        $otp = random_int(100000, 999999);

        Passcode::create([
            'user_id'       => $user->id,
            'email'         => $user->email,
            'passcode'      => $otp,
            'requested_at'  => now(),
            'request_count' => $requestCount,
            'attempt_count' => 0,
            'locked_until'  => null,
        ]);

        // Send OTP email
        Mail::to($user->email)->send(new LoginOtpMail($otp, $user->name));

        // Store email in session and redirect back to login page
        session(['otp_email' => $user->email]);

        return redirect()->route('login')->with('success', 'OTP sent to your email.');
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
