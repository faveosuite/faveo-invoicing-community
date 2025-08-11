<?php

namespace Database\Seeders\v4_0_2_4_RC_2;

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