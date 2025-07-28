<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPulseEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (! config('pulse.enabled')) {
            return redirect('404');
        }

        return $next($request);
    }
}
