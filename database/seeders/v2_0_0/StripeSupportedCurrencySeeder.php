<?php

namespace Database\Seeders\v2_0_0;

use App\Plugins\Stripe\Model\StripePayment;
use Illuminate\Database\Seeder;

class StripeSupportedCurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        StripePayment::create([
            'currencies' => 'USD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'AED',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'AFN',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'ALL',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'AMD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'ANG',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'AOA',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'ARS',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'AUD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'AWG',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'AZN',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'BAM',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'BBD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'BDT',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'BGN',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'BIF',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'BMD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'BND',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'BOB',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'BRL',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'BSD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'BWP',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'BZD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'CAD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'CDF',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'CHF',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'CLP',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'CNY',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'COP',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'CRC',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'CVE',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'CZK',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'DJF',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'DKK',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'DOP',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'DZD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'EGP',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'ETB',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'EUR',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'FJD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'FKP',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'GBP',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'GEL',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'GIP',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'GMD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'GNF',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'GTQ',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'GYD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'HKD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'HNL',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'HRK',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'HTG',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'HUF',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'IDR',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'ILS',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'INR',
            'base_currency' => 'USD',
            'processing_fee' => '1',
        ]);

        StripePayment::create([
            'currencies' => 'ISK',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'JMD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'JPY',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'KES',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'KGS',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'KHR',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'KMF',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'KRW',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'KYD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'KZT',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'LAK',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'LBP',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'ZMW',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'ZAR',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'YER',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'XPF',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'XOF',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'XCD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'XAF',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'WST',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'VUV',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'VND',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'UZS',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'UYU',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'UGX',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'UAH',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'TZS',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'TWD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'TTD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'TOP',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'TJS',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'THB',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'SZL',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'STD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'SRD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'SOS',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'SLL',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'SHP',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'SGD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'SEK',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'SCR',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'SBD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'SAR',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'RWF',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'RUB',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'RSD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'RON',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'QAR',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'PYG',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'PLN',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'PKR',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'PHP',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'PGK',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'PEN',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'PAB',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'NZD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'NPR',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'NOK',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'NIO',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'NGN',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'NAD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'MZN',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'MYR',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'MXN',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'MWK',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'MVR',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'MUR',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'MRO',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'MOP',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'MNT',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'MMK',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'MKD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'MGA',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'MDL',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'MAD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'LSL',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'LRD',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);

        StripePayment::create([
            'currencies' => 'LKR',
            'base_currency' => 'USD',
            'processing_fee' => '5',
        ]);
    }
}
