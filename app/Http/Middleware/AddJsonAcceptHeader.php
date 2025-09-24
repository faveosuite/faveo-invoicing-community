<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\HeaderBag;

class AddJsonAcceptHeader
{
    private array $allowedEndpoints = [
    ];

    /**
     * Add JSON HTTP_ACCEPT header for an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->isAllowedWithoutApiKey($request)) {
            return $next($request);
        }

//        if ($errorResponse = $this->validateSettings($request)) {
//            return $errorResponse;
//        }

        $request->server->set('HTTP_ACCEPT', 'application/json');
        $request->headers = new HeaderBag($request->server->getHeaders());

        return $next($request);
    }

    private function isAllowedWithoutApiKey($request): bool
    {
        foreach ($this->allowedEndpoints as $value) {
            if (str_contains($request->url(), $value)) {
                return true;
            }
        }

        return false;
    }

//    private function validateSettings($request)
//    {
//        $apiInfo = ApiSetting::pluck('value', 'key')->toArray();
//
//        $rules = [];
//
//        $rules['api_key_check'][] = new ApiFeatureEnabled;
//
//        if (!empty($apiInfo['api_key_mandatory'])) {
//            $rules['api_key'] = ['required', new ValidApiKey];
//        }
//
//        $validator = \Validator::make(
//            ['api_key' => $request->input('api_key'), 'api_key_check' => 'check'],
//            $rules,
//            ['api_key.required' => \Lang::get('lang.api_key_is_required')]
//        );
//
//        if ($validator->fails()) {
//            return errorResponse($validator->errors());
//        }
//
//        $usedKey = $request->attributes->get('used_api_key');
//
//        return $this->handleLogging($request, $usedKey);
//    }

//    private function handleLogging($request, $usedKey)
//    {
//        $usedKey?->logs()->create([
//            'api_key_id' => $usedKey->id,
//            'endpoint' => $request->path(),
//            'method' => $request->method(),
//            'payload' => json_encode($request->except(['password', 'api_key'])),
//            'executed_at' => now(),
//            'executed_by' => \Auth::id(),
//        ]);
//
//        return false;
//    }
}
