<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Auth\LoginController;
use Cache;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RateLimiter;
use Session;

/**
 * Blocks requests when a user exceeds the allowed number of failed attempts
 * for login, verification (email/mobile OTP), or two-factor authentication.
 *
 * How it works:
 * - Each action type (e.g. email-verify, mobile-otp) has a max attempt limit.
 * - Once any limit is reached, all routes using this middleware are blocked.
 * - Lockout durations escalate progressively: 30min -> 1hr -> 3hr -> 6hr.
 * - Penalty level is shared per user per context, so repeated violations
 *   across different types escalate together.
 * - Penalty tracking is per type, so hitting one type's limit doesn't
 *   prevent another type from getting its own penalty applied.
 *
 * Usage in routes:
 *   - Check all limits: middleware('blockFailedVerifications:verify')
 *   - Check specific types: middleware('blockFailedVerifications:verify,email-verify,mobile-verify')
 *     When specific types are passed, only those are checked instead of all.
 *     This is used to verify OTP routes so that OTP send limits don't block verification.
 *
 * The actual RateLimiter::hit() calls happen in the controllers on failure/action,
 * not in this middleware. This middleware only reads and enforces the limits.
 *
 * Contexts:
 *   - login: tracks failed login attempts (identifier: IP + email/username hash)
 *   - verify: tracks OTP sends and failed verifications (identifier: user ID)
 *   - 2fa: tracks failed 2FA code attempts (identifier: user ID)
 *
 * Cache keys used:
 *   - penalty_level:{context}:{identifier} — current escalation level (1-4), expires in 24h
 *   - penalty_applied:{context}:{type}:{identifier} — whether penalty was already applied for this cycle
 */
class BlockFailedVerifications
{
    /**
     * Progressive lockout durations in minutes.
     * The level increases each time a user exhausts their attempts within a penalty window.
     */
    private const array PENALTIES = [
        1 => 30,
        2 => 60,
        3 => 180,
        4 => 360,
    ];

    /**
     * Rate limit types and their max allowed attempts per context.
     *
     * "Verify" types explained:
     *   mobile-otp / email-otp* — OTP send attempts (hit in controller on every send)
     *   mobile-verify / email-verify* — OTP verification failures (hit in controller only on wrong OTP)
     */
    private const array CONFIGS = [
        'login' => [
            'limits' => ['login-attempt' => 5],
        ],
        'verify' => [
            'limits' => [
                'mobile-otp' => 3,
                'email-otp' => 3,
                'email-otp-new' => 3,
                'email-otp-old' => 3,
                'mobile-verify' => 3,
                'email-verify' => 3,
                'email-verify-new' => 3,
                'email-verify-old' => 3,
                'email-verify-mobile' => 3,
            ],
        ],
        '2fa' => [
            'limits' => ['2fa-code' => 3, 'recovery-code' => 3],
        ],
    ];

    /**
     * Main handler. Checks all configured limits (or only the specified types)
     * and blocks the request if any limit has been exceeded.
     *
     * @param  string  $context  One of: logins, verify, 2fa
     * @param  string  ...$onlyTypes  Optional — if provided, only these types are checked.
     *                                Used by verify OTP routes to skip OTP send limits.
     */
    public function handle(Request $request, Closure $next, string $context = 'verify', string ...$onlyTypes): mixed
    {
        $identifier = $this->getIdentifier($context);

        if (! $identifier) {
            return redirect('login');
        }

        $limits = self::CONFIGS[$context]['limits'];

        if ($onlyTypes !== []) {
            $limits = array_intersect_key($limits, array_flip($onlyTypes));
        }

        foreach ($limits as $type => $max) {
            if ($response = $this->enforce($context, $type, $identifier, $max, $request)) {
                return $response;
            }
        }

        return $next($request);
    }

    /**
     * Resolves who we're rate-limiting based on the context.
     * Login uses IP + input hash, verify/2fa uses user ID from auth or session.
     */
    private function getIdentifier(string $context): ?string
    {
        return match ($context) {
            'login' => (new LoginController)->getLoginRateLimitKey(request()->input('email_username')),
            'verify', '2fa' => (string) (auth()->id() ?? Session::get('verification_user_id')) ?: null,
            default => request()->ip(),
        };
    }

    /**
     * Checks if the given rate limit type has been exceeded.
     * If yes, applies a progressive penalty (if not already applied) and returns a 429 response.
     */
    private function enforce(string $context, string $type, string $identifier, int $maxAttempts, Request $request): mixed
    {
        $key = sprintf('%s:%s', $type, $identifier);

        if (! RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return null;
        }

        $this->applyProgressivePenalty($context, $type, $identifier, $key, $maxAttempts);

        $waitTime = formatDuration(RateLimiter::availableIn($key));

        return $this->respond($request, $type, $waitTime);
    }

    /**
     * Escalates the lockout duration when the user hits a limit for the first time in a cycle.
     *
     * On first trigger: clears the rate limiter, then re-fills it with the penalty duration
     * so that availableIn() returns the correct lockout time.
     *
     * Skips if a penalty was already applied for this type in the current cycle
     * (prevents re-escalating on every subsequent blocked request).
     *
     * Penalty level is shared across types within the same context — so if a user
     * hits email-otp limit (level 1 = 30min), then later hits email-verify limit,
     * that starts at level 2 (60min).
     */
    private function applyProgressivePenalty(string $context, string $type, string $identifier, string $key, int $maxAttempts): void
    {
        $penaltyKey = sprintf('penalty_level:%s:%s', $context, $identifier);
        $appliedKey = sprintf('penalty_applied:%s:%s:%s', $context, $type, $identifier);

        if (Cache::get($appliedKey, false)) {
            return;
        }

        $level = min(Cache::get($penaltyKey, 0) + 1, count(self::PENALTIES));
        $minutes = self::PENALTIES[$level]; // @phpstan-ignore offsetAccess.invalidOffset

        Cache::put($penaltyKey, $level, now()->addHours(24));
        Cache::put($appliedKey, true, now()->addMinutes($minutes));

        RateLimiter::clear($key);
        for ($i = 0; $i < $maxAttempts; $i++) {
            RateLimiter::hit($key, $minutes * 60);
        }
    }

    /**
     * Returns a 429 JSON error for AJAX requests, or redirects to login with
     * an error flash message for standard page requests.
     */
    private function respond(Request $request, string $type, string $waitTime): JsonResponse|RedirectResponse
    {
        $message = __($this->getMessageKey($type), ['time' => $waitTime]);

        if ($request->expectsJson()) {
            return errorResponse($message, 429);
        }

        return redirect('login')->withErrors($message);
    }

    /**
     * Maps rate limit types to their translation keys.
     * Extend this to show different messages for different types (e.g. "Too many OTP requests").
     */
    private function getMessageKey(string $type): string
    {
        return match ($type) {
            default => 'message.verify_time_limit_exceed',
        };
    }
}
