<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsOfficeStaffMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('auth.login');
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->isOfficeStaff() && ! $user->isAdmin()) {
            abort(403);
        }

        return $next($request);
    }
}
