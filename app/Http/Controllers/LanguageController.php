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
    public function getLanguageFile()
    {
        $languages = array_unique([Lang::getFallback(), App::getLocale()]);

        $languageArray = [];

        foreach ($languages as $lang) {
            $this->appendCoreLanguage($lang, $languageArray);
        }

        header('Content-Type: text/javascript');
        header('Cache-Control: max-age=2592000');
        echo 'translator = '.json_encode($languageArray).';';
        exit();
    }

    private function appendCoreLanguage(string $languageName, array &$languageArray): void
    {
        $path = base_path('lang/'.$languageName);
        $this->updateLanguageArray($path, $languageArray);
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
                ->simplePaginate($limit);

            $defaultLang = Setting::value('content') ?? 'en';

            return successResponse(__('message.language_fetched'), [
                'languages' => $languages,
                'default_language' => $defaultLang,
            ]);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function toggleLanguageStatus(Request $request)
    {
        try {
            $request->validate([
                'locale' => 'required|string',
                'status' => 'required|boolean',
            ]);

            $language = Language::where('locale', $request->locale)->first();

            if ($language) {
                $languageById = Language::find($language->id);

                if ($languageById) {
                    $languageById->update([
                        'status' => $request->status,
                    ]);

                    return successResponse(__('message.language_status_updated_successfully'));
                }
            }

            return errorResponse(__('message.language_not_found'));
        } catch (\Exception $e) {
            return errorResponse(__('message.something_went_wrong'));
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
