<?php

namespace Database\Seeders\v4_0_2_4;

use App\ApiKey;
use App\Model\Common\EmailMobileValidationProviders;
use App\Model\Common\ManagerSetting;
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
        $this->addMangerRole();

        PricingTemplate::where('id',1)->update(['data' => '<div class="">
        <div class="card border-radius-0 bg-color-light box-shadow-6 anim-hover-translate-top-10px transition-3ms">
            <div class="card-body py-5">
    
                <div class="pricing-block">
                    <div class="text-center">
                        <h4 class="text-color-primary">{{name}}</h4>
                            <h6 class="text-color-primary">{{product_description}}</h6>

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
                            <h6 class="text-color-primary">{{product_description}}</h6>

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

    public function addMangerRole(): void
    {
        foreach (['account', 'sales'] as $role) {
            ManagerSetting::updateOrCreate(
                ['manager_role' => $role],
                ['auto_assign' => 0]
            );
        }
    }
}