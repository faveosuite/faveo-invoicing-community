<?php

namespace App\Http\Middleware;

// use Illuminate\Contracts\Routing\Middleware;
use App\Model\Common\Language;
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
        if (Auth::check()) {
            $user = Auth::user(); // Store user instance to avoid multiple calls

            $lang = match (true) {
                Session::has('language') => tap(Session::get('language'), function ($language) use ($user) {
                    $user->language = $language;
                    $user->save();
                }),
                ! empty($user->language) => $user->language, // Ensures null or empty check
                default => Setting::where('id', 1)->value('content') ?? 'en',
            };

            $this->setLocale($this->checkEnabledLanguage($lang));

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
            ! Cache::has('language') && isInstall() => Setting::select('content')->where('id', 1)->first()->content,
            default => 'en',
        };

        return $lang;
    }

    public function checkEnabledLanguage($lang)
    {
        if (! empty($lang)) {
            // Check if the language is enabled in the database
            $language = Language::where('locale', $lang)->where('enable_disable', 1)->first();

            if ($language) {
                return $lang;
            }
        }

        // Fallback to system default language from settings
        return Setting::where('id', 1)->value('content') ?? 'en';
    }
}
