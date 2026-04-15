<?php

namespace App\Modules\License\Controllers\Admin;

use App\Http\Controllers\Controller;

class LanguageController extends Controller
{
    public function getLanguageFile()
    {
        $locale = app()->getLocale() ?: 'en';
        $fallback = 'en';

        $languages = array_unique([$fallback, $locale]);

        $languageArray = [];

        foreach ($languages as $lang) {
            $this->appendLicenseLanguage($lang, $languageArray);
        }

        header('Content-Type: text/javascript');
        header('Cache-Control: max-age=2592000');

        echo 'translator = '.json_encode($languageArray).';';
        exit;
    }

    private function appendLicenseLanguage(string $languageName, array &$languageArray): void
    {
        $basePath = base_path('app/Modules/License/Lang');
        $path = $basePath.'/'.$languageName;

        if (! is_dir($path)) {
            return;
        }

        $files = glob($path.DIRECTORY_SEPARATOR.'*.php');

        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (array_key_exists($name, $languageArray)) {
                $languageArray[$name] = array_merge($languageArray[$name], require $file);
            } else {
                $languageArray[$name] = require $file;
            }
        }
    }
}
