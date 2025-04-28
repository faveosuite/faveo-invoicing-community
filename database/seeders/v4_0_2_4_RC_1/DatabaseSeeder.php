<?php

namespace Database\Seeders\v4_0_2_4_RC_1;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $languages = [
            ["locale" => "ar", "name" => "Arabic", "translation" => "العربية"],
            ["locale" => "bsn", "name" => "Bosnian", "translation" => "босански"],
            ["locale" => "zh-hans", "name" => "Chinese", "translation" => "中文 [Simplified]"],
            ["locale" => "zh-hant", "name" => "Chinese", "translation" => "中文 [Traditional]"],
            ["locale" => "nl", "name" => "Dutch", "translation" => "Vlaams"],
            ["locale" => "en", "name" => "English  - United States", "translation" => "English"],
            ["locale" => "en-gb", "name" => "English - United Kingdom", "translation" => "English"],
            ["locale" => "fr", "name" => "French", "translation" => "français"],
            ["locale" => "de", "name" => "German", "translation" => "Deutsch"],
            ["locale" => "he", "name" => "Hebrew", "translation" => "עברית"],
            ["locale" => "hi", "name" => "Hindi", "translation" => "हिन्दी"],
            ["locale" => "id", "name" => "Indonesian", "translation" => "Bahasa Indonesia"],
            ["locale" => "it", "name" => "Italian", "translation" => "Italiano"],
            ["locale" => "ja", "name" => "Japanese", "translation" => "日本語 [にほんご]"],
            ["locale" => "kr", "name" => "Korean", "translation" => "한국어"],
            ["locale" => "mt", "name" => "Maltese", "translation" => "Malti"],
            ["locale" => "no", "name" => "Norwegian", "translation" => "Norsk"],
            ["locale" => "pt", "name" => "Portuguese", "translation" => "Português"],
            ["locale" => "ru", "name" => "Russian", "translation" => "Русский"],
            ["locale" => "es", "name" => "Spanish", "translation" => "Español"],
            ["locale" => "ta", "name" => "Tamil", "translation" => "தமிழ்"],
            ["locale" => "tr", "name" => "Turkish", "translation" => "Türkçe"],
            ["locale" => "vi", "name" => "Vietnamese", "translation" => "Tiếng Việt"],
        ];

        foreach ($languages as &$lang) {
            $lang['enable_disable'] = 1; // default enable all languages
        }

        DB::table('languages')->insert($languages);
    }
}


