<?php

namespace App\Http\Middleware;

use App\DefaultPage;
use Auth;
use Closure;
//use Illuminate\Routing\Middleware;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Session;

class Admin
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $defaulturl = DefaultPage::pluck('page_url')->first();
        if (Auth::user()->role == 'admin') {
            return $next($request);
        } elseif (Auth::user()->role == 'user') {
            $url = Session::get('session-url');
            if ($url) {
                return redirect($url);
            }

            return redirect($defaulturl);
        } else {
            Auth::logout();
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect('login')->with('fails', 'Unauthorized');
            }
        }
    }
}
