<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCitizenVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('auth.login');
        }

        $user = Auth::user();

        if ($user->isAdmin() || $user->isOfficeStaff()) {
            return $next($request);
        }

        $profile = $user->citizenProfile;

        if (! $profile || $profile->verification_status !== 'verified') {
            return redirect()
                ->route('citizen.profile.show')
                ->withErrors([
                    'verification' => 'Please verify your citizen profile before using citizen portal features.',
                ]);
        }

        return $next($request);
    }
}