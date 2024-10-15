<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

class CustomCanMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$permissions
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        foreach ($permissions as $permission) {
            if (checkPermission($permission)) {
                return $next($request);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return formatApiResponse(
                true,
                get_label('not_authorized', 'You are not authorized to perform this action.')
            );
        } else {
            // For regular web requests, return the view.
            return response()->view('auth.not-authorized', ['unauthorized' => true], 403);
        }
    }
}
