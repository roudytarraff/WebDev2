<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Mail\TwoFactorOtpMail;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\TwoFactorAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /* ================= LOGIN VIEW ================= */
    public function login()
    {
        return view('auth.login');
    }

    /* ================= REGISTER VIEW ================= */
    public function register()
    {
        return view('auth.register');
    }

    /* ================= LOGIN ================= */
    public function connect(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors(['email' => 'Invalid credentials']);
        }

        /** @var User $user */
        $user = Auth::user();
        Auth::logout();

        return $this->sendOtp($user);
    }

    /* ================= REGISTER ================= */
    public function create(RegisterRequest $request)
    {
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'password'   => Hash::make($request->password),
            'status'     => 'pending'
        ]);

        return $this->sendOtp($user);
    }

    /* ================= GOOGLE LOGIN ================= */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')
            ->stateless()
            ->user();

        $account = SocialAccount::where('provider', 'google')
            ->where('provider_user_id', $googleUser->id)
            ->first();

        if ($account) {
            $user = $account->user;
        } else {

            $user = User::firstOrCreate(
                ['email' => $googleUser->email],
                [
                    'first_name' => $googleUser->name,
                    'last_name'  => '',
                    'phone'      => null,
                    'password'   => Hash::make(Str::random(16)),
                    'status'     => 'pending'
                ]
            );

            SocialAccount::create([
                'user_id' => $user->id,
                'provider' => 'google',
                'provider_user_id' => $googleUser->id,
                'provider_email' => $googleUser->email
            ]);
        }

        Auth::logout();

        return $this->sendOtp($user);
    }

    /* ================= SEND OTP ================= */
    private function sendOtp(User $user)
    {
        $otp = rand(100000, 999999);

        TwoFactorAuth::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10)
        ]);

        try {
            Mail::to($user->email)->send(new TwoFactorOtpMail($otp));
        } catch (\Throwable $exception) {
            Log::warning('OTP email could not be sent: '.$exception->getMessage());
        }

        session([
            'otp_user_id' => $user->id,
            'otp_verified' => false,
            'demo_otp' => $otp,
        ]);

        return redirect()->route('otp.form');
    }

    /* ================= OTP FORM ================= */
    public function otpForm()
    {
        return view('auth.otp');
    }

    /* ================= VERIFY OTP ================= */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        $userId = session('otp_user_id');

        $otp = TwoFactorAuth::where('user_id', $userId)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp || !Hash::check($request->otp, $otp->otp_hash)) {
            return back()->withErrors(['otp' => 'Invalid OTP']);
        }

        Auth::loginUsingId($userId, true);

        session([
            'otp_verified' => true
        ]);

        session()->forget('otp_user_id');
        session()->forget('demo_otp');

        /** @var User $authenticatedUser */
        $authenticatedUser = Auth::user();

        if ($authenticatedUser->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($authenticatedUser->isOfficeStaff()) {
            return redirect()->route('office.dashboard');
        }

        return redirect()->route('home');
    }

    /* ================= LOGOUT ================= */
    public function logout()
    {
        Auth::logout();
        session()->flush();

        return redirect()->route('auth.login');
    }
}
