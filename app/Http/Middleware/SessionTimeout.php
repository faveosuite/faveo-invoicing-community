<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use HTTP;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class SessionTimeout
{
    /**
     * Handle an incoming request and manage session timeout for verification/2FA.
     *
     * @param  Request  $request
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     * @param  int  $timeoutMinutes  The session timeout threshold in minutes (default: 10).
     * @param  string  $sessionKey  The session key used to track verification activity.
     * @return HTTP|RedirectResponse
     */
    public function handle(Request $request, Closure $next, int $timeoutMinutes = 10, string $sessionKey = 'lastVerificationActivity')
    {
        $now = Carbon::now();

        // Reset timer if new verification/2FA flow just started
        if ($this->shouldResetTimer()) {
            $this->resetVerificationTimer($sessionKey, $now);

            return $next($request);
        }

        // Initialize timer if not set
        if (! Session::has($sessionKey)) {
            $this->resetVerificationTimer($sessionKey, $now);

            return $next($request);
        }

        // Check for timeout
        $lastActivity = Carbon::createFromTimestamp(Session::get($sessionKey));
        if ($now->diffInMinutes($lastActivity) >= $timeoutMinutes) {
            $this->expireSession($sessionKey);

            return $request->expectsJson()
                ? errorResponse('Your session has expired. Please log in again to continue.', 401)
                : redirect()->route('login')->with('fails', 'Your session has expired. Please log in again to continue.');
        }

        return $next($request);
    }

    /**
     * Determine if the timer should be reset due to fresh verification or 2FA start.
     *
     * @return bool
     */
    private function shouldResetTimer(): bool
    {
        return Session::pull('justStarted') ?? false;
    }

    /**
     * Reset or start a new verification session timer.
     *
     * @param  string  $sessionKey
     * @param  Carbon|null  $time
     * @return void
     */
    private function resetVerificationTimer(string $sessionKey, ?Carbon $time = null): void
    {
        Session::put($sessionKey, ($time ?? Carbon::now())->timestamp);
    }

    /**
     * Expire the current verification session and notify the user.
     *
     * @param  string  $sessionKey
     * @return void
     */
    private function expireSession(string $sessionKey): void
    {
        Session::forget($sessionKey);
        Session::flash('fails', 'Your session has expired. Please log in again to continue.');
    }
}
