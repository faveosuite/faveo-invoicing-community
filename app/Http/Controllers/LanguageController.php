<?php

namespace App\Http\Controllers;

use App\Model\Common\Language;
use App\Model\Common\Setting;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;

class LanguageController extends Controller
{
    public function __construct()
    {
        // getLanguageFile serves the public translation strings (window.translator)
        // consumed by every SPA page, including the guest login/register/verify
        // pages — so it must not require auth/admin.
        $this->middleware(['auth', 'admin'], ['except' => ['getLanguageFile']]);
    }

    public function getLanguageFile(): void
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

    /**
     * @param array<mixed> $languageArray
     */
    private function appendCoreLanguage(string $languageName, array &$languageArray): void
    {
        $path = base_path('lang/'.$languageName);
        $this->updateLanguageArray($path, $languageArray);
    }

    /**
     * @param array<mixed> $languageArray
     */
    private function appendRecaptchaLanguage(string $locale, array &$languageArray): void
    {
        $path = app_path(sprintf('Plugins/Recaptcha/resources/lang/%s/recaptcha.php', $locale));

        if (! is_file($path)) {
            return;
        }

        $languageArray['recaptcha'] = array_merge($languageArray['recaptcha'] ?? [], require $path);
    }

    /**
     * @param array<mixed> $languageArray
     */
    private function appendLicenseLanguage(string $locale, array &$languageArray): void
    {
        $path = app_path('License/Lang/'.$locale);
        foreach ($this->getLanguageFileArray($path) as $file) {
            $content = require $file;
            $languageArray['lang'] = array_merge($languageArray['lang'] ?? [], $content);
        }
    }

    /**
     * @param array<mixed> $languageArray
     */
    private function appendPackageLanguage(string $package, string $locale, string $namespace, array &$languageArray): void
    {
        $path = app_path(sprintf('%s/lang/%s', $package, $locale));
        foreach ($this->getLanguageFileArray($path) as $file) {
            $content = require $file;
            $languageArray[$namespace] = array_merge($languageArray[$namespace] ?? [], $content);
        }
    }

    /**
     * @param array<mixed> $languageArray
     */
    private function updateLanguageArray(string $path, array &$languageArray): void
    {
        $files = $this->getLanguageFileArray($path);
        foreach ($files as $file) {
            $name = basename((string) $file, '.php');
            if (array_key_exists($name, $languageArray)) {
                $languageArray[$name] = array_merge($languageArray[$name], require $file);
            } else {
                $languageArray[$name] = require $file;
            }
        }
    }

    /**
     * @return array<mixed>
     */
    private function getLanguageFileArray(string $path): array
    {
        if (! is_dir($path)) {
            return [];
        }

        return glob($path.DIRECTORY_SEPARATOR.'*.php');
    }

    public function viewLanguage(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'asc');
            $sortField = $request->input('sort-field', 'name');
            $limit = $request->input('limit', 10);

            $languages = Language::when($searchString, function ($query) use ($searchString): void {
                $query->where('name', 'like', sprintf('%%%s%%', $searchString))
                    ->orWhere('locale', 'like', sprintf('%%%s%%', $searchString));
            })
                ->orderBy($sortField, $sortOrder)
                ->paginate($limit);

            $defaultLocale = Setting::value('content') ?: 'en';
            $result = $languages->toArray();
            $result['data'] = array_map(function (array $lang) use ($defaultLocale): array {
                $lang['is_default'] = $lang['locale'] === $defaultLocale;

                return $lang;
            }, $result['data']);

            return successResponse(__('message.language_fetched'), $result);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function toggleLanguageStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'locale' => ['required', 'string', 'exists:languages,locale'],
                'status' => ['required', 'boolean'],
            ]);

            $language = Language::where('locale', $request->input('locale'))->firstOrFail();
            $language->status = (int) $request->boolean('status');
            $language->save();

            return successResponse(__('message.language_status_updated_successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function setDefaultLanguage(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate(['locale' => ['required', 'string', 'exists:languages,locale']]);

            /** @var \App\Model\Common\Setting $setting */
            $setting = Setting::firstOrFail();
            $setting->content = $request->input('locale');
            $setting->save();

            return successResponse(__('message.language_set_as_default'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }
}
