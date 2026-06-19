<?php

namespace App\License\Controllers\Admin;

use App\Http\Controllers\Controller;

class LanguageController extends Controller
{
    public function getLanguageFile(): \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
    {
        $locale = app()->getLocale() ?: 'en';
        $fallback = 'en';
        $languages = array_unique([$fallback, $locale]);
        $languageArray = [];

        foreach ($languages as $lang) {
            $this->appendLicenseLanguage($lang, $languageArray);
        }

        return response('translator = '.json_encode($languageArray).';', 200, [
            'Content-Type' => 'text/javascript',
            'Cache-Control' => 'max-age=2592000',
        ]);
    }

    /**
     * @param array<mixed> $languageArray
     */
    private function appendLicenseLanguage(string $languageName, array &$languageArray): void
    {
        $basePath = base_path('app/License/Lang');
        $path = $basePath.'/'.$languageName;

        if (! is_dir($path)) {
            return;
        }

        $files = glob($path.DIRECTORY_SEPARATOR.'*.php');

        foreach ($files as $file) {
            $name = basename($file, '.php');
            $values = require $file;
            $languageArray[$name] = array_merge($languageArray[$name] ?? [], $values);
        }
    }
}
