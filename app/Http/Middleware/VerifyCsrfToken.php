<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Override;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     */
    protected $except = [
        'serial',
        'verification',
        'update-latest-version',
        'v1/checkUpdatesExpiry',
        'v2/serial',
        'update/lic-code',
        'renewurl',
        'pricing/data',
        'group/data',
        'api/csp-report',
        'faveo-whatsapp',
        'api/v3/*',
        'v3/api/*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    #[Override]
    public function handle($request, Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException) {
            $request->session()->regenerateToken();

            return redirect('login')->withInput($request->input())->with('fails', 'Your session has expired. Please refresh this page and login again to continue');
        }
    }
}
