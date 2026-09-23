<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\HeaderBag;

class AddJsonAcceptHeader
{
    /**
     * @var array<mixed>
     */
    private array $allowedEndpoints = [
    ];

    /**
     * Add JSON HTTP_ACCEPT header for an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->isAllowedWithoutApiKey($request)) {
            return $next($request);
        }

        $request->server->set('HTTP_ACCEPT', 'application/json');
        $request->headers = new HeaderBag($request->server->getHeaders());

        return $next($request);
    }

    private function isAllowedWithoutApiKey(mixed $request): bool
    {
        return array_any($this->allowedEndpoints, fn ($value): bool => str_contains((string) $request->url(), (string) $value));
    }
}
