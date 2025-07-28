<?php

namespace Database\Seeders\v2_0_0;

use App\Model\Common\Setting;
use App\Model\Payment\PlanPrice;
use Illuminate\Database\Seeder;

class PlanPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->mapCountriesToCurrency();
    }

    private function mapCountriesToCurrency()
    {
        $nonDefaultCurrencies = PlanPrice::where(
            'currency', '=', (new Setting())->first()->default_currency
        )->get(['id', 'currency']);
        if ($nonDefaultCurrencies) {
            foreach ($nonDefaultCurrencies as $currency) {
                $currency->update(['country_id' => 0]);
            }
        }
    }
}
