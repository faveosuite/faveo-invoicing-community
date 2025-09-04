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
    public function handle(Request $request, Closure $next, $action)
    {
        $settings = RecaptchaSetting::first();
        $statusSetting = StatusSetting::first()->value('recaptcha_status');

        if(!$statusSetting){
            return $next($request);
        }

        $recaptchaResponse = $request->input('g-recaptcha-response');
        $remoteIp = $request->ip();
        $pageId = $request->input('page_id');

        switch ($settings->captcha_version) {
            case 'v3_invisible':
                return $this->handleV3Invisible($request, $recaptchaResponse, $remoteIp, $settings, $action, $next, $pageId);

            case 'v2_checkbox':
            case 'v2_invisible':
                return $this->handleV2($request, $recaptchaResponse, $remoteIp, $settings, $next);

            default:
                return $next($request);
        }
    }

    private function handleV3Invisible($request, $recaptchaResponse, $remoteIp, $settings, $action, $next, $pageId)
    {
        $sessionKey = $this->getSessionKey($action, $pageId);

        // If session does not show V2 recaptcha, verify V3
        if (!Session::get($sessionKey, false)) {
            $verification = $this->verify($settings->v3_secret_key, $recaptchaResponse, $remoteIp);

            if (!$this->isV3Valid($verification, $action, $request->getHost())) {
                return errorResponse($settings->error_message, 422);
            }
            if ($verification['score'] < $settings->score_threshold) {
                if ($settings->failover_action === 'v2_checkbox') {
                    Session::put($sessionKey, true);
                    return successResponse($settings->error_message, ['show_v2_recaptcha' => true], 422);
                }
                return errorResponse($settings->error_message, 422);
            }

            return $next($request);
        }

        // Failover: V2 verification
        if ($settings->failover_action === 'v2_checkbox' && Session::get($sessionKey)) {
            $verification = $this->verify($settings->v2_secret_key, $recaptchaResponse, $remoteIp);
            if (!$verification['success']) {
                return errorResponse($settings->error_message, 422);
            }
            return $next($request);
        }

        return errorResponse($settings->error_message, 422);
    }

    private function handleV2($request,$recaptchaResponse, $remoteIp, $settings, $next)
    {
        if (!$recaptchaResponse) {
            return errorResponse($settings->error_message, 422);
        }

        $verification = $this->verify($settings->v2_secret_key, $recaptchaResponse, $remoteIp);

        if (!$verification['success']) {
            return errorResponse($settings->error_message, 422);
        }

        return $next($request);
    }

    private function verify($secretKey, $response, $ip)
    {
        return Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => $secretKey,
            'response' => $response,
            'remoteip' => $ip,
        ])->json();
    }

    private function isV3Valid($verification, $action, $hostname)
    {
        return ($verification['success'])
            && (($verification['action']) === $action)
            && (($verification['hostname']) === $hostname);
    }

    private function getSessionKey($action, $pageId): string
    {
        return 'show_v2_recaptcha_' . $action . '_' . $pageId;

    }
}
