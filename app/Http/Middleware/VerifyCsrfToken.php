<?php

namespace App\Http\Middleware;

use Override;
use Illuminate\Http\Request;
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
        'faveo-whatsapp',
        'upload-image',
        'api/v3/*',
        'v3/api/*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
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
