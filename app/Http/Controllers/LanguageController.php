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

        $dbLanguages = \App\Model\Common\Language::pluck('status', 'locale')->toArray();

        foreach ($apiLanguages as $language) {
            $language->status = $dbLanguages[$language->locale] ?? 0;
        }

        return view('themes.default1.common.languages', [
            'languages' => $apiLanguages,
            'defaultLang' => $defaultLang,
        ]);
    }

    public function toggleLanguageStatus(Request $request)
    {
        try {
            $request->validate([
                'locale' => 'required|string',
                'status' => 'required|boolean',
            ]);

            $language = \App\Model\Common\Language::where('locale', $request->locale)->first();

            if ($language) {
                $language->status = $request->status;
                $language->save();
                return response()->json(['success' => true, 'message' => __('message.language_status_updated_successfully')]);
            }

            return response()->json(['success' => false, 'message' =>  __('message.language_not_found')], 404);
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function fetchLangDropdownUsers()
    {
        try {
            $languageList = array_map('basename', File::directories(lang_path()));
            $languages = [];
            $dbLanguages = Language::all()->keyBy('locale');

            foreach ($languageList as $key => $langLocale) {
                $language = [];
                $language['id'] = $key;
                $language['locale'] = $langLocale;

                $languageArray = \Config::get("languages.$langLocale", ['', '']);
                $language['name'] = $languageArray[0];
                $language['translation'] = $languageArray[1];

                $language['status'] = $dbLanguages[$langLocale]->status ?? 0;

                $languages[] = $language;
            }

            return successResponse('', collect($languages)->sortBy('name')->values()->all());
        } catch (\Exception $exception) {
            \Log::error($exception);

            return errorResponse($exception->getMessage());
        }
    }
}
