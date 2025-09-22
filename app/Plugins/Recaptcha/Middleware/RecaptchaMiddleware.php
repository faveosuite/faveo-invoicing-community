<?php

namespace App\Plugins\Recaptcha\Middleware;

use App\Model\Common\StatusSetting;
use App\Plugins\Recaptcha\Model\RecaptchaSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class RecaptchaMiddleware
{
    public function handle(Request $request, Closure $next, string $action): mixed
    {
        // Early exit if reCAPTCHA is disabled
        $statusSetting = StatusSetting::query()->first();
        if (! $statusSetting || ! $statusSetting->recaptcha_status) {
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
            'v2_checkbox', 'v2_invisible' => $this->handleV2($request, $recaptchaResponse, $settings, $next),
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
        $pageId = $request->input('page_id');
        if (! $pageId) {
            return errorResponse(__('recaptcha::recaptcha.captcha_message'), 422);
        }

        $sessionKey = $this->getSessionKey($action, $pageId);

        // Handle failover mode (V2 verification)
        if (Session::get($sessionKey)) {
            return $this->verifyV2($recaptchaResponse, $settings, $next);
        }

        // Primary V3 verification
        $verification = $this->verify(
            $settings->v3_secret_key,
            $recaptchaResponse,
            $request->ip(),
            $request->getHost()
        );

        // Check if token is valid (success + correct action/hostname)
        $isTokenValid = ($verification['success'] ?? false)
            && ($verification['action'] ?? '') === $action
            && ($verification['hostname'] ?? '') === $request->getHost();

        // If token is valid but score is too low, trigger fallback
        if ($isTokenValid && ($verification['score'] ?? 0) < $settings->score_threshold) {
            if ($settings->failover_action === 'v2_checkbox') {
                Session::put($sessionKey, true);

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
        Request $request,
        string $recaptchaResponse,
        RecaptchaSetting $settings,
        Closure $next
    ): mixed {
        return $this->verifyV2($recaptchaResponse, $settings, $next);
    }

    private function verifyV2(string $response, RecaptchaSetting $settings, Closure $next): mixed
    {
        $verification = $this->verify(
            $settings->v2_secret_key,
            $response,
            request()->ip()
        );

        return $verification['success']
            ? $next(request())
            : errorResponse(__('recaptcha::recaptcha.captcha_message'), 422);
    }

    private function verify(string $secretKey, string $response, string $ip, ?string $hostname = null): array
    {
        return Http::asForm()->post(
            'https://www.google.com/recaptcha/api/siteverify',
            array_filter([
                'secret' => $secretKey,
                'response' => $response,
                'remoteip' => $ip,
                'hostname' => $hostname,
            ])
        )->json();
    }

    private function getSessionKey(string $action, string $pageId): string
    {
        return "recaptcha_v2_fallback_{$action}_{$pageId}";
    }
}
