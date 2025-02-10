<?php

namespace Database\Seeders\v4_0_2_4_RC_1;

use App\ApiKey;
use App\Model\Common\Msg91Status;
use App\Model\Github\Github;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->addMsgStatus();
        $this->removeOldGitPassword();
        $this->updateAppKey();
        $this->langSeeder();
    }

    public function addMsgStatus()
    {
        $statuses = [
            ['status_code' => 0, 'status_label' => 'Pending'],
            ['status_code' => 1, 'status_label' => 'Delivered'],
            ['status_code' => 2, 'status_label' => 'Failed'],
            ['status_code' => 9, 'status_label' => 'NDNC'],
            ['status_code' => 16, 'status_label' => 'Rejected'],
            ['status_code' => 25, 'status_label' => 'Rejected'],
            ['status_code' => 17, 'status_label' => 'Blocked number'],
        ];

        foreach ($statuses as $status) {
            Msg91Status::updateOrCreate(
                ['status_code' => $status['status_code']],
                ['status_label' => $status['status_label']]
            );
        }
    }

    public function removeOldGitPassword(){
        Github::where('id',1)->update(['password' => null]);
    }

    private function updateAppKey()
    {
        $env = base_path().DIRECTORY_SEPARATOR.'.env';

        if (is_file($env) && config('app.env') !== 'testing' && env('APP_KEY_UPDATED') !== 'true') {

            setEnvValue(['APP_PREVIOUS_KEYS' => 'SomeRandomString']);

            \Artisan::call('key:generate', ['--force' => true]);

            setEnvValue(['APP_KEY_UPDATED' => 'true']);

        }
    }
    public function langSeeder()
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

        \DB::table('languages')->insert($languages);

    }
}