<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! isInstall()) {
            return $next($request);
        }

        if ($request->isJson()) {
            $url = url('/');
            $result = ['fails' => 'already installed', 'api' => $url];
            return response()->json(compact('result'));
        }

        return redirect('/');
    }
}
