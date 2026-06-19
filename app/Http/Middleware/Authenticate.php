<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class Authenticate
{
    /**
     * The Guard implementation.
     */
    protected \Illuminate\Contracts\Auth\Guard $auth;

    /**
     * Create a new filter instance.
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->guest()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            }

            return redirect()->guest('auth/login');
        }

        /** @var \App\User $authUser */
        $authUser = Auth::user();
        if ($authUser->active == 1) {
            return $next($request);
        }

        Auth::logout();

        return redirect('home')->with('fails', 'Activate Your Account');
    }
}
