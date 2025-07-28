<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Handles all security related headers.
 *
 * @refer https://cheatsheetseries.owasp.org/cheatsheets/HTTP_Strict_Transport_Security_Cheat_Sheet.html
 * @refer https://www.owasp.org/index.php/Cross_Frame_Scripting
 */
class SecurityEnforcer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! config('database.DB_INSTALL')) {
            return $next($request);
        }

        $response = $next($request);

        if (method_exists($response, 'header')) {
            // tells browser that faveo cannot be used within in i-frame. ( XFS vulnerability )
            $response->header('X-Frame-Options', 'SAMEORIGIN');
            $response->header('X-Content-Type-Options', 'nosniff');

            // redirecting to https if configured to open in https
            if ($this->urlScheme(config('app.url')) == 'https' && $this->urlScheme($request->url()) == 'http') {
                return redirect()->secure($request->getPathInfo());
            }
        }

        return $response;
    }

    /**
     * Checks if url is http or https.
     *
     * @param  string  $url
     * @return string
     */
    private function urlScheme($url)
    {
        $parsedUrl = parse_url($url);

        if (! $parsedUrl) {
            return '';
        }

        return $parsedUrl['scheme'];
    }
}
