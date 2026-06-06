<?php

namespace App\Http\Controllers;

use App\Model\Common\Language;
use App\Model\Common\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;

class LanguageController extends Controller
{
    public function __construct()
    {
        // getLanguageFile serves the public translation strings (window.translator)
        // consumed by every SPA page, including the guest login/register/verify
        // pages — so it must not require auth/admin.
        $this->middleware(['auth', 'admin'], ['except' => ['fetchLangDropdownUsers', 'getLanguageFile']]);
    }

    public function getLanguageFile()
    {
        $languages = array_unique([Lang::getFallback(), App::getLocale()]);

        $languageArray = [];

        foreach ($languages as $lang) {
            $this->appendCoreLanguage($lang, $languageArray);
            $this->appendPackageLanguage('BillingLog', $lang, 'log', $languageArray);
            $this->appendRecaptchaLanguage($lang, $languageArray);
            $this->appendLicenseLanguage($lang, $languageArray);
        }

        header('Content-Type: text/javascript');
        header('Cache-Control: no-store');
        echo 'translator = '.json_encode($languageArray).';';
        exit;
    }

    private function appendCoreLanguage(string $languageName, array &$languageArray): void
    {
        $path = base_path('lang/'.$languageName);
        $this->updateLanguageArray($path, $languageArray);
    }

    private function appendRecaptchaLanguage(string $locale, array &$languageArray): void
    {
        $path = app_path("Plugins/Recaptcha/resources/lang/{$locale}/recaptcha.php");

        if (! is_file($path)) {
            return;
        }

        $languageArray['recaptcha'] = array_merge($languageArray['recaptcha'] ?? [], require $path);
    }

    private function appendLicenseLanguage(string $locale, array &$languageArray): void
    {
        $path = app_path("License/Lang/{$locale}");
        foreach ($this->getLanguageFileArray($path) as $file) {
            $content = require $file;
            $languageArray['lang'] = array_merge($languageArray['lang'] ?? [], $content);
        }
    }

    private function appendPackageLanguage(string $package, string $locale, string $namespace, array &$languageArray): void
    {
        $path = app_path("{$package}/lang/{$locale}");
        foreach ($this->getLanguageFileArray($path) as $file) {
            $content = require $file;
            $languageArray[$namespace] = array_merge($languageArray[$namespace] ?? [], $content);
        }
    }

    private function updateLanguageArray(string $path, array &$languageArray): void
    {
        $files = $this->getLanguageFileArray($path);
        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (array_key_exists($name, $languageArray)) {
                $languageArray[$name] = array_merge($languageArray[$name], require $file);
            } else {
                $languageArray[$name] = require $file;
            }
        }
    }

    private function getLanguageFileArray(string $path): array
    {
        if (! is_dir($path)) {
            return [];
        }

        return glob($path.DIRECTORY_SEPARATOR.'*.php');
    }

    public function viewLanguage(Request $request)
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'asc');
            $sortField = $request->input('sort-field', 'name');
            $limit = $request->input('limit', 10);

            $languages = Language::when($searchString, function ($query) use ($searchString) {
                $query->where('name', 'like', "%$searchString%")
                    ->orWhere('locale', 'like', "%$searchString%");
            })
                ->orderBy($sortField, $sortOrder)
                ->paginate($limit);

            $defaultLocale = Setting::value('content') ?: 'en';
            $result = $languages->toArray();
            $result['data'] = array_map(function ($lang) use ($defaultLocale) {
                $lang['is_default'] = $lang['locale'] === $defaultLocale;

                return $lang;
            }, $result['data']);

            return successResponse(__('message.language_fetched'), $result);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function toggleLanguageStatus(Request $request)
    {
        try {
            $request->validate([
                'locale' => 'required|string|exists:languages,locale',
                'status' => 'required|boolean',
            ]);

            $language = Language::where('locale', $request->input('locale'))->firstOrFail();
            $language->status = (int) $request->boolean('status');
            $language->save();

            return successResponse(__('message.language_status_updated_successfully'));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function setDefaultLanguage(Request $request)
    {
        try {
            $request->validate(['locale' => 'required|string|exists:languages,locale']);

            $setting = Setting::first();
            $setting->content = $request->input('locale');
            $setting->save();

            return successResponse(__('message.language_set_as_default'));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function fetchLangDropdownUsers()
    {
        try {
            $languageList = array_map('basename', File::directories(lang_path()));
            $dbLanguages = Language::all()->keyBy('locale');

            $languages = [];

            foreach ($languageList as $key => $langLocale) {
                $languageConfig = \Config::get("languages.$langLocale", ['', '']);

                $languages[] = [
                    'id' => $key,
                    'locale' => $langLocale,
                    'name' => $languageConfig[0] ?? $langLocale,
                    'translation' => $languageConfig[1] ?? '',
                    'status' => $dbLanguages[$langLocale]->status ?? 0,
                ];
            }

            $languages = collect($languages)->sortBy('name')->values()->all();

            return successResponse(__('message.language_fetched'), $languages);
        } catch (\Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }
}
