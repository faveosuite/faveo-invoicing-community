<?php

namespace Laravel\Sentinel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sentinel\Sentinel;
use Symfony\Component\HttpFoundation\Response;

class SentinelMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $driver = null)
    {
        abort_unless(Sentinel::driverOrFallback($driver)->authorize($request), 401);

        return $next($request);
    }
}
