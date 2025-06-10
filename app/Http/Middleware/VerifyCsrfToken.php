<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
use Illuminate\Session\TokenMismatchException;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
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
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $e) {
            $request->session()->regenerateToken();

            return redirect('login')->withInput($request->input())->with('fails', 'Your session has expired. Please refresh this page and login again to continue');
        }
    }
}
