<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureOtpVerified
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {

            // if user still in OTP session → block access
            if (session()->has('otp_user_id')) {
                return redirect()->route('otp.form');
            }
        }

        return $next($request);
    }
}