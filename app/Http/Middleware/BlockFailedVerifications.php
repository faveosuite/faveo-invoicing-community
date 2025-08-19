<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Auth\LoginController;
use Cache;
use Closure;
use Illuminate\Http\Request;
use RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class BlockFailedVerifications
{
    public function handle(Request $request, Closure $next, string $context = 'verify', ?string $identifier = null): Response
    {
        // 1. Get the identifier for rate limiting based on context
        $rateLimitIdentifier = $this->getRateLimitIdentifier($context, $identifier);

        if (! $rateLimitIdentifier) {
            return redirect('login'); // No valid identifier found
        }

        // 2. Get configuration for the context
        $config = $this->getRateLimitConfig($context);

        // 3. Check rate limiting for all configured types in this context
        foreach ($config['limits'] as $type => $maxAttempts) {
            $rateLimitResult = $this->checkProgressiveRateLimit(
                $type,
                $rateLimitIdentifier,
                $maxAttempts,
                $config['penalties'],
                $request
            );

            if ($rateLimitResult !== null) {
                return $rateLimitResult; // Return rate limit response
            }
        }

        return $next($request);
    }

    /**
     * Get rate limit identifier based on context.
     */
    private function getRateLimitIdentifier(string $context, ?string $customIdentifier): ?string
    {
        if ($customIdentifier) {
            return $customIdentifier;
        }

        switch ($context) {
            case 'login':
                // For login, use IP + login input for security
                $loginInput = request()->input('email_username');

                return (new LoginController())->getLoginRateLimitKey($loginInput);

            case 'verify':
            case '2fa':
                // For verification/2fa, use user ID from session
                $userId = \Session::get('verification_user_id');

                return $userId ? (string) $userId : null;

            default:
                // Fallback to IP-based limiting
                return request()->ip();
        }
    }

    /**
     * Get rate limiting configuration for different contexts.
     */
    private function getRateLimitConfig(string $context): array
    {
        $configs = [
            'login' => [
                'limits' => [
                    'login-attempt' => 5,
                ],
                'penalties' => [
                    1 => 30,   // 30 minutes
                    2 => 60,   // 1 hour
                    3 => 180,  // 3 hours
                    4 => 360,  // 6 hours
                ],
            ],
            'verify' => [
                'limits' => [
                    'mobile-otp' => 3,
                    'email-otp' => 3,
                    'mobile-verify' => 3,
                    'email-verify' => 3,
                ],
                'penalties' => [
                    1 => 30,   // 30 minutes
                    2 => 60,   // 1 hour
                    3 => 180,  // 3 hours
                    4 => 360,  // 6 hours
                ],
            ],
            '2fa' => [
                'limits' => [
                    '2fa-code' => 3,
                    'recovery-code' => 3,
                ],
                'penalties' => [
                    1 => 30,   // 30 minutes
                    2 => 60,   // 1 hour
                    3 => 180,  // 3 hours
                    4 => 360,  // 6 hours
                ],
            ],
        ];

        return $configs[$context];
    }

    private function checkProgressiveRateLimit(
        string $type,
        string $identifier,
        int $maxAttempts,
        array $penaltyLevels,
        Request $request
    ): ?Response {
        $key = "{$type}:{$identifier}";
        $penaltyKey = "penalty_level:{$type}:{$identifier}";
        $penaltyAppliedKey = "penalty_applied:{$type}:{$identifier}";

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            // Check if penalty already applied for this cycle
            $penaltyAlreadyApplied = Cache::get($penaltyAppliedKey, false);

            if (! $penaltyAlreadyApplied) {
                // First time hitting this penalty level → increase penalty
                $currentPenaltyLevel = Cache::get($penaltyKey, 0);
                $newPenaltyLevel = min($currentPenaltyLevel + 1, count($penaltyLevels));

                // Duration (in seconds) of lockout
                $penaltyMinutes = $penaltyLevels[$newPenaltyLevel];
                $penaltySeconds = $penaltyMinutes * 60;

                // Save new penalty level (reset in 24 hours)
                Cache::put($penaltyKey, $newPenaltyLevel, now()->addHours(24));

                // Mark penalty applied for this cycle
                Cache::put($penaltyAppliedKey, true, now()->addMinutes($penaltyMinutes));

                // Reset RateLimiter and apply the lockout
                RateLimiter::clear($key);
                for ($i = 0; $i < $maxAttempts; $i++) {
                    RateLimiter::hit($key, $penaltySeconds);
                }
            }

            // Get remaining wait time
            $seconds = RateLimiter::availableIn($key);
            $waitTime = formatDuration($seconds);

            // Return context-appropriate response
            return $this->buildRateLimitResponse($request, $type, $waitTime);
        }

        return null;
    }

    /**
     * Build appropriate response based on context and request type.
     */
    private function buildRateLimitResponse(Request $request, string $type, string $waitTime): Response
    {
        // Get appropriate error message based on type
        $messageKey = $this->getErrorMessageKey($type);
        $errorMessage = __($messageKey, ['time' => $waitTime]);

        if ($request->expectsJson()) {
            return errorResponse($errorMessage, 429);
        }

        return redirect('login')->withErrors($errorMessage);
    }

    /**
     * Get error message key based on rate limit type.
     */
    private function getErrorMessageKey(string $type): string
    {
        $messageKeys = [
            // Add custom message keys for each type
        ];

        return $messageKeys[$type] ?? 'message.verify_time_limit_exceed';
    }
}
