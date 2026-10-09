<?php

namespace App\Plugins\Recaptcha\Middleware;

use App\Plugins\Recaptcha\Model\RecaptchaSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Logger;
use Throwable;

class RecaptchaMiddleware
{
    public function handle(Request $request, Closure $next, string $action): mixed
    {
        // Early exit if reCAPTCHA is disabled
        $statusEnabled = RecaptchaSetting::isCaptchaCanRun();
        if (! $statusEnabled) {
            return $next($request);
        }

        // Validate required settings exist
        $settings = RecaptchaSetting::query()->first();
        if (! $settings) {
            return $next($request);
        }

        // Validate required request parameters
        $recaptchaResponse = $request->input('g-recaptcha-response');
        if (! $recaptchaResponse) {
            return errorResponse(__('recaptcha::recaptcha.captcha_message'), 422);
        }

        return match ($settings->captcha_version) {
            'v3_invisible' => $this->handleV3Invisible($request, $recaptchaResponse, $action, $settings, $next),
            'v2_checkbox', 'v2_invisible' => $this->handleV2($recaptchaResponse, $settings, $next),
            default => $next($request),
        };
    }

    private function handleV3Invisible(
        Request $request,
        string $recaptchaResponse,
        string $action,
        RecaptchaSetting $settings,
        Closure $next
    ): mixed {
        $sessionKey = $this->getSessionKey($action);

        // Failover mode: this action was switched to the v2 checkbox after a low v3 score.
        if (Session::get($sessionKey)) {
            if ($this->passesV2($recaptchaResponse, $settings)) {
                // One solved checkbox clears it — otherwise the action stays v2-only for the whole session.
                Session::forget($sessionKey);

                return $next($request);
            }

            // Still ask for the checkbox: after a reload the form is back on v3 and must be told to switch again.
            return successResponse(
                __('recaptcha::recaptcha.captcha_message'),
                ['show_v2_recaptcha' => true],
                422
            );
        }

        // Primary V3 verification
        $verification = $this->verify(
            (string) $settings->v3_secret_key,
            $recaptchaResponse,
            (string) $request->ip(),
            $request->getHost()
        );

        // Check if token is valid (success + correct action/hostname)
        $isTokenValid = ($verification['success'] ?? false)
            && ($verification['action'] ?? '') === $action
            && ($verification['hostname'] ?? '') === $request->getHost();

        // If token is valid but score is too low, trigger fallback
        if ($isTokenValid && ($verification['score'] ?? 0) < $settings->score_threshold) {
            if ($settings->failover_action === 'v2_checkbox') {
                Session::put($sessionKey, value: true);

                return successResponse(
                    __('recaptcha::recaptcha.captcha_message'),
                    ['show_v2_recaptcha' => true],
                    422
                );
            }

            return errorResponse(__('recaptcha::recaptcha.captcha_message'), 422);
        }

        // If token is valid and score is acceptable, proceed
        if ($isTokenValid) {
            return $next($request);
        }

        // Any other verification failure
        return errorResponse(__('recaptcha::recaptcha.captcha_message'), 422);
    }

    private function handleV2(
        string $recaptchaResponse,
        RecaptchaSetting $settings,
        Closure $next
    ): mixed {
        return $this->verifyV2($recaptchaResponse, $settings, $next);
    }

    private function verifyV2(string $response, RecaptchaSetting $settings, Closure $next): mixed
    {
        return $this->passesV2($response, $settings)
            ? $next(request())
            : errorResponse(__('recaptcha::recaptcha.captcha_message'), 422);
    }

    private function passesV2(string $response, RecaptchaSetting $settings): bool
    {
        $verification = $this->verify((string) $settings->v2_secret_key, $response, (string) request()->ip());

        return (bool) ($verification['success'] ?? false);
    }

    /**
     * @return array<mixed>
     */
    private function verify(string $secretKey, string $response, string $ip, ?string $hostname = null): array
    {
        try {
            $result = Http::asForm()->timeout(10)->post(
                'https://www.google.com/recaptcha/api/siteverify',
                array_filter([
                    'secret' => $secretKey,
                    'response' => $response,
                    'remoteip' => $ip,
                    'hostname' => $hostname,
                ])
            )->json();
        } catch (Throwable $exception) {
            // Google unreachable: fail closed with the normal captcha error instead of a 500.
            Logger::exception($exception);

            return [];
        }

        return is_array($result) ? $result : [];
    }

    private function getSessionKey(string $action): string
    {
        return 'recaptcha_v2_fallback_'.$action;
    }
}
