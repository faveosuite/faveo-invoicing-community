<?php

namespace App\Http\Middleware;

use App\DefaultPage;
use App\User;
use Auth;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Session;

class Admin
{
    /**
     * The Guard implementation.
     */
    protected Guard $auth;

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
        $defaulturl = DefaultPage::value('page_url');
        /** @var User $authUser */
        $authUser = Auth::user();
        if ($authUser->role == 'admin') {
            return $next($request);
        }

        if ($authUser->role == 'user') {
            $url = Session::get('session-url');
            if ($url) {
                return redirect($url);
            }

            return redirect($defaulturl);
        }

        Auth::logout();
        if ($request->ajax()) {
            return response('Unauthorized.', 401);
        }

        return redirect('login')->with('fails', 'Unauthorized');
    }
}
