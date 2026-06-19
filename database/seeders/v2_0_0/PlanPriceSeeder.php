<?php

namespace Database\Seeders\v2_0_0;

use App\Model\Common\Setting;
use App\Model\Payment\PlanPrice;
use Illuminate\Database\Seeder;

class PlanPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->mapCountriesToCurrency();
    }

    private function mapCountriesToCurrency(): void
    {
        /** @var \App\Model\Common\Setting $settingForCurrency */
        $settingForCurrency = new Setting()->first();
        $nonDefaultCurrencies = PlanPrice::where(
            'currency', '=', $settingForCurrency->default_currency
        )->get(['id', 'currency']);
        if ($nonDefaultCurrencies) { // @phpstan-ignore if.alwaysTrue
            foreach ($nonDefaultCurrencies as $currency) {
                $currency->update(['country_id' => 0]);
            }
        }
    }
}
