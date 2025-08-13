<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class BlockFailedVerifications
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $context = 'verify'): Response
    {
        $userId = \Session::get('verification_user_id');

        if (! $userId) {
            return redirect('login');
        }

        // Two sets of limits
        $limitSets = [
            'verify' => [
                'mobile-otp' => 3,
                'email-otp' => 3,
                'mobile-verify' => 3,
                'email-verify' => 3,
            ],
            '2fa' => [
                '2fa-code' => 3,
                'recovery-code' => 3,
            ],
        ];

        // Pick the correct set based on the middleware parameter
        $limits = $limitSets[$context] ?? $limitSets['verify'];

        foreach ($limits as $type => $maxAttempts) {
            $key = "{$type}:{$userId}";
            if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                $seconds = RateLimiter::availableIn($key);
                $waitTime = formatDuration($seconds);

                if ($request->expectsJson()) {
                    return errorResponse(__('message.verify_time_limit_exceed', ['time' => $waitTime]), 429);
                }

                return redirect('login')->withErrors(
                    __('message.verify_time_limit_exceed', ['time' => $waitTime])
                );
            }
        }

        return $next($request);
    }
}
