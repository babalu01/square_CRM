<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomVerifiedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the 'web' guard (for users) email is verified
        if (Auth::guard('web')->check() && !Auth::guard('web')->user()->hasVerifiedEmail() && !(getAuthenticatedUser()->hasRole('admin'))) {
            if ($request->expectsJson()) {
                return formatApiResponse(true, 'Email not verified.');
            } else {
                return redirect()->route('verification.notice');
            }
        }

        // Check if the 'client' guard (for clients) email is verified
        if (Auth::guard('client')->check() && !Auth::guard('client')->user()->hasVerifiedEmail()) {
            if ($request->expectsJson()) {
                return formatApiResponse(true, 'Email not verified.');
            } else {
                return redirect()->route('verification.notice'); // Customize this route
            }
        }

        // Check if the user is authenticated via Sanctum and email is verified
        if (Auth::guard('sanctum')->check() && !Auth::guard('sanctum')->user()->hasVerifiedEmail()) {
            if ($request->expectsJson()) {
                return formatApiResponse(true, 'Email not verified.');
            } else {
                return redirect()->route('verification.notice'); // Customize this route
            }
        }

        return $next($request);
    }
}
