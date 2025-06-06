<?php

namespace Database\Seeders\v4_0_2_4_RC_1;

use App\ApiKey;
use App\Model\Common\Msg91Status;
use App\Http\Controllers\Common\PipedriveController;
use App\Model\Common\PipedriveGroups;
use App\Model\Common\PipedriveLocalFields;
use App\Model\Github\Github;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Product\Product;
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
        $this->addFielsForPipedrive();
        $this->invoiceItemProductIDChange();
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
        $env = base_path() . DIRECTORY_SEPARATOR . '.env';

        if (is_file($env) && config('app.env') !== 'testing' && env('APP_KEY_UPDATED') !== 'true') {

            setEnvValue(['APP_PREVIOUS_KEYS' => 'SomeRandomString']);

            \Artisan::call('key:generate', ['--force' => true]);

            setEnvValue(['APP_KEY_UPDATED' => 'true']);
        }
    }
    private function addFielsForPipedrive()
    {
        $fields = [
            ['field_name' => 'User Name', 'field_key' => 'user_name'],
            ['field_name' => 'First Name', 'field_key' => 'first_name'],
            ['field_name' => 'Last Name', 'field_key' => 'last_name'],
            ['field_name' => 'Email', 'field_key' => 'email'],
            ['field_name' => 'Mobile', 'field_key' => 'mobile'],
            ['field_name' => 'Company', 'field_key' => 'company'],
            ['field_name' => 'Address', 'field_key' => 'address'],
            ['field_name' => 'Town', 'field_key' => 'town'],
            ['field_name' => 'State', 'field_key' => 'state'],
            ['field_name' => 'Country', 'field_key' => 'country'],
            ['field_name' => 'Created At', 'field_key' => 'created_at'],
        ];

        $groups = [
            ['group_name' => 'Person'],
            ['group_name' => 'Organization'],
            ['group_name' => 'Deal'],
        ];

        foreach ($fields as $field) {
            PipedriveLocalFields::updateOrCreate(
                ['field_key' => $field['field_key']],
                $field
            );
        }

        foreach ($groups as $group) {
            PipedriveGroups::updateOrCreate(
                ['group_name' => $group['group_name']],
                $group
            );
        }
    }
    public function invoiceItemProductIDChange()
    {
        $orders = Order::all();
        foreach ($orders as $order) {
            // Make sure invoice_item_id and product_id exist in the order
            if ($order->invoice_item_id && $order->product) {
                // Find the invoice item
                $invoiceItem = InvoiceItem::find($order->invoice_item_id);
                $product = Product::find($order->product);
                if ($invoiceItem && $product) {
                    $invoiceItem->product_id = $product->id;
                    $invoiceItem->save();
                }
            }
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

        foreach ($languages as $lang) {
            \DB::table('languages')->updateOrInsert(
                ['locale' => $lang['locale']],
                ['name' => $lang['name'], 'translation' => $lang['translation']]
            );
        }

    }
}