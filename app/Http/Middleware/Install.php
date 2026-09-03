<?php

namespace App\Http\Middleware;

use Closure;
use File;
use Illuminate\Http\Request;

class Install
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $env = base_path('.env');
        if (File::exists($env) && \Config('database.DB_INSTALL') == 1) {
            return $next($request);
        }

        return redirect('probe.php');
    }
}
