<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BillingInstaller\InstallerController;
use App\Model\Common\Language;
use App\Model\Common\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class LanguageController extends Controller
{
    public function viewLanguage()
    {
        $response = (new InstallerController())->languageList();
        $apiLanguages = $response->getData()->data ?? [];
        $defaultLang = optional(Setting::first())->content;

        $dbLanguages = \App\Model\Common\Language::pluck('enable_disable', 'locale')->toArray();

        // Attach enable_disable status to each language
        foreach ($apiLanguages as $language) {
            $language->enable_disable = $dbLanguages[$language->locale] ?? 0;
        }

        return view('themes.default1.common.languages', [
            'languages' => $apiLanguages,
            'defaultLang' => $defaultLang,
        ]);
    }

    public function toggleLanguageStatus(Request $request)
    {
        try {
            // Validate the incoming request data
            $request->validate([
                'locale' => 'required|string',
                'status' => 'required|boolean',
            ]);

            // Find the language using the locale
            $language = \App\Model\Common\Language::where('locale', $request->locale)->first();

            // If the language is found, update its status
            if ($language) {
                $language->enable_disable = $request->status;
                $language->save();

                return response()->json(['success' => true, 'message' => 'Language status updated successfully.']);
            }

            // If language not found, return a 404 response
            return response()->json(['success' => false, 'message' => 'Language not found.'], 404);
        } catch (\Exception $e) {
            // If any error occurs, return a failure message
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function fetchLangDropdownUsers()
    {
        try {
            // Get list of language directories
            $languageList = array_map('basename', File::directories(lang_path()));
            $languages = [];

            // Fetch all database language entries indexed by locale
            $dbLanguages = Language::all()->keyBy('locale');

            foreach ($languageList as $key => $langLocale) {
                $language = [];
                $language['id'] = $key;
                $language['locale'] = $langLocale;

                // Get name and translation from config
                $languageArray = \Config::get("languages.$langLocale", ['', '']);
                $language['name'] = $languageArray[0];
                $language['translation'] = $languageArray[1];

                // Get enable_disable from the DB (if exists)
                $language['enable_disable'] = $dbLanguages[$langLocale]->enable_disable ?? 0;

                $languages[] = $language;
            }

            return successResponse('', collect($languages)->sortBy('name')->values()->all());
        } catch (\Exception $exception) {
            \Log::error($exception);

            return errorResponse($exception->getMessage());
        }
    }
}
