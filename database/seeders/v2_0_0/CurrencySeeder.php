<?php

namespace Database\Seeders\v2_0_0;

use App\Model\Payment\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('currencies')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Currency::create([
            'id' => 1,
            'code' => 'AFN',
            'symbol' => '؋ ',
            'name' => 'Afghani',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 2,
            'code' => 'ALL',
            'symbol' => 'L',
            'name' => 'Albanian lek',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 3,
            'code' => 'DZD',
            'symbol' => ' دج  ',
            'name' => 'Algerian dinar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 4,
            'code' => 'WST',
            'symbol' => 'WS$',
            'name' => 'United States Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 5,
            'code' => 'ADF ',
            'symbol' => 'F',
            'name' => 'Andorran Franc',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 6,
            'code' => 'AOA',
            'symbol' => 'Kz',
            'name' => 'Kwanza',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 7,
            'code' => 'XCD',
            'symbol' => '$',
            'name' => 'East Caribbean Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 8,
            'code' => 'USD',
            'symbol' => '$',
            'name' => 'US Dollar',
            'dashboard_currency' => null,
            'status' => 1,
        ]);

        Currency::create([
            'id' => 10,
            'code' => 'ARS',
            'symbol' => '$',
            'name' => 'Argentine Peso',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 11,
            'code' => 'AMD',
            'symbol' => 'դր',
            'name' => 'Armenian Dram',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 12,
            'code' => 'AWG',
            'symbol' => 'ƒ',
            'name' => 'Aruban Guilder/Florin',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 13,
            'code' => 'AUD',
            'symbol' => '$',
            'name' => 'Australian Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 14,
            'code' => 'EUR',
            'symbol' => '€',
            'name' => 'Euro',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 15,
            'code' => 'AZN',
            'symbol' => 'AZN',
            'name' => 'Azerbaijanian Manat',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 16,
            'code' => 'BSD',
            'symbol' => '$',
            'name' => 'Bahamas Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 17,
            'code' => 'BHD',
            'symbol' => 'د.ب  ',
            'name' => 'Bahraini Dinar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 18,
            'code' => 'BDT',
            'symbol' => '৳',
            'name' => 'Taka',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 19,
            'code' => 'BBD',
            'symbol' => '$',
            'name' => 'Barbados Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 20,
            'code' => 'BYN',
            'symbol' => 'Br',
            'name' => 'Belarusian Ruble',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 22,
            'code' => 'BZD',
            'symbol' => '$',
            'name' => 'Belize Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 23,
            'code' => 'XOF',
            'symbol' => 'CFA',
            'name' => 'CFA Franc BCEAO',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 24,
            'code' => 'BMD',
            'symbol' => '$',
            'name' => 'Bermudian Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 25,
            'code' => 'BTN',
            'symbol' => 'Nu',
            'name' => 'Ngultrum',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 26,
            'code' => 'BOB',
            'symbol' => 'Bs',
            'name' => 'Boliviano',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 27,
            'code' => 'BAM',
            'symbol' => 'KM',
            'name' => 'Convertible Marka',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 28,
            'code' => 'BWP',
            'symbol' => 'P',
            'name' => 'Pula',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 30,
            'code' => 'BRL',
            'symbol' => 'R$',
            'name' => 'Brazilian Real',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 32,
            'code' => 'BND',
            'symbol' => '$',
            'name' => 'Brunei Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 33,
            'code' => 'BGN',
            'symbol' => 'Лв',
            'name' => 'Bulgarian Lev',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 34,
            'code' => 'XOF',
            'symbol' => 'CFA',
            'name' => 'CFA franc',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 35,
            'code' => 'BIF',
            'symbol' => 'FBu',
            'name' => 'Burundi Franc',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 36,
            'code' => 'KHR',
            'symbol' => '៛',
            'name' => 'Cambodian riel',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 37,
            'code' => 'XAF',
            'symbol' => 'FCFA',
            'name' => 'franc',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 38,
            'code' => 'CAD',
            'symbol' => '$',
            'name' => 'Canadian Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 39,
            'code' => 'CVE',
            'symbol' => '$',
            'name' => 'Cape Verde Escudo',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 40,
            'code' => 'KYD',
            'symbol' => '$',
            'name' => 'Caymanian Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 42,
            'code' => 'CAF',
            'symbol' => 'FCFA',
            'name' => 'Central African CFA franc ',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 43,
            'code' => 'CLP',
            'symbol' => '$',
            'name' => 'Chilean Peso',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 44,
            'code' => 'CNY',
            'symbol' => '¥',
            'name' => 'Yuan',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 47,
            'code' => 'COP',
            'symbol' => '$',
            'name' => 'Colombian Peso',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 48,
            'code' => 'KMF',
            'symbol' => 'CF',
            'name' => 'Comorian franc',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 50,
            'code' => 'CDF',
            'symbol' => '₣',
            'name' => 'Congolese Franc',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 51,
            'code' => 'NZD',
            'symbol' => '$',
            'name' => 'New Zealand Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 52,
            'code' => 'CRC',
            'symbol' => '¢',
            'name' => 'Costa Rican Colon',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 53,
            'code' => 'XOF',
            'symbol' => 'CFA',
            'name' => 'West African CFA franc',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 54,
            'code' => 'HRK',
            'symbol' => 'kn',
            'name' => 'Croatia Kuna',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 55,
            'code' => 'CUP',
            'symbol' => '$',
            'name' => 'Cuban Peso',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 57,
            'code' => 'CZK',
            'symbol' => 'Kc',
            'name' => 'Czech Republic Koruna',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 58,
            'code' => 'DKK',
            'symbol' => 'kr',
            'name' => 'Danish Krone',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 59,
            'code' => 'DJF',
            'symbol' => 'Fdj',
            'name' => '  Djibouti Franc',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 60,
            'code' => 'DOP',
            'symbol' => '$',
            'name' => 'Dominican Peso',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 62,
            'code' => 'ECS',
            'symbol' => '$',
            'name' => 'Ecuadorian sucre',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 63,
            'code' => 'EGP',
            'symbol' => '£',
            'name' => 'Egyptian Pound',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 64,
            'code' => 'SVC',
            'symbol' => '$',
            'name' => 'El Salvador Colon',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 66,
            'code' => 'ERN',
            'symbol' => 'Nfk',
            'name' => 'Nakfa',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 68,
            'code' => 'ETB',
            'symbol' => 'ብር ',
            'name' => 'Ethiopian Birr',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 69,
            'code' => 'FKP',
            'symbol' => '£',
            'name' => 'Falkland Islands Pound',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 70,
            'code' => 'FOK',
            'symbol' => 'kr',
            'name' => 'Faroese króna',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 71,
            'code' => 'FJD',
            'symbol' => '$',
            'name' => 'Fiji Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 78,
            'code' => 'GMD',
            'symbol' => 'D',
            'name' => 'Dalasi',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 79,
            'code' => 'GEL',
            'symbol' => ' ლ ',
            'name' => 'Lari',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 81,
            'code' => 'GHS',
            'symbol' => '‎GH₵',
            'name' => 'Cedi',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 82,
            'code' => 'GIP',
            'symbol' => '£',
            'name' => 'Gibraltar Pound',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 84,
            'code' => 'DKK',
            'symbol' => 'kr',
            'name' => 'krone',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 88,
            'code' => 'GTQ',
            'symbol' => 'Q',
            'name' => 'Quetzal',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 89,
            'code' => 'PGK',
            'symbol' => 'K',
            'name' => 'Kina',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 91,
            'code' => 'GYD',
            'symbol' => '$',
            'name' => 'Guyana Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 92,
            'code' => 'HTG',
            'symbol' => 'G',
            'name' => 'Gourde',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 94,
            'code' => 'VAL',
            'symbol' => '£',
            'name' => 'Vatican lira',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 95,
            'code' => 'HNL',
            'symbol' => 'L',
            'name' => 'Lempira',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 96,
            'code' => 'HKD',
            'symbol' => '$',
            'name' => 'Hong Kong Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 97,
            'code' => 'HUF',
            'symbol' => 'Ft',
            'name' => 'Forint',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 98,
            'code' => 'ISK',
            'symbol' => 'Kr',
            'name' => 'Iceland Krona',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 99,
            'code' => 'INR',
            'symbol' => '₹',
            'name' => 'Indian Rupee',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 100,
            'code' => 'IDR',
            'symbol' => 'Rp',
            'name' => 'Rupiah',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 101,
            'code' => 'IRR',
            'symbol' => 'ع.د ',
            'name' => 'Iranian Rial',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 102,
            'code' => 'IQD',
            'symbol' => 'ع.د ',
            'name' => 'Iraqi Dinar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 104,
            'code' => 'ILS',
            'symbol' => '₪',
            'name' => 'New Israeli Shekel',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 106,
            'code' => 'JMD',
            'symbol' => '$',
            'name' => 'Jamaican Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 107,
            'code' => 'JPY',
            'symbol' => '¥',
            'name' => 'Yen',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 108,
            'code' => 'JOD',
            'symbol' => ' د.ا',
            'name' => 'Jordanian Dinar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 109,
            'code' => 'KZT',
            'symbol' => '₸',
            'name' => 'Tenge',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 110,
            'code' => 'KES',
            'symbol' => 'Sh',
            'name' => 'Kenyan Shilling',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 113,
            'code' => 'KPW',
            'symbol' => '₩',
            'name' => 'North Korean Won',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 114,
            'code' => 'KWD',
            'symbol' => '‎ ‎د.ك  ',
            'name' => 'Kuwaiti Dinar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 115,
            'code' => 'KGS',
            'symbol' => 'KGS',
            'name' => 'Som',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 116,
            'code' => 'LAK',
            'symbol' => '₭',
            'name' => 'Lao Kip',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 118,
            'code' => 'LBP',
            'symbol' => 'ل.ل. ',
            'name' => 'Lebanese Pound',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 119,
            'code' => 'LSL',
            'symbol' => 'L',
            'name' => 'Loti',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 120,
            'code' => 'LRD',
            'symbol' => '$',
            'name' => 'Liberian Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 121,
            'code' => 'LYD',
            'symbol' => 'LYD',
            'name' => 'Libyan Dinar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 122,
            'code' => 'CHF',
            'symbol' => 'Fr',
            'name' => 'Swiss franc',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 125,
            'code' => 'MOP',
            'symbol' => 'P',
            'name' => ' Pataca',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 126,
            'code' => 'MKD',
            'symbol' => 'Ден',
            'name' => 'Denar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 127,
            'code' => 'MGA',
            'symbol' => 'MGA',
            'name' => 'Malagasy Ariary',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 128,
            'code' => 'MWK',
            'symbol' => 'MK',
            'name' => 'Kwacha',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 129,
            'code' => 'MYR',
            'symbol' => 'RM',
            'name' => 'Malaysian Ringgit',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 130,
            'code' => 'MVR',
            'symbol' => '‎Rf',
            'name' => 'Rufiyaa',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 135,
            'code' => 'MRO',
            'symbol' => 'UM',
            'name' => 'Ouguiya',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 136,
            'code' => 'MUR',
            'symbol' => '₨',
            'name' => 'Mauritius Rupee',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 138,
            'code' => 'MXN',
            'symbol' => '$',
            'name' => 'Mexican Peso',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 140,
            'code' => 'MDL',
            'symbol' => 'L',
            'name' => 'Moldavian Leu',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 142,
            'code' => 'MNT',
            'symbol' => '₮',
            'name' => 'Tugrik',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 144,
            'code' => 'MAD',
            'symbol' => 'DH',
            'name' => 'Moroccan Dirham',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 145,
            'code' => 'MZN',
            'symbol' => 'MTn',
            'name' => 'Metical',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 146,
            'code' => 'MMK',
            'symbol' => 'K',
            'name' => 'Kyat',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 147,
            'code' => 'NAD',
            'symbol' => '$',
            'name' => 'Namibia Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 149,
            'code' => 'NPR',
            'symbol' => 'Rs',
            'name' => 'Nepalese Rupee',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 152,
            'code' => 'XPF',
            'symbol' => '₣',
            'name' => 'CFP Franc',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 154,
            'code' => 'NIO',
            'symbol' => 'C$',
            'name' => 'Cordoba Oro',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 156,
            'code' => 'NGN',
            'symbol' => '₦',
            'name' => 'Naira',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 160,
            'code' => 'NOK',
            'symbol' => 'kr',
            'name' => 'Norwegian Krone',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 161,
            'code' => 'OMR',
            'symbol' => 'ر.ع. ',
            'name' => 'Rial Omani',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 162,
            'code' => 'PKR',
            'symbol' => '₨',
            'name' => 'Pakistan Rupee',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 167,
            'code' => 'PYG',
            'symbol' => '₲',
            'name' => 'Guarani',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 168,
            'code' => 'PEN',
            'symbol' => 'S/.',
            'name' => 'Nuevo Sol',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 169,
            'code' => 'PHP',
            'symbol' => '₱',
            'name' => 'Philippine Peso',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 171,
            'code' => 'PLN',
            'symbol' => 'zl',
            'name' => 'PZloty',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 174,
            'code' => 'QAR',
            'symbol' => 'ر.ق  ',
            'name' => 'Qatari Rial',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 175,
            'code' => 'EUR',
            'symbol' => '€',
            'name' => 'Réunion franc',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 176,
            'code' => 'RON',
            'symbol' => 'L',
            'name' => 'Leu',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 177,
            'code' => 'RUB',
            'symbol' => 'руб',
            'name' => 'Russian ruble',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 178,
            'code' => 'RWF',
            'symbol' => 'R₣',
            'name' => 'Rwanda Franc',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 179,
            'code' => 'SHP',
            'symbol' => '£',
            'name' => 'Saint Helena Pound',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 186,
            'code' => 'STD',
            'symbol' => 'Db',
            'name' => 'Dobra',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 187,
            'code' => 'SAR',
            'symbol' => 'ر.س  ',
            'name' => 'Saudi Riyal',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 189,
            'code' => 'RSD',
            'symbol' => 'din',
            'name' => 'Serbian Dinar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 190,
            'code' => 'SCR',
            'symbol' => 'SR',
            'name' => 'Seychelles Rupee',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 191,
            'code' => 'SLL',
            'symbol' => 'Le',
            'name' => 'Leone',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 195,
            'code' => 'SBD',
            'symbol' => '$',
            'name' => 'Solomon Islands Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 196,
            'code' => 'SOS',
            'symbol' => 'Sh',
            'name' => 'Somali Shilling',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 197,
            'code' => 'ZAR',
            'symbol' => 'R',
            'name' => 'Rand',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 200,
            'code' => 'LKR',
            'symbol' => 'Rs',
            'name' => 'Sri Lanka Rupee',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 201,
            'code' => 'SDG',
            'symbol' => '£',
            'name' => 'Sudanese Pound',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 202,
            'code' => 'SRD',
            'symbol' => '$',
            'name' => 'Suriname Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 203,
            'code' => 'NOK',
            'symbol' => 'NOK',
            'name' => 'Norwegian Krone',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 204,
            'code' => 'SZL',
            'symbol' => 'L',
            'name' => 'Lilangeni',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 205,
            'code' => 'SEK',
            'symbol' => 'kr',
            'name' => 'Swedish Krona',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 206,
            'code' => 'CHF',
            'symbol' => 'SFr',
            'name' => 'Swiss Franc',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 207,
            'code' => 'SYP',
            'symbol' => '£S',
            'name' => 'Syrian Pound',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 208,
            'code' => 'TWD',
            'symbol' => '$',
            'name' => 'Taiwan Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 209,
            'code' => 'TJS',
            'symbol' => 'ЅM',
            'name' => 'Somoni',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 210,
            'code' => 'TZS',
            'symbol' => 'Sh',
            'name' => 'Tanzanian Shilling',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 211,
            'code' => 'THB',
            'symbol' => '฿',
            'name' => 'Baht',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 215,
            'code' => 'TOP',
            'symbol' => 'T$',
            'name' => 'Paanga',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 216,
            'code' => 'TTD',
            'symbol' => '$',
            'name' => 'Trinidad and Tobago Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 217,
            'code' => 'TND',
            'symbol' => 'د.ت  ',
            'name' => 'Tunisian Dinar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 218,
            'code' => 'TRY',
            'symbol' => '£',
            'name' => 'Turkish Lira',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 219,
            'code' => 'TMT',
            'symbol' => 'm',
            'name' => 'Manat',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 222,
            'code' => 'UGX',
            'symbol' => 'Sh',
            'name' => 'Uganda Shilling ',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 223,
            'code' => 'UAH',
            'symbol' => '₴',
            'name' => 'Hryvnia',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 224,
            'code' => 'AED',
            'symbol' => 'د.إ ',
            'name' => 'UAE Dirham',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 225,
            'code' => 'GBP',
            'symbol' => '£',
            'name' => 'Pound Sterling',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 228,
            'code' => 'UYU',
            'symbol' => '$',
            'name' => 'Peso Uruguayo',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 229,
            'code' => 'UZS',
            'symbol' => 'UZS',
            'name' => 'Uzbekistan Sum',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 230,
            'code' => 'VUV',
            'symbol' => 'Vt',
            'name' => 'Vatu',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 231,
            'code' => 'VEF',
            'symbol' => 'Bs F',
            'name' => 'Bolivar Fuerte',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 232,
            'code' => 'VND',
            'symbol' => '₫',
            'name' => 'Dong',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 237,
            'code' => 'YER',
            'symbol' => '﷼   ',
            'name' => ' Yemeni Rial',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 238,
            'code' => 'ZMW',
            'symbol' => 'ZK',
            'name' => 'Zambian Kwacha',
            'dashboard_currency' => null,
            'status' => 0,
        ]);

        Currency::create([
            'id' => 239,
            'code' => 'ZWL',
            'symbol' => '$',
            'name' => 'Zimbabwe Dollar',
            'dashboard_currency' => null,
            'status' => 0,
        ]);
    }
}
