<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stamp every response with an "X-Faveo-App" marker header.
 *
 * A 401 reaching the browser is not necessarily ours: proxies, WAFs
 * (mod_security, Cloudflare) and captive portals inject that status with their
 * own body when connectivity breaks. The axios interceptor uses this header to
 * tell a genuine "your session expired" from an infrastructure hiccup, so a
 * flaky network can't silently log everyone out.
 */
class MarkAppResponse
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Faveo-App', '1');

        return $response;
    }
}
