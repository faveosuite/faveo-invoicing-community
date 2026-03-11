<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Auth\LoginController;
use Cache;
use Closure;
use Illuminate\Http\Request;
use RateLimiter;
use Session;
use Symfony\Component\HttpFoundation\Response;

class BlockFailedVerifications
{
    private const array PENALTIES = [
        1 => 30,    // 30 minutes
        2 => 60,    // 1 hour
        3 => 180,   // 3 hours
        4 => 360,   // 6 hours
    ];

    private const array CONFIGS = [
        'login' => [
            'global_limits' => ['login-attempt' => 5],
            'route_limits' => [],
        ],
        'verify' => [
            'global_limits' => [
                'mobile-verify' => 3,
                'email-verify' => 3,
                'email-verify-new' => 3,
                'email-verify-old' => 3,
                'email-verify-mobile' => 3,
            ],
            'route_limits' => [
                'mobile-otp' => 3,
                'email-otp' => 3,
                'email-otp-new' => 3,
                'email-otp-old' => 3,
            ],
        ],
        '2fa' => [
            'global_limits' => ['2fa-code' => 3, 'recovery-code' => 3],
            'route_limits' => [],
        ],
    ];

    public function handle(Request $request, Closure $next, string $context = 'verify', string ...$routeLimitTypes): Response
    {
        $identifier = $this->getIdentifier($context);

        if (! $identifier) {
            return redirect('login');
        }

        $config = self::CONFIGS[$context];

        // 1. Global limits — if ANY is reached, block ALL routes
        foreach ($config['global_limits'] as $type => $max) {
            if ($response = $this->enforce($context, $type, $identifier, $max, $request)) {
                return $response;
            }
        }

        // 2. Route-specific limits — only check types passed as middleware parameter
        foreach ($routeLimitTypes as $type) {
            if ($max = $config['route_limits'][$type] ?? null) {
                if ($response = $this->enforce($context, $type, $identifier, $max, $request)) {
                    return $response;
                }
            }
        }

        return $next($request);
    }

    private function getIdentifier(string $context): ?string
    {
        return match ($context) {
            'login' => (new LoginController)->getLoginRateLimitKey(request()->input('email_username')),
            'verify', '2fa' => (string) (auth()->id() ?? Session::get('verification_user_id')) ?: null,
            default => request()->ip(),
        };
    }

    private function enforce(string $context, string $type, string $identifier, int $maxAttempts, Request $request): ?Response
    {
        $key = "{$type}:{$identifier}";

        if (! RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return null;
        }

        $this->applyProgressivePenalty($context, $identifier, $key, $maxAttempts);

        $waitTime = formatDuration(RateLimiter::availableIn($key));

        return $this->respond($request, $type, $waitTime);
    }

    private function applyProgressivePenalty(string $context, string $identifier, string $key, int $maxAttempts): void
    {
        $penaltyKey = "penalty_level:{$context}:{$identifier}";
        $appliedKey = "penalty_applied:{$context}:{$identifier}";

        if (Cache::get($appliedKey, false)) {
            return;
        }

        $level = min(Cache::get($penaltyKey, 0) + 1, count(self::PENALTIES));
        $minutes = self::PENALTIES[$level];

        Cache::put($penaltyKey, $level, now()->addHours(24));
        Cache::put($appliedKey, true, now()->addMinutes($minutes));

        RateLimiter::clear($key);
        for ($i = 0; $i < $maxAttempts; $i++) {
            RateLimiter::hit($key, $minutes * 60);
        }
    }

    private function respond(Request $request, string $type, string $waitTime): Response
    {
        $message = __($this->getMessageKey($type), ['time' => $waitTime]);

        if ($request->expectsJson()) {
            return errorResponse($message, 429);
        }

        return redirect('login')->withErrors($message);
    }

    private function getMessageKey(string $type): string
    {
        return match ($type) {
            default => 'message.verify_time_limit_exceed',
        };
    }
}
