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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $timeoutMinutes = 10, $sessionKey = 'lastActivityTime')
    {
        $timeoutSeconds = (int) $timeoutMinutes * 60;

        $startTime = Session::get($sessionKey);

        if (!$startTime) {
            $startTime = now();
            Session::put($sessionKey, $startTime);
        }

        if (now()->diffInSeconds($startTime) > $timeoutSeconds) {
            Session::flush();

            if ($request->expectsJson()) {
                return errorResponse('Your session has expired. Please log in again to continue.');
            }

            return redirect('login')->withErrors('Your session has expired. Please log in again to continue.');
        }

        return $next($request);
    }
}
