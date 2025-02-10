<?php

namespace App\Http\Middleware;

// use Illuminate\Contracts\Routing\Middleware;
use App\Model\Common\Setting;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class LanguageMiddleware
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->language) {
            $this->setLocale(Auth::user()->language);

            return $next($request);
        }
        $this->setLocale($this->getLangFromSessionOrCache());

        return $next($request);
    }

    protected function setLocale($lang)
    {
        if ($lang != '' && array_key_exists($lang, Config::get('languages'))) {
            $availableLanguages = array_map('basename', File::directories(lang_path()));
            in_array($lang, $availableLanguages) ? App::setLocale($lang) : App::setLocale('en');
        }
    }

    public function getLangFromSessionOrCache()
    {
        $lang = match (true) {
            Session::has('language') => Session::get('language'),
            Cache::has('language') => Cache::get('language'),
            !Cache::has('language') && isInstall() => Setting::select('content')->where('id', 1)->first()->content,
            default => 'en',
        };

        if (!Cache::has('language') && isInstall()) {
            Cache::forever('language', $lang);
        }

        return $lang;
    }
}
