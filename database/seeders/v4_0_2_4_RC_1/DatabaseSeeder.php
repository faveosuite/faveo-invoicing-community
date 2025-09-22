<?php

namespace Database\Seeders\v4_0_2_4_RC_1;

use App\ApiKey;
use App\Model\Common\EmailMobileValidationProviders;
use App\Model\Common\Msg91Status;
use App\Http\Controllers\Common\PipedriveController;
use App\Model\Common\PipedriveGroups;
use App\Model\Common\PipedriveLocalFields;
use App\Model\Common\PricingTemplate;
use App\Model\Github\Github;
use App\Model\Mailjob\ExpiryMailDay;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use Carbon\Carbon;
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
        $this->add_providers();
        $this->invoiceItemProductIDChange();
        $this->langSeeder();
        $this->update_is_deleted();
        PricingTemplate::where('id',1)->update(['data' => '<div class="">
        <div class="card border-radius-0 bg-color-light box-shadow-6 anim-hover-translate-top-10px transition-3ms">
            <div class="card-body py-5">
    
                <div class="pricing-block">
                    <div class="text-center">
                        <h4 class="text-color-primary">{{name}}</h4>
                        <h4 class="text-color-primary">{{product_description}}</h4>
                        <div class="content-switcher-wrapper">
                            <div class="content-switcher left-50pct transform3dx-n50 active" data-content-switcher-id="pricingTable1" data-content-switcher-rel="1">
                                <div class="plan-price bg-transparent mb-4">
                                    <span class="price text-color-primary">{{price-year}}</span>
                                    <span class="strike" style="margin-bottom: 5px">{{strike-priceyear}}</span>
                                    <label class="price-label" style="margin-bottom: 5px">{{price-description}}</label>
                                    <div class="subscription table-responsive">{{subscription}}</div>
    <div class="text-center mt-4 pt-2">
                       {{url}}
                    </div>
                                </div>
                            </div>
                            <div class="content-switcher left-50pct transform3dx-n50" data-content-switcher-id="pricingTable1" data-content-switcher-rel="2">
                                <div class="plan-price bg-transparent mb-4">
                                    <span class="price text-color-primary">{{price}}</span>
                                    <span class="strike" style="margin-bottom: 5px">{{strike-price}}</span>
                                    <label class="price-label" style="margin-bottom: 5px">{{pricemonth-description}}</label>
                                    <div class="subscription table-responsive">{{subscription}}</div>
    <div class="text-center mt-4 pt-2">
                       {{url}}
                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
    <div class="plan-features blue">
    
                    
                        <li>{{feature}}</li>
                                            
                   
    </div>
    
                    
    
                </div>
    
            </div>
        </div>
    </div>']);


        PricingTemplate::where('id',2)->update(['data' => '<div class="">
        <div class="card border-radius-0 bg-color-light box-shadow-6 anim-hover-translate-top-10px transition-3ms">
            <div class="card-body py-5">
    
                <div class="pricing-block">
                    <div class="text-center">
                        <h4 class="">{{name}}</h4>
                        
                        <div class="content-switcher-wrapper">
                            <div class="content-switcher left-50pct transform3dx-n50 active" data-content-switcher-id="pricingTable1" data-content-switcher-rel="1">
                                <div class="plan-price bg-transparent mb-4">
                                    <span class="price">{{price-year}}</span>
                                    <span class="strike" style="margin-bottom: 5px">{{strike-priceyear}}</span>
                                    <label class="price-label" style="margin-bottom: 5px">{{price-description}}</label>
                                    <div class="subscription table-responsive">{{subscription}}</div>

<div class="text-center mt-4 pt-2">
                       {{url}}
                    </div>
                   

                                </div>
                            </div>
                            <div class="content-switcher left-50pct transform3dx-n50" data-content-switcher-id="pricingTable1" data-content-switcher-rel="2">
                                <div class="plan-price bg-transparent mb-4">
                                    <span class="price">{{price}}</span>
                                    <span class="strike" style="margin-bottom: 5px">{{strike-price}}</span>
                                    <label class="price-label" style="margin-bottom: 5px">{{pricemonth-description}}</label>
                                    <div class="subscription table-responsive">{{subscription}}</div>

 <div class="text-center mt-4 pt-2">
                       {{url}}
                    </div>
                    
                                </div>
                            </div>
                        </div>
                    </div>
    <div class="plan-features">
    
                        <li>{{feature}}</li>
                                            
    </div>
    
                   
    
                </div>
    
            </div>
        </div>
    </div>
    ']);
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

    public function removeOldGitPassword()
    {
        Github::where('id', 1)->update(['password' => null]);
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

    public function update_is_deleted(){
        $today= Carbon::today();
        $day = ExpiryMailDay::value('cloud_days');
        Subscription::whereNotNull('ends_at')
        ->whereIn('product_id',cloudPopupProducts())->whereDate(
            \DB::raw("DATE_ADD(ends_at, INTERVAL {$day} DAY)"),
            '<',
            $today
        )->update(['is_deleted'=>1]);
    }



    public function add_providers(){
        $providers =[ ['provider'=>'reoon','type'=>'email'],
            ['provider'=>'vonage','type'=>'mobile'],
            ['provider'=>'abstract','type'=>'mobile'],
        ];
        foreach ($providers as $provider) {
            EmailMobileValidationProviders::updateOrCreate([
                'provider' => $provider['provider'],
                'type' => $provider['type'],
            ]);
        }
    }
}