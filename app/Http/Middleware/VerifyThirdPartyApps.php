<?php

namespace App\Http\Middleware;

use App\ThirdPartyApp;
use Closure;
use Illuminate\Http\Request;

class VerifyThirdPartyApps
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // abs(): a future timestamp must not stay valid forever (replay window).
        if (abs(time() - (int) $request->input('timestamp')) > 900) { // 900 sec is 15 mins
            $result = ['status' => 'fails', 'message' => 'Suspicious activity detected'];

            return response()->json(compact('result'), 401);
        }

        $appKey = $request->header('app-key');
        $app_secret = $appKey ? ThirdPartyApp::where('app_key', $appKey)->value('app_secret') : null;

        // An unknown key (null secret) or a blank secret must never reach the HMAC:
        // hash_hmac() with an empty key is something anyone can compute.
        if (! is_string($app_secret) || $app_secret === '') {
            return $this->invalidSignature();
        }

        $requestParameters = file_get_contents('php://input'); // get all the form parameters in the request
        $requestHeader = $request->header('signature'); // get signature sent in the request
        $signature = hash_hmac('sha256', (string) $requestParameters, $app_secret); // hash the request parameter with the app secret

        if ($requestHeader && hash_equals($signature, $requestHeader)) {
            return $next($request);
        }

        return $this->invalidSignature();
    }

    private function invalidSignature(): \Illuminate\Http\JsonResponse
    {
        $result = ['status' => 'fails', 'message' => 'Invalid signature'];

        return response()->json(compact('result'), 401);
    }
}
