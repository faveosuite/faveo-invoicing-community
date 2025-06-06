<?php

namespace App\Http\Controllers;

use App\Model\Common\Language;
use App\Model\Common\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LanguageController extends Controller
{
    public function viewLanguage()
    {
        $dbLanguages = \App\Model\Common\Language::all();
        $defaultLang = Setting::first()->value('content') ?? 'en';

        return view('themes.default1.common.languages', [
            'languages' => $dbLanguages,
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

            $language = Language::where('locale', $request->locale)->first();

            if ($language) {
                $languageById = Language::find($language->id);

                if ($languageById) {
                    $languageById->status = $request->status;
                    $languageById->save();

                    return response()->json([
                        'success' => true,
                        'message' => __('message.language_status_updated_successfully')
                    ]);
                }
            }

            return response()->json(['success' => false, 'message' => __('message.language_not_found')], 404);
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
