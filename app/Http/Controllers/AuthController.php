<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SocialAccount;
use App\Models\TwoFactorAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\TwoFactorOtpMail;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

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
    public function connect(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors(['email' => 'Invalid credentials']);
        }

        $user = Auth::user();
        Auth::logout();

        return $this->sendOtp($user);
    }

    /* ================= REGISTER ================= */
    public function create(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name'  => 'required',
            'email'      => 'required|email|unique:users',
            'phone'      => 'required',
            'password'   => 'required|confirmed'
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'password'   => Hash::make($request->password),
            'status'     => 'pending'
        ]);

        Auth::login($user);

        return $this->sendOtp($user);
    }

    /* ================= GOOGLE LOGIN ================= */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')
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
    private function sendOtp($user)
    {
        $otp = rand(100000, 999999);

        TwoFactorAuth::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10)
        ]);

        Mail::to($user->email)->send(new TwoFactorOtpMail($otp));

        session([
            'otp_user_id' => $user->id,
            'otp_verified' => false
        ]);

        return redirect()->route('otp.form');
    }

    /* ================= OTP FORM ================= */
    public function otpForm()
    {
        return view('auth.otp');
    }

    /* ================= VERIFY OTP ================= */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required'
        ]);

        $userId = session('otp_user_id');

        $otp = TwoFactorAuth::where('user_id', $userId)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp || !Hash::check($request->otp, $otp->otp_hash)) {
            return back()->withErrors(['otp' => 'Invalid OTP']);
        }

        Auth::loginUsingId($userId);

        session([
            'otp_verified' => true
        ]);

        session()->forget('otp_user_id');

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