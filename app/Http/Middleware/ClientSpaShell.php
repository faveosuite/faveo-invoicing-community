<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Serves the client Vue SPA shell for top-level browser navigations to client
 * panel paths, so those pages live at the app root (e.g. /orders, /invoices)
 * instead of under a /client prefix.
 *
 * Only genuine browser navigations are intercepted: GET requests that accept
 * HTML and do NOT want JSON. The SPA's axios calls always send
 * `Accept: application/json`, so the existing data APIs that share these paths
 * (e.g. the admin panel's GET /orders, GET /invoices) are left untouched.
 */
class ClientSpaShell
{
    /** Authenticated client panel paths (guarded server-side to avoid a UI flash). */
    protected array $protected = [
        'client-dashboard',
        'my-orders', 'my-order/*',
        'my-invoices', 'my-invoice/*',
        'my-profile', 'my-profile/*',
        'store', 'store/*',
        'cart',
        'checkout',
        'place-order',
        'payment-success',
    ];

    /** Public client paths (served to anyone, authenticated or not). */
    protected array $public = [
        'pages/*',
        'verify',
        'verify-2fa',
    ];

    /** Guest-only paths: served to guests, but authenticated users go to their panel. */
    protected array $guestOnly = [
        'login',
        'password/reset',
        'password/reset/*',
    ];

    public function handle($request, Closure $next)
    {
        $isBrowserNavigation = $request->isMethod('GET')
            && ! $request->wantsJson()
            && $request->acceptsHtml();

        if ($isBrowserNavigation) {
            if ($request->is(...$this->public)) {
                return response(view('client'));
            }

            if ($request->is(...$this->guestOnly)) {
                return auth()->check()
                    ? redirect($this->panelUrl())
                    : response(view('client'));
            }

            if ($request->is(...$this->protected)) {
                return auth()->check()
                    ? response(view('client'))
                    : redirect(url('/login'));
            }
        }

        return $next($request);
    }

    /** Landing URL for an authenticated user, by role. */
    protected function panelUrl(): string
    {
        return url(auth()->user()?->role === 'user' ? '/dashboard' : '/admin');
    }
}
