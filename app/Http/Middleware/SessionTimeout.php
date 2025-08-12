<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Session;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  string  $timeoutMinutes  Timeout in minutes
     * @param  string  $sessionKey      Unique session key for this verification flow
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $timeoutMinutes = 10, $sessionKey = 'lastVerificationActivity')
    {
        $timeoutSeconds = (int) $timeoutMinutes * 60;

        $startTime = Session::get($sessionKey);

        // First visit after login or verification start → reset timer
        if ($request->session()->get("justStarted_{$sessionKey}")) {
            $startTime = now();
            Session::put($sessionKey, $startTime);
            Session::forget("justStarted_{$sessionKey}");
        }

        // If expired → flush and redirect
        if (now()->diffInSeconds($startTime) > $timeoutSeconds) {
            Session::flush();

            if ($request->expectsJson()) {
                return errorResponse('Your session has expired. Please log in again to continue.');
            }

            return redirect('login')->withErrors('Your session has expired. Please log in again to continue.');
        }

        // Refresh timer on active request
        Session::put($sessionKey, now());

        return $next($request);
    }
}
