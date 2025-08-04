<?php

namespace database\seeders\v2_0_0;

use App\Plugins\Razorpay\Model\RazorpayPayment;
use Illuminate\Database\Seeder;

class RazorpaySupportedCurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RazorpayPayment::create([
            'currencies' => 'INR',
            'base_currency' => 'INR',
            'processing_fee' => '0',
        ]);

        RazorpayPayment::create([
            'currencies' => 'USD',
            'base_currency' => 'INR',
            'processing_fee' => '0',
        ]);
    }
}
