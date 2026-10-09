<?php

namespace Laravel\Sentinel\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class Laravel extends Driver
{
    /**
     * Authorize access for the request.
     *
     * @throws RuntimeException
     */
    public function authorize(Request $request): bool
    {
        if (! $this->app()->environment('local')) {
            return true;
        }

        if ($this->isPrivateIp($request->ip())
            && ! $request->isFromTrustedProxy()
            && Str::endsWith($request->host(), ['.sharedwithexpose.com', '.ngrok-free.app', '.ngrok.io'])) {
            throw new RuntimeException(
                sprintf('Unable to access "%s /%s" using "local" environment, please change the environment or configure trusted proxies: https://laravel.com/docs/requests#configuring-trusted-proxies', $request->method(), $request->path())
            );
        }

        if ($this->isRunningOnDockerLocally($request)) {
            return true;
        }

        return $this->authorizeAccessingViaReverseProxies($request);
    }
}
