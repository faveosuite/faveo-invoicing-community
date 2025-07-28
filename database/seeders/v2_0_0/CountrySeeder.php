<?php

namespace Database\Seeders\v2_0_0;

use App\Model\Common\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('countries')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Country::create([
            'country_id' => 1,
            'country_code_char2' => 'AF',
            'country_name' => 'AFGHANISTAN',
            'nicename' => 'Afghanistan',
            'country_code_char3' => 'AFG',
            'numcode' => 4,
            'phonecode' => 93,
            'currency_id' => '1',
        ]);

        Country::create([
            'country_id' => 2,
            'country_code_char2' => 'AL',
            'country_name' => 'ALBANIA',
            'nicename' => 'Albania',
            'country_code_char3' => 'ALB',
            'numcode' => 8,
            'phonecode' => 355,
            'currency_id' => '2',
        ]);

        Country::create([
            'country_id' => 3,
            'country_code_char2' => 'DZ',
            'country_name' => 'ALGERIA',
            'nicename' => 'Algeria',
            'country_code_char3' => 'DZA',
            'numcode' => 12,
            'phonecode' => 213,
            'currency_id' => '3',
        ]);

        Country::create([
            'country_id' => 4,
            'country_code_char2' => 'AS',
            'country_name' => 'AMERICAN SAMOA',
            'nicename' => 'American Samoa',
            'country_code_char3' => 'ASM',
            'numcode' => 16,
            'phonecode' => 1684,
            'currency_id' => '4',
        ]);

        Country::create([
            'country_id' => 5,
            'country_code_char2' => 'AD',
            'country_name' => 'ANDORRA',
            'nicename' => 'Andorra',
            'country_code_char3' => 'AND',
            'numcode' => 20,
            'phonecode' => 376,
            'currency_id' => '5',
        ]);

        Country::create([
            'country_id' => 6,
            'country_code_char2' => 'AO',
            'country_name' => 'ANGOLA',
            'nicename' => 'Angola',
            'country_code_char3' => 'AGO',
            'numcode' => 24,
            'phonecode' => 244,
            'currency_id' => '6',
        ]);

        Country::create([
            'country_id' => 7,
            'country_code_char2' => 'AI',
            'country_name' => 'ANGUILLA',
            'nicename' => 'Anguilla',
            'country_code_char3' => 'AIA',
            'numcode' => 660,
            'phonecode' => 1264,
            'currency_id' => '7',
        ]);

        Country::create([
            'country_id' => 8,
            'country_code_char2' => 'AQ',
            'country_name' => 'ANTARCTICA',
            'nicename' => 'Antarctica',
            'country_code_char3' => null,
            'numcode' => null,
            'phonecode' => 0,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 9,
            'country_code_char2' => 'AG',
            'country_name' => 'ANTIGUA AND BARBUDA',
            'nicename' => 'Antigua and Barbuda',
            'country_code_char3' => 'ATG',
            'numcode' => 28,
            'phonecode' => 1268,
            'currency_id' => '7',
        ]);

        Country::create([
            'country_id' => 10,
            'country_code_char2' => 'AR',
            'country_name' => 'ARGENTINA',
            'nicename' => 'Argentina',
            'country_code_char3' => 'ARG',
            'numcode' => 32,
            'phonecode' => 54,
            'currency_id' => '10',
        ]);

        Country::create([
            'country_id' => 11,
            'country_code_char2' => 'AM',
            'country_name' => 'ARMENIA',
            'nicename' => 'Armenia',
            'country_code_char3' => 'ARM',
            'numcode' => 51,
            'phonecode' => 374,
            'currency_id' => '11',
        ]);

        Country::create([
            'country_id' => 12,
            'country_code_char2' => 'AW',
            'country_name' => 'ARUBA',
            'nicename' => 'Aruba',
            'country_code_char3' => 'ABW',
            'numcode' => 533,
            'phonecode' => 297,
            'currency_id' => '12',
        ]);

        Country::create([
            'country_id' => 13,
            'country_code_char2' => 'AU',
            'country_name' => 'AUSTRALIA',
            'nicename' => 'Australia',
            'country_code_char3' => 'AUS',
            'numcode' => 36,
            'phonecode' => 61,
            'currency_id' => '13',
        ]);

        Country::create([
            'country_id' => 14,
            'country_code_char2' => 'AT',
            'country_name' => 'AUSTRIA',
            'nicename' => 'Austria',
            'country_code_char3' => 'AUT',
            'numcode' => 40,
            'phonecode' => 43,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 15,
            'country_code_char2' => 'AZ',
            'country_name' => 'AZERBAIJAN',
            'nicename' => 'Azerbaijan',
            'country_code_char3' => 'AZE',
            'numcode' => 31,
            'phonecode' => 994,
            'currency_id' => '15',
        ]);

        Country::create([
            'country_id' => 16,
            'country_code_char2' => 'BS',
            'country_name' => 'BAHAMAS',
            'nicename' => 'Bahamas',
            'country_code_char3' => 'BHS',
            'numcode' => 44,
            'phonecode' => 1242,
            'currency_id' => '16',
        ]);

        Country::create([
            'country_id' => 17,
            'country_code_char2' => 'BH',
            'country_name' => 'BAHRAIN',
            'nicename' => 'Bahrain',
            'country_code_char3' => 'BHR',
            'numcode' => 48,
            'phonecode' => 973,
            'currency_id' => '17',
        ]);

        Country::create([
            'country_id' => 18,
            'country_code_char2' => 'BD',
            'country_name' => 'BANGLADESH',
            'nicename' => 'Bangladesh',
            'country_code_char3' => 'BGD',
            'numcode' => 50,
            'phonecode' => 880,
            'currency_id' => '18',
        ]);

        Country::create([
            'country_id' => 19,
            'country_code_char2' => 'BB',
            'country_name' => 'BARBADOS',
            'nicename' => 'Barbados',
            'country_code_char3' => 'BRB',
            'numcode' => 52,
            'phonecode' => 1246,
            'currency_id' => '19',
        ]);

        Country::create([
            'country_id' => 20,
            'country_code_char2' => 'BY',
            'country_name' => 'BELARUS',
            'nicename' => 'Belarus',
            'country_code_char3' => 'BLR',
            'numcode' => 112,
            'phonecode' => 375,
            'currency_id' => '20',
        ]);

        Country::create([
            'country_id' => 21,
            'country_code_char2' => 'BE',
            'country_name' => 'BELGIUM',
            'nicename' => 'Belgium',
            'country_code_char3' => 'BEL',
            'numcode' => 56,
            'phonecode' => 32,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 22,
            'country_code_char2' => 'BZ',
            'country_name' => 'BELIZE',
            'nicename' => 'Belize',
            'country_code_char3' => 'BLZ',
            'numcode' => 84,
            'phonecode' => 501,
            'currency_id' => '22',
        ]);

        Country::create([
            'country_id' => 23,
            'country_code_char2' => 'BJ',
            'country_name' => 'BENIN',
            'nicename' => 'Benin',
            'country_code_char3' => 'BEN',
            'numcode' => 204,
            'phonecode' => 229,
            'currency_id' => '23',
        ]);

        Country::create([
            'country_id' => 24,
            'country_code_char2' => 'BM',
            'country_name' => 'BERMUDA',
            'nicename' => 'Bermuda',
            'country_code_char3' => 'BMU',
            'numcode' => 60,
            'phonecode' => 1441,
            'currency_id' => '24',
        ]);

        Country::create([
            'country_id' => 25,
            'country_code_char2' => 'BT',
            'country_name' => 'BHUTAN',
            'nicename' => 'Bhutan',
            'country_code_char3' => 'BTN',
            'numcode' => 64,
            'phonecode' => 975,
            'currency_id' => '25',
        ]);

        Country::create([
            'country_id' => 26,
            'country_code_char2' => 'BO',
            'country_name' => 'BOLIVIA',
            'nicename' => 'Bolivia',
            'country_code_char3' => 'BOL',
            'numcode' => 68,
            'phonecode' => 591,
            'currency_id' => '26',
        ]);

        Country::create([
            'country_id' => 27,
            'country_code_char2' => 'BA',
            'country_name' => 'BOSNIA AND HERZEGOVINA',
            'nicename' => 'Bosnia and Herzegovina',
            'country_code_char3' => 'BIH',
            'numcode' => 70,
            'phonecode' => 387,
            'currency_id' => '27',
        ]);

        Country::create([
            'country_id' => 28,
            'country_code_char2' => 'BW',
            'country_name' => 'BOTSWANA',
            'nicename' => 'Botswana',
            'country_code_char3' => 'BWA',
            'numcode' => 72,
            'phonecode' => 267,
            'currency_id' => '28',
        ]);

        Country::create([
            'country_id' => 29,
            'country_code_char2' => 'BV',
            'country_name' => 'BOUVET ISLAND',
            'nicename' => 'Bouvet Island',
            'country_code_char3' => null,
            'numcode' => null,
            'phonecode' => 0,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 30,
            'country_code_char2' => 'BR',
            'country_name' => 'BRAZIL',
            'nicename' => 'Brazil',
            'country_code_char3' => 'BRA',
            'numcode' => 76,
            'phonecode' => 55,
            'currency_id' => '30',
        ]);

        Country::create([
            'country_id' => 31,
            'country_code_char2' => 'IO',
            'country_name' => 'BRITISH INDIAN OCEAN TERRITORY',
            'nicename' => 'British Indian Ocean Territory',
            'country_code_char3' => null,
            'numcode' => null,
            'phonecode' => 246,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 32,
            'country_code_char2' => 'BN',
            'country_name' => 'BRUNEI DARUSSALAM',
            'nicename' => 'Brunei Darussalam',
            'country_code_char3' => 'BRN',
            'numcode' => 96,
            'phonecode' => 673,
            'currency_id' => '32',
        ]);

        Country::create([
            'country_id' => 33,
            'country_code_char2' => 'BG',
            'country_name' => 'BULGARIA',
            'nicename' => 'Bulgaria',
            'country_code_char3' => 'BGR',
            'numcode' => 100,
            'phonecode' => 359,
            'currency_id' => '33',
        ]);

        Country::create([
            'country_id' => 34,
            'country_code_char2' => 'BF',
            'country_name' => 'BURKINA FASO',
            'nicename' => 'Burkina Faso',
            'country_code_char3' => 'BFA',
            'numcode' => 854,
            'phonecode' => 226,
            'currency_id' => '34',
        ]);

        Country::create([
            'country_id' => 35,
            'country_code_char2' => 'BI',
            'country_name' => 'BURUNDI',
            'nicename' => 'Burundi',
            'country_code_char3' => 'BDI',
            'numcode' => 108,
            'phonecode' => 257,
            'currency_id' => '35',
        ]);

        Country::create([
            'country_id' => 36,
            'country_code_char2' => 'KH',
            'country_name' => 'CAMBODIA',
            'nicename' => 'Cambodia',
            'country_code_char3' => 'KHM',
            'numcode' => 116,
            'phonecode' => 855,
            'currency_id' => '36',
        ]);

        Country::create([
            'country_id' => 37,
            'country_code_char2' => 'CM',
            'country_name' => 'CAMEROON',
            'nicename' => 'Cameroon',
            'country_code_char3' => 'CMR',
            'numcode' => 120,
            'phonecode' => 237,
            'currency_id' => '37',
        ]);

        Country::create([
            'country_id' => 38,
            'country_code_char2' => 'CA',
            'country_name' => 'CANADA',
            'nicename' => 'Canada',
            'country_code_char3' => 'CAN',
            'numcode' => 124,
            'phonecode' => 1,
            'currency_id' => '38',
        ]);

        Country::create([
            'country_id' => 39,
            'country_code_char2' => 'CV',
            'country_name' => 'CAPE VERDE',
            'nicename' => 'Cape Verde',
            'country_code_char3' => 'CPV',
            'numcode' => 132,
            'phonecode' => 238,
            'currency_id' => '39',
        ]);

        Country::create([
            'country_id' => 40,
            'country_code_char2' => 'KY',
            'country_name' => 'CAYMAN ISLANDS',
            'nicename' => 'Cayman Islands',
            'country_code_char3' => 'CYM',
            'numcode' => 136,
            'phonecode' => 1345,
            'currency_id' => '40',
        ]);

        Country::create([
            'country_id' => 41,
            'country_code_char2' => 'CF',
            'country_name' => 'CENTRAL AFRICAN REPUBLIC',
            'nicename' => 'Central African Republic',
            'country_code_char3' => 'CAF',
            'numcode' => 140,
            'phonecode' => 236,
            'currency_id' => '42',
        ]);

        Country::create([
            'country_id' => 42,
            'country_code_char2' => 'TD',
            'country_name' => 'CHAD',
            'nicename' => 'Chad',
            'country_code_char3' => 'TCD',
            'numcode' => 148,
            'phonecode' => 235,
            'currency_id' => '42',
        ]);

        Country::create([
            'country_id' => 43,
            'country_code_char2' => 'CL',
            'country_name' => 'CHILE',
            'nicename' => 'Chile',
            'country_code_char3' => 'CHL',
            'numcode' => 152,
            'phonecode' => 56,
            'currency_id' => '43',
        ]);

        Country::create([
            'country_id' => 44,
            'country_code_char2' => 'CN',
            'country_name' => 'CHINA',
            'nicename' => 'China',
            'country_code_char3' => 'CHN',
            'numcode' => 156,
            'phonecode' => 86,
            'currency_id' => '44',
        ]);

        Country::create([
            'country_id' => 45,
            'country_code_char2' => 'CX',
            'country_name' => 'CHRISTMAS ISLAND',
            'nicename' => 'Christmas Island',
            'country_code_char3' => null,
            'numcode' => null,
            'phonecode' => 61,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 46,
            'country_code_char2' => 'CC',
            'country_name' => 'COCOS (KEELING) ISLANDS',
            'nicename' => 'Cocos (Keeling) Islands',
            'country_code_char3' => null,
            'numcode' => null,
            'phonecode' => 672,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 47,
            'country_code_char2' => 'CO',
            'country_name' => 'COLOMBIA',
            'nicename' => 'Colombia',
            'country_code_char3' => 'COL',
            'numcode' => 170,
            'phonecode' => 57,
            'currency_id' => '47',
        ]);

        Country::create([
            'country_id' => 48,
            'country_code_char2' => 'KM',
            'country_name' => 'COMOROS',
            'nicename' => 'Comoros',
            'country_code_char3' => 'COM',
            'numcode' => 174,
            'phonecode' => 269,
            'currency_id' => '48',
        ]);

        Country::create([
            'country_id' => 49,
            'country_code_char2' => 'CG',
            'country_name' => 'CONGO',
            'nicename' => 'Congo',
            'country_code_char3' => 'COG',
            'numcode' => 178,
            'phonecode' => 242,
            'currency_id' => '50',
        ]);

        Country::create([
            'country_id' => 50,
            'country_code_char2' => 'CD',
            'country_name' => 'CONGO, THE DEMOCRATIC REPUBLIC OF THE',
            'nicename' => 'Congo, the Democratic Republic of the',
            'country_code_char3' => 'COD',
            'numcode' => 180,
            'phonecode' => 242,
            'currency_id' => '50',
        ]);

        Country::create([
            'country_id' => 51,
            'country_code_char2' => 'CK',
            'country_name' => 'COOK ISLANDS',
            'nicename' => 'Cook Islands',
            'country_code_char3' => 'COK',
            'numcode' => 184,
            'phonecode' => 682,
            'currency_id' => '51',
        ]);

        Country::create([
            'country_id' => 52,
            'country_code_char2' => 'CR',
            'country_name' => 'COSTA RICA',
            'nicename' => 'Costa Rica',
            'country_code_char3' => 'CRI',
            'numcode' => 188,
            'phonecode' => 506,
            'currency_id' => '52',
        ]);

        Country::create([
            'country_id' => 53,
            'country_code_char2' => 'CI',
            'country_name' => 'COTE D',
            'nicename' => 'IVOIRE',
            'country_code_char3' => 'Cote D',
            'numcode' => 'Ivoire',
            'phonecode' => 'CIV',
            'currency_id' => 384,
            'phonecode' => 225,
            'currency_id' => '53',
        ]);

        Country::create([
            'country_id' => 54,
            'country_code_char2' => 'HR',
            'country_name' => 'CROATIA',
            'nicename' => 'Croatia',
            'country_code_char3' => 'HRV',
            'numcode' => 191,
            'phonecode' => 385,
            'currency_id' => '54',
        ]);

        Country::create([
            'country_id' => 55,
            'country_code_char2' => 'CU',
            'country_name' => 'CUBA',
            'nicename' => 'Cuba',
            'country_code_char3' => 'CUB',
            'numcode' => 192,
            'phonecode' => 53,
            'currency_id' => '55',
        ]);

        Country::create([
            'country_id' => 56,
            'country_code_char2' => 'CY',
            'country_name' => 'CYPRUS',
            'nicename' => 'Cyprus',
            'country_code_char3' => 'CYP',
            'numcode' => 196,
            'phonecode' => 357,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 57,
            'country_code_char2' => 'CZ',
            'country_name' => 'CZECH REPUBLIC',
            'nicename' => 'Czech Republic',
            'country_code_char3' => 'CZE',
            'numcode' => 203,
            'phonecode' => 420,
            'currency_id' => '57',
        ]);

        Country::create([
            'country_id' => 58,
            'country_code_char2' => 'DK',
            'country_name' => 'DENMARK',
            'nicename' => 'Denmark',
            'country_code_char3' => 'DNK',
            'numcode' => 208,
            'phonecode' => 45,
            'currency_id' => '58',
        ]);

        Country::create([
            'country_id' => 59,
            'country_code_char2' => 'DJ',
            'country_name' => 'DJIBOUTI',
            'nicename' => 'Djibouti',
            'country_code_char3' => 'DJI',
            'numcode' => 262,
            'phonecode' => 253,
            'currency_id' => '59',
        ]);

        Country::create([
            'country_id' => 60,
            'country_code_char2' => 'DM',
            'country_name' => 'DOMINICA',
            'nicename' => 'Dominica',
            'country_code_char3' => 'DMA',
            'numcode' => 212,
            'phonecode' => 1767,
            'currency_id' => '60',
        ]);

        Country::create([
            'country_id' => 61,
            'country_code_char2' => 'DO',
            'country_name' => 'DOMINICAN REPUBLIC',
            'nicename' => 'Dominican Republic',
            'country_code_char3' => 'DOM',
            'numcode' => 214,
            'phonecode' => 1809,
            'currency_id' => '60',
        ]);

        Country::create([
            'country_id' => 62,
            'country_code_char2' => 'EC',
            'country_name' => 'ECUADOR',
            'nicename' => 'Ecuador',
            'country_code_char3' => 'ECU',
            'numcode' => 218,
            'phonecode' => 593,
            'currency_id' => '62',
        ]);

        Country::create([
            'country_id' => 63,
            'country_code_char2' => 'EG',
            'country_name' => 'EGYPT',
            'nicename' => 'Egypt',
            'country_code_char3' => 'EGY',
            'numcode' => 818,
            'phonecode' => 20,
            'currency_id' => '63',
        ]);

        Country::create([
            'country_id' => 64,
            'country_code_char2' => 'SV',
            'country_name' => 'EL SALVADOR',
            'nicename' => 'El Salvador',
            'country_code_char3' => 'SLV',
            'numcode' => 222,
            'phonecode' => 503,
            'currency_id' => '64',
        ]);

        Country::create([
            'country_id' => 65,
            'country_code_char2' => 'GQ',
            'country_name' => 'EQUATORIAL GUINEA',
            'nicename' => 'Equatorial Guinea',
            'country_code_char3' => 'GNQ',
            'numcode' => 226,
            'phonecode' => 240,
            'currency_id' => '23',
        ]);

        Country::create([
            'country_id' => 66,
            'country_code_char2' => 'ER',
            'country_name' => 'ERITREA',
            'nicename' => 'Eritrea',
            'country_code_char3' => 'ERI',
            'numcode' => 232,
            'phonecode' => 291,
            'currency_id' => '66',
        ]);

        Country::create([
            'country_id' => 67,
            'country_code_char2' => 'EE',
            'country_name' => 'ESTONIA',
            'nicename' => 'Estonia',
            'country_code_char3' => 'EST',
            'numcode' => 233,
            'phonecode' => 372,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 68,
            'country_code_char2' => 'ET',
            'country_name' => 'ETHIOPIA',
            'nicename' => 'Ethiopia',
            'country_code_char3' => 'ETH',
            'numcode' => 231,
            'phonecode' => 251,
            'currency_id' => '68',
        ]);

        Country::create([
            'country_id' => 69,
            'country_code_char2' => 'FK',
            'country_name' => 'FALKLAND ISLANDS (MALVINAS)',
            'nicename' => 'Falkland Islands (Malvinas)',
            'country_code_char3' => 'FLK',
            'numcode' => 238,
            'phonecode' => 500,
            'currency_id' => '69',
        ]);

        Country::create([
            'country_id' => 70,
            'country_code_char2' => 'FO',
            'country_name' => 'FAROE ISLANDS',
            'nicename' => 'Faroe Islands',
            'country_code_char3' => 'FRO',
            'numcode' => 234,
            'phonecode' => 298,
            'currency_id' => '70',
        ]);

        Country::create([
            'country_id' => 71,
            'country_code_char2' => 'FJ',
            'country_name' => 'FIJI',
            'nicename' => 'Fiji',
            'country_code_char3' => 'FJI',
            'numcode' => 242,
            'phonecode' => 679,
            'currency_id' => '71',
        ]);

        Country::create([
            'country_id' => 72,
            'country_code_char2' => 'FI',
            'country_name' => 'FINLAND',
            'nicename' => 'Finland',
            'country_code_char3' => 'FIN',
            'numcode' => 246,
            'phonecode' => 358,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 73,
            'country_code_char2' => 'FR',
            'country_name' => 'FRANCE',
            'nicename' => 'France',
            'country_code_char3' => 'FRA',
            'numcode' => 250,
            'phonecode' => 33,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 74,
            'country_code_char2' => 'GF',
            'country_name' => 'FRENCH GUIANA',
            'nicename' => 'French Guiana',
            'country_code_char3' => 'GUF',
            'numcode' => 254,
            'phonecode' => 594,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 75,
            'country_code_char2' => 'PF',
            'country_name' => 'FRENCH POLYNESIA',
            'nicename' => 'French Polynesia',
            'country_code_char3' => 'PYF',
            'numcode' => 258,
            'phonecode' => 689,
            'currency_id' => '152',
        ]);

        Country::create([
            'country_id' => 76,
            'country_code_char2' => 'TF',
            'country_name' => 'FRENCH SOUTHERN TERRITORIES',
            'nicename' => 'French Southern Territories',
            'country_code_char3' => null,
            'numcode' => null,
            'phonecode' => 0,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 77,
            'country_code_char2' => 'GA',
            'country_name' => 'GABON',
            'nicename' => 'Gabon',
            'country_code_char3' => 'GAB',
            'numcode' => 266,
            'phonecode' => 241,
            'currency_id' => '23',
        ]);

        Country::create([
            'country_id' => 78,
            'country_code_char2' => 'GM',
            'country_name' => 'GAMBIA',
            'nicename' => 'Gambia',
            'country_code_char3' => 'GMB',
            'numcode' => 270,
            'phonecode' => 220,
            'currency_id' => '78',
        ]);

        Country::create([
            'country_id' => 79,
            'country_code_char2' => 'GE',
            'country_name' => 'GEORGIA',
            'nicename' => 'Georgia',
            'country_code_char3' => 'GEO',
            'numcode' => 268,
            'phonecode' => 995,
            'currency_id' => '79',
        ]);

        Country::create([
            'country_id' => 80,
            'country_code_char2' => 'DE',
            'country_name' => 'GERMANY',
            'nicename' => 'Germany',
            'country_code_char3' => 'DEU',
            'numcode' => 276,
            'phonecode' => 49,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 81,
            'country_code_char2' => 'GH',
            'country_name' => 'GHANA',
            'nicename' => 'Ghana',
            'country_code_char3' => 'GHA',
            'numcode' => 288,
            'phonecode' => 233,
            'currency_id' => '81',
        ]);

        Country::create([
            'country_id' => 82,
            'country_code_char2' => 'GI',
            'country_name' => 'GIBRALTAR',
            'nicename' => 'Gibraltar',
            'country_code_char3' => 'GIB',
            'numcode' => 292,
            'phonecode' => 350,
            'currency_id' => '82',
        ]);

        Country::create([
            'country_id' => 83,
            'country_code_char2' => 'GR',
            'country_name' => 'GREECE',
            'nicename' => 'Greece',
            'country_code_char3' => 'GRC',
            'numcode' => 300,
            'phonecode' => 30,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 84,
            'country_code_char2' => 'GL',
            'country_name' => 'GREENLAND',
            'nicename' => 'Greenland',
            'country_code_char3' => 'GRL',
            'numcode' => 304,
            'phonecode' => 299,
            'currency_id' => '84',
        ]);

        Country::create([
            'country_id' => 85,
            'country_code_char2' => 'GD',
            'country_name' => 'GRENADA',
            'nicename' => 'Grenada',
            'country_code_char3' => 'GRD',
            'numcode' => 308,
            'phonecode' => 1473,
            'currency_id' => '7',
        ]);

        Country::create([
            'country_id' => 86,
            'country_code_char2' => 'GP',
            'country_name' => 'GUADELOUPE',
            'nicename' => 'Guadeloupe',
            'country_code_char3' => 'GLP',
            'numcode' => 312,
            'phonecode' => 590,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 87,
            'country_code_char2' => 'GU',
            'country_name' => 'GUAM',
            'nicename' => 'Guam',
            'country_code_char3' => 'GUM',
            'numcode' => 316,
            'phonecode' => 1671,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 88,
            'country_code_char2' => 'GT',
            'country_name' => 'GUATEMALA',
            'nicename' => 'Guatemala',
            'country_code_char3' => 'GTM',
            'numcode' => 320,
            'phonecode' => 502,
            'currency_id' => '88',
        ]);

        Country::create([
            'country_id' => 89,
            'country_code_char2' => 'GN',
            'country_name' => 'GUINEA',
            'nicename' => 'Guinea',
            'country_code_char3' => 'GIN',
            'numcode' => 324,
            'phonecode' => 224,
            'currency_id' => '89',
        ]);

        Country::create([
            'country_id' => 90,
            'country_code_char2' => 'GW',
            'country_name' => 'GUINEA-BISSAU',
            'nicename' => 'Guinea-Bissau',
            'country_code_char3' => 'GNB',
            'numcode' => 624,
            'phonecode' => 245,
            'currency_id' => '23',
        ]);

        Country::create([
            'country_id' => 91,
            'country_code_char2' => 'GY',
            'country_name' => 'GUYANA',
            'nicename' => 'Guyana',
            'country_code_char3' => 'GUY',
            'numcode' => 328,
            'phonecode' => 592,
            'currency_id' => '91',
        ]);

        Country::create([
            'country_id' => 92,
            'country_code_char2' => 'HT',
            'country_name' => 'HAITI',
            'nicename' => 'Haiti',
            'country_code_char3' => 'HTI',
            'numcode' => 332,
            'phonecode' => 509,
            'currency_id' => '92',
        ]);

        Country::create([
            'country_id' => 93,
            'country_code_char2' => 'HM',
            'country_name' => 'HEARD ISLAND AND MCDONALD ISLANDS',
            'nicename' => 'Heard Island and Mcdonald Islands',
            'country_code_char3' => null,
            'numcode' => null,
            'phonecode' => 0,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 94,
            'country_code_char2' => 'VA',
            'country_name' => 'HOLY SEE (VATICAN CITY STATE)',
            'nicename' => 'Holy See (Vatican City State)',
            'country_code_char3' => 'VAT',
            'numcode' => 336,
            'phonecode' => 39,
            'currency_id' => '94',
        ]);

        Country::create([
            'country_id' => 95,
            'country_code_char2' => 'HN',
            'country_name' => 'HONDURAS',
            'nicename' => 'Honduras',
            'country_code_char3' => 'HND',
            'numcode' => 340,
            'phonecode' => 504,
            'currency_id' => '95',
        ]);

        Country::create([
            'country_id' => 96,
            'country_code_char2' => 'HK',
            'country_name' => 'HONG KONG',
            'nicename' => 'Hong Kong',
            'country_code_char3' => 'HKG',
            'numcode' => 344,
            'phonecode' => 852,
            'currency_id' => '96',
        ]);

        Country::create([
            'country_id' => 97,
            'country_code_char2' => 'HU',
            'country_name' => 'HUNGARY',
            'nicename' => 'Hungary',
            'country_code_char3' => 'HUN',
            'numcode' => 348,
            'phonecode' => 36,
            'currency_id' => '97',
        ]);

        Country::create([
            'country_id' => 98,
            'country_code_char2' => 'IS',
            'country_name' => 'ICELAND',
            'nicename' => 'Iceland',
            'country_code_char3' => 'ISL',
            'numcode' => 352,
            'phonecode' => 354,
            'currency_id' => '98',
        ]);

        Country::create([
            'country_id' => 99,
            'country_code_char2' => 'IN',
            'country_name' => 'INDIA',
            'nicename' => 'India',
            'country_code_char3' => 'IND',
            'numcode' => 356,
            'phonecode' => 91,
            'currency_id' => '99',
        ]);

        Country::create([
            'country_id' => 100,
            'country_code_char2' => 'ID',
            'country_name' => 'INDONESIA',
            'nicename' => 'Indonesia',
            'country_code_char3' => 'IDN',
            'numcode' => 360,
            'phonecode' => 62,
            'currency_id' => '100',
        ]);

        Country::create([
            'country_id' => 101,
            'country_code_char2' => 'IR',
            'country_name' => 'IRAN, ISLAMIC REPUBLIC OF',
            'nicename' => 'Iran, Islamic Republic of',
            'country_code_char3' => 'IRN',
            'numcode' => 364,
            'phonecode' => 98,
            'currency_id' => '101',
        ]);

        Country::create([
            'country_id' => 102,
            'country_code_char2' => 'IQ',
            'country_name' => 'IRAQ',
            'nicename' => 'Iraq',
            'country_code_char3' => 'IRQ',
            'numcode' => 368,
            'phonecode' => 964,
            'currency_id' => '102',
        ]);

        Country::create([
            'country_id' => 103,
            'country_code_char2' => 'IE',
            'country_name' => 'IRELAND',
            'nicename' => 'Ireland',
            'country_code_char3' => 'IRL',
            'numcode' => 372,
            'phonecode' => 353,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 104,
            'country_code_char2' => 'IL',
            'country_name' => 'ISRAEL',
            'nicename' => 'Israel',
            'country_code_char3' => 'ISR',
            'numcode' => 376,
            'phonecode' => 972,
            'currency_id' => '104',
        ]);

        Country::create([
            'country_id' => 105,
            'country_code_char2' => 'IT',
            'country_name' => 'ITALY',
            'nicename' => 'Italy',
            'country_code_char3' => 'ITA',
            'numcode' => 380,
            'phonecode' => 39,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 106,
            'country_code_char2' => 'JM',
            'country_name' => 'JAMAICA',
            'nicename' => 'Jamaica',
            'country_code_char3' => 'JAM',
            'numcode' => 388,
            'phonecode' => 1876,
            'currency_id' => '106',
        ]);

        Country::create([
            'country_id' => 107,
            'country_code_char2' => 'JP',
            'country_name' => 'JAPAN',
            'nicename' => 'Japan',
            'country_code_char3' => 'JPN',
            'numcode' => 392,
            'phonecode' => 81,
            'currency_id' => '107',
        ]);

        Country::create([
            'country_id' => 108,
            'country_code_char2' => 'JO',
            'country_name' => 'JORDAN',
            'nicename' => 'Jordan',
            'country_code_char3' => 'JOR',
            'numcode' => 400,
            'phonecode' => 962,
            'currency_id' => '108',
        ]);

        Country::create([
            'country_id' => 109,
            'country_code_char2' => 'KZ',
            'country_name' => 'KAZAKHSTAN',
            'nicename' => 'Kazakhstan',
            'country_code_char3' => 'KAZ',
            'numcode' => 398,
            'phonecode' => 7,
            'currency_id' => '109',
        ]);

        Country::create([
            'country_id' => 110,
            'country_code_char2' => 'KE',
            'country_name' => 'KENYA',
            'nicename' => 'Kenya',
            'country_code_char3' => 'KEN',
            'numcode' => 404,
            'phonecode' => 254,
            'currency_id' => '110',
        ]);

        Country::create([
            'country_id' => 111,
            'country_code_char2' => 'KI',
            'country_name' => 'KIRIBATI',
            'nicename' => 'Kiribati',
            'country_code_char3' => 'KIR',
            'numcode' => 296,
            'phonecode' => 686,
            'currency_id' => '13',
        ]);

        Country::create([
            'country_id' => 112,
            'country_code_char2' => 'KP',
            'country_name' => 'KOREA, DEMOCRATIC PEOPLE',
            'nicename' => 'S REPUBLIC OF',
            'country_code_char3' => 'Korea, Democratic People',
            'numcode' => 's Republic of',
            'phonecode' => 'PRK',
            'currency_id' => 408,
            'phonecode' => 850,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 113,
            'country_code_char2' => 'KR',
            'country_name' => 'KOREA, REPUBLIC OF',
            'nicename' => 'Korea, Republic of',
            'country_code_char3' => 'KOR',
            'numcode' => 410,
            'phonecode' => 82,
            'currency_id' => '113',
        ]);

        Country::create([
            'country_id' => 114,
            'country_code_char2' => 'KW',
            'country_name' => 'KUWAIT',
            'nicename' => 'Kuwait',
            'country_code_char3' => 'KWT',
            'numcode' => 414,
            'phonecode' => 965,
            'currency_id' => '114',
        ]);

        Country::create([
            'country_id' => 115,
            'country_code_char2' => 'KG',
            'country_name' => 'KYRGYZSTAN',
            'nicename' => 'Kyrgyzstan',
            'country_code_char3' => 'KGZ',
            'numcode' => 417,
            'phonecode' => 996,
            'currency_id' => '115',
        ]);

        Country::create([
            'country_id' => 117,
            'country_code_char2' => 'LV',
            'country_name' => 'LATVIA',
            'nicename' => 'Latvia',
            'country_code_char3' => 'LVA',
            'numcode' => 428,
            'phonecode' => 371,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 118,
            'country_code_char2' => 'LB',
            'country_name' => 'LEBANON',
            'nicename' => 'Lebanon',
            'country_code_char3' => 'LBN',
            'numcode' => 422,
            'phonecode' => 961,
            'currency_id' => '118',
        ]);

        Country::create([
            'country_id' => 119,
            'country_code_char2' => 'LS',
            'country_name' => 'LESOTHO',
            'nicename' => 'Lesotho',
            'country_code_char3' => 'LSO',
            'numcode' => 426,
            'phonecode' => 266,
            'currency_id' => '119',
        ]);

        Country::create([
            'country_id' => 120,
            'country_code_char2' => 'LR',
            'country_name' => 'LIBERIA',
            'nicename' => 'Liberia',
            'country_code_char3' => 'LBR',
            'numcode' => 430,
            'phonecode' => 231,
            'currency_id' => '120',
        ]);

        Country::create([
            'country_id' => 121,
            'country_code_char2' => 'LY',
            'country_name' => 'LIBYAN ARAB JAMAHIRIYA',
            'nicename' => 'Libyan Arab Jamahiriya',
            'country_code_char3' => 'LBY',
            'numcode' => 434,
            'phonecode' => 218,
            'currency_id' => '121',
        ]);

        Country::create([
            'country_id' => 122,
            'country_code_char2' => 'LI',
            'country_name' => 'LIECHTENSTEIN',
            'nicename' => 'Liechtenstein',
            'country_code_char3' => 'LIE',
            'numcode' => 438,
            'phonecode' => 423,
            'currency_id' => '122',
        ]);

        Country::create([
            'country_id' => 123,
            'country_code_char2' => 'LT',
            'country_name' => 'LITHUANIA',
            'nicename' => 'Lithuania',
            'country_code_char3' => 'LTU',
            'numcode' => 440,
            'phonecode' => 370,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 124,
            'country_code_char2' => 'LU',
            'country_name' => 'LUXEMBOURG',
            'nicename' => 'Luxembourg',
            'country_code_char3' => 'LUX',
            'numcode' => 442,
            'phonecode' => 352,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 125,
            'country_code_char2' => 'MO',
            'country_name' => 'MACAO',
            'nicename' => 'Macao',
            'country_code_char3' => 'MAC',
            'numcode' => 446,
            'phonecode' => 853,
            'currency_id' => '125',
        ]);

        Country::create([
            'country_id' => 126,
            'country_code_char2' => 'MK',
            'country_name' => 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF',
            'nicename' => 'Macedonia, the Former Yugoslav Republic of',
            'country_code_char3' => 'MKD',
            'numcode' => 807,
            'phonecode' => 389,
            'currency_id' => '126',
        ]);

        Country::create([
            'country_id' => 127,
            'country_code_char2' => 'MG',
            'country_name' => 'MADAGASCAR',
            'nicename' => 'Madagascar',
            'country_code_char3' => 'MDG',
            'numcode' => 450,
            'phonecode' => 261,
            'currency_id' => '127',
        ]);

        Country::create([
            'country_id' => 128,
            'country_code_char2' => 'MW',
            'country_name' => 'MALAWI',
            'nicename' => 'Malawi',
            'country_code_char3' => 'MWI',
            'numcode' => 454,
            'phonecode' => 265,
            'currency_id' => '128',
        ]);

        Country::create([
            'country_id' => 129,
            'country_code_char2' => 'MY',
            'country_name' => 'MALAYSIA',
            'nicename' => 'Malaysia',
            'country_code_char3' => 'MYS',
            'numcode' => 458,
            'phonecode' => 60,
            'currency_id' => '129',
        ]);

        Country::create([
            'country_id' => 130,
            'country_code_char2' => 'MV',
            'country_name' => 'MALDIVES',
            'nicename' => 'Maldives',
            'country_code_char3' => 'MDV',
            'numcode' => 462,
            'phonecode' => 960,
            'currency_id' => '130',
        ]);

        Country::create([
            'country_id' => 131,
            'country_code_char2' => 'ML',
            'country_name' => 'MALI',
            'nicename' => 'Mali',
            'country_code_char3' => 'MLI',
            'numcode' => 466,
            'phonecode' => 223,
            'currency_id' => '23',
        ]);

        Country::create([
            'country_id' => 132,
            'country_code_char2' => 'MT',
            'country_name' => 'MALTA',
            'nicename' => 'Malta',
            'country_code_char3' => 'MLT',
            'numcode' => 470,
            'phonecode' => 356,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 133,
            'country_code_char2' => 'MH',
            'country_name' => 'MARSHALL ISLANDS',
            'nicename' => 'Marshall Islands',
            'country_code_char3' => 'MHL',
            'numcode' => 584,
            'phonecode' => 692,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 134,
            'country_code_char2' => 'MQ',
            'country_name' => 'MARTINIQUE',
            'nicename' => 'Martinique',
            'country_code_char3' => 'MTQ',
            'numcode' => 474,
            'phonecode' => 596,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 135,
            'country_code_char2' => 'MR',
            'country_name' => 'MAURITANIA',
            'nicename' => 'Mauritania',
            'country_code_char3' => 'MRT',
            'numcode' => 478,
            'phonecode' => 222,
            'currency_id' => '135',
        ]);

        Country::create([
            'country_id' => 136,
            'country_code_char2' => 'MU',
            'country_name' => 'MAURITIUS',
            'nicename' => 'Mauritius',
            'country_code_char3' => 'MUS',
            'numcode' => 480,
            'phonecode' => 230,
            'currency_id' => '136',
        ]);

        Country::create([
            'country_id' => 137,
            'country_code_char2' => 'YT',
            'country_name' => 'MAYOTTE',
            'nicename' => 'Mayotte',
            'country_code_char3' => null,
            'numcode' => null,
            'phonecode' => 269,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 138,
            'country_code_char2' => 'MX',
            'country_name' => 'MEXICO',
            'nicename' => 'Mexico',
            'country_code_char3' => 'MEX',
            'numcode' => 484,
            'phonecode' => 52,
            'currency_id' => '138',
        ]);

        Country::create([
            'country_id' => 139,
            'country_code_char2' => 'FM',
            'country_name' => 'MICRONESIA, FEDERATED STATES OF',
            'nicename' => 'Micronesia, Federated States of',
            'country_code_char3' => 'FSM',
            'numcode' => 583,
            'phonecode' => 691,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 140,
            'country_code_char2' => 'MD',
            'country_name' => 'MOLDOVA, REPUBLIC OF',
            'nicename' => 'Moldova, Republic of',
            'country_code_char3' => 'MDA',
            'numcode' => 498,
            'phonecode' => 373,
            'currency_id' => '140',
        ]);

        Country::create([
            'country_id' => 141,
            'country_code_char2' => 'MC',
            'country_name' => 'MONACO',
            'nicename' => 'Monaco',
            'country_code_char3' => 'MCO',
            'numcode' => 492,
            'phonecode' => 377,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 142,
            'country_code_char2' => 'MN',
            'country_name' => 'MONGOLIA',
            'nicename' => 'Mongolia',
            'country_code_char3' => 'MNG',
            'numcode' => 496,
            'phonecode' => 976,
            'currency_id' => '142',
        ]);

        Country::create([
            'country_id' => 143,
            'country_code_char2' => 'MS',
            'country_name' => 'MONTSERRAT',
            'nicename' => 'Montserrat',
            'country_code_char3' => 'MSR',
            'numcode' => 500,
            'phonecode' => 1664,
            'currency_id' => '7',
        ]);

        Country::create([
            'country_id' => 144,
            'country_code_char2' => 'MA',
            'country_name' => 'MOROCCO',
            'nicename' => 'Morocco',
            'country_code_char3' => 'MAR',
            'numcode' => 504,
            'phonecode' => 212,
            'currency_id' => '144',
        ]);

        Country::create([
            'country_id' => 145,
            'country_code_char2' => 'MZ',
            'country_name' => 'MOZAMBIQUE',
            'nicename' => 'Mozambique',
            'country_code_char3' => 'MOZ',
            'numcode' => 508,
            'phonecode' => 258,
            'currency_id' => '145',
        ]);

        Country::create([
            'country_id' => 146,
            'country_code_char2' => 'MM',
            'country_name' => 'MYANMAR',
            'nicename' => 'Myanmar',
            'country_code_char3' => 'MMR',
            'numcode' => 104,
            'phonecode' => 95,
            'currency_id' => '146',
        ]);

        Country::create([
            'country_id' => 147,
            'country_code_char2' => 'NA',
            'country_name' => 'NAMIBIA',
            'nicename' => 'Namibia',
            'country_code_char3' => 'NAM',
            'numcode' => 516,
            'phonecode' => 264,
            'currency_id' => '147',
        ]);

        Country::create([
            'country_id' => 148,
            'country_code_char2' => 'NR',
            'country_name' => 'NAURU',
            'nicename' => 'Nauru',
            'country_code_char3' => 'NRU',
            'numcode' => 520,
            'phonecode' => 674,
            'currency_id' => '13',
        ]);

        Country::create([
            'country_id' => 149,
            'country_code_char2' => 'NP',
            'country_name' => 'NEPAL',
            'nicename' => 'Nepal',
            'country_code_char3' => 'NPL',
            'numcode' => 524,
            'phonecode' => 977,
            'currency_id' => '149',
        ]);

        Country::create([
            'country_id' => 150,
            'country_code_char2' => 'NL',
            'country_name' => 'NETHERLANDS',
            'nicename' => 'Netherlands',
            'country_code_char3' => 'NLD',
            'numcode' => 528,
            'phonecode' => 31,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 151,
            'country_code_char2' => 'AN',
            'country_name' => 'NETHERLANDS ANTILLES',
            'nicename' => 'Netherlands Antilles',
            'country_code_char3' => 'ANT',
            'numcode' => 530,
            'phonecode' => 599,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 152,
            'country_code_char2' => 'NC',
            'country_name' => 'NEW CALEDONIA',
            'nicename' => 'New Caledonia',
            'country_code_char3' => 'NCL',
            'numcode' => 540,
            'phonecode' => 687,
            'currency_id' => '152',
        ]);

        Country::create([
            'country_id' => 153,
            'country_code_char2' => 'NZ',
            'country_name' => 'NEW ZEALAND',
            'nicename' => 'New Zealand',
            'country_code_char3' => 'NZL',
            'numcode' => 554,
            'phonecode' => 64,
            'currency_id' => '51',
        ]);

        Country::create([
            'country_id' => 154,
            'country_code_char2' => 'NI',
            'country_name' => 'NICARAGUA',
            'nicename' => 'Nicaragua',
            'country_code_char3' => 'NIC',
            'numcode' => 558,
            'phonecode' => 505,
            'currency_id' => '154',
        ]);

        Country::create([
            'country_id' => 155,
            'country_code_char2' => 'NE',
            'country_name' => 'NIGER',
            'nicename' => 'Niger',
            'country_code_char3' => 'NER',
            'numcode' => 562,
            'phonecode' => 227,
            'currency_id' => '23',
        ]);

        Country::create([
            'country_id' => 156,
            'country_code_char2' => 'NG',
            'country_name' => 'NIGERIA',
            'nicename' => 'Nigeria',
            'country_code_char3' => 'NGA',
            'numcode' => 566,
            'phonecode' => 234,
            'currency_id' => '156',
        ]);

        Country::create([
            'country_id' => 157,
            'country_code_char2' => 'NU',
            'country_name' => 'NIUE',
            'nicename' => 'Niue',
            'country_code_char3' => 'NIU',
            'numcode' => 570,
            'phonecode' => 683,
            'currency_id' => '51',
        ]);

        Country::create([
            'country_id' => 158,
            'country_code_char2' => 'NF',
            'country_name' => 'NORFOLK ISLAND',
            'nicename' => 'Norfolk Island',
            'country_code_char3' => 'NFK',
            'numcode' => 574,
            'phonecode' => 672,
            'currency_id' => '158',
        ]);

        Country::create([
            'country_id' => 159,
            'country_code_char2' => 'MP',
            'country_name' => 'NORTHERN MARIANA ISLANDS',
            'nicename' => 'Northern Mariana Islands',
            'country_code_char3' => 'MNP',
            'numcode' => 580,
            'phonecode' => 1670,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 160,
            'country_code_char2' => 'NO',
            'country_name' => 'NORWAY',
            'nicename' => 'Norway',
            'country_code_char3' => 'NOR',
            'numcode' => 578,
            'phonecode' => 47,
            'currency_id' => '160',
        ]);

        Country::create([
            'country_id' => 161,
            'country_code_char2' => 'OM',
            'country_name' => 'OMAN',
            'nicename' => 'Oman',
            'country_code_char3' => 'OMN',
            'numcode' => 512,
            'phonecode' => 968,
            'currency_id' => '161',
        ]);

        Country::create([
            'country_id' => 162,
            'country_code_char2' => 'PK',
            'country_name' => 'PAKISTAN',
            'nicename' => 'Pakistan',
            'country_code_char3' => 'PAK',
            'numcode' => 586,
            'phonecode' => 92,
            'currency_id' => '162',
        ]);

        Country::create([
            'country_id' => 163,
            'country_code_char2' => 'PW',
            'country_name' => 'PALAU',
            'nicename' => 'Palau',
            'country_code_char3' => 'PLW',
            'numcode' => 585,
            'phonecode' => 680,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 164,
            'country_code_char2' => 'PS',
            'country_name' => 'PALESTINIAN TERRITORY, OCCUPIED',
            'nicename' => 'Palestinian Territory, Occupied',
            'country_code_char3' => null,
            'numcode' => null,
            'phonecode' => 970,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 165,
            'country_code_char2' => 'PA',
            'country_name' => 'PANAMA',
            'nicename' => 'Panama',
            'country_code_char3' => 'PAN',
            'numcode' => 591,
            'phonecode' => 507,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 166,
            'country_code_char2' => 'PG',
            'country_name' => 'PAPUA NEW GUINEA',
            'nicename' => 'Papua New Guinea',
            'country_code_char3' => 'PNG',
            'numcode' => 598,
            'phonecode' => 675,
            'currency_id' => '89',
        ]);

        Country::create([
            'country_id' => 167,
            'country_code_char2' => 'PY',
            'country_name' => 'PARAGUAY',
            'nicename' => 'Paraguay',
            'country_code_char3' => 'PRY',
            'numcode' => 600,
            'phonecode' => 595,
            'currency_id' => '167',
        ]);

        Country::create([
            'country_id' => 168,
            'country_code_char2' => 'PE',
            'country_name' => 'PERU',
            'nicename' => 'Peru',
            'country_code_char3' => 'PER',
            'numcode' => 604,
            'phonecode' => 51,
            'currency_id' => '168',
        ]);

        Country::create([
            'country_id' => 169,
            'country_code_char2' => 'PH',
            'country_name' => 'PHILIPPINES',
            'nicename' => 'Philippines',
            'country_code_char3' => 'PHL',
            'numcode' => 608,
            'phonecode' => 63,
            'currency_id' => '169',
        ]);

        Country::create([
            'country_id' => 170,
            'country_code_char2' => 'PN',
            'country_name' => 'PITCAIRN',
            'nicename' => 'Pitcairn',
            'country_code_char3' => 'PCN',
            'numcode' => 612,
            'phonecode' => 0,
            'currency_id' => '51',
        ]);

        Country::create([
            'country_id' => 171,
            'country_code_char2' => 'PL',
            'country_name' => 'POLAND',
            'nicename' => 'Poland',
            'country_code_char3' => 'POL',
            'numcode' => 616,
            'phonecode' => 48,
            'currency_id' => '171',
        ]);

        Country::create([
            'country_id' => 172,
            'country_code_char2' => 'PT',
            'country_name' => 'PORTUGAL',
            'nicename' => 'Portugal',
            'country_code_char3' => 'PRT',
            'numcode' => 620,
            'phonecode' => 351,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 173,
            'country_code_char2' => 'PR',
            'country_name' => 'PUERTO RICO',
            'nicename' => 'Puerto Rico',
            'country_code_char3' => 'PRI',
            'numcode' => 630,
            'phonecode' => 1787,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 174,
            'country_code_char2' => 'QA',
            'country_name' => 'QATAR',
            'nicename' => 'Qatar',
            'country_code_char3' => 'QAT',
            'numcode' => 634,
            'phonecode' => 974,
            'currency_id' => '174',
        ]);

        Country::create([
            'country_id' => 175,
            'country_code_char2' => 'RE',
            'country_name' => 'REUNION',
            'nicename' => 'Reunion',
            'country_code_char3' => 'REU',
            'numcode' => 638,
            'phonecode' => 262,
            'currency_id' => '175',
        ]);

        Country::create([
            'country_id' => 176,
            'country_code_char2' => 'RO',
            'country_name' => 'ROMANIA',
            'nicename' => 'Romania',
            'country_code_char3' => 'ROM',
            'numcode' => 642,
            'phonecode' => 40,
            'currency_id' => '176',
        ]);

        Country::create([
            'country_id' => 177,
            'country_code_char2' => 'RU',
            'country_name' => 'RUSSIAN FEDERATION',
            'nicename' => 'Russian Federation',
            'country_code_char3' => 'RUS',
            'numcode' => 643,
            'phonecode' => 70,
            'currency_id' => '177',
        ]);

        Country::create([
            'country_id' => 178,
            'country_code_char2' => 'RW',
            'country_name' => 'RWANDA',
            'nicename' => 'Rwanda',
            'country_code_char3' => 'RWA',
            'numcode' => 646,
            'phonecode' => 250,
            'currency_id' => '178',
        ]);

        Country::create([
            'country_id' => 179,
            'country_code_char2' => 'SH',
            'country_name' => 'SAINT HELENA',
            'nicename' => 'Saint Helena',
            'country_code_char3' => 'SHN',
            'numcode' => 654,
            'phonecode' => 290,
            'currency_id' => '179',
        ]);

        Country::create([
            'country_id' => 180,
            'country_code_char2' => 'KN',
            'country_name' => 'SAINT KITTS AND NEVIS',
            'nicename' => 'Saint Kitts and Nevis',
            'country_code_char3' => 'KNA',
            'numcode' => 659,
            'phonecode' => 1869,
            'currency_id' => '7',
        ]);

        Country::create([
            'country_id' => 181,
            'country_code_char2' => 'LC',
            'country_name' => 'SAINT LUCIA',
            'nicename' => 'Saint Lucia',
            'country_code_char3' => 'LCA',
            'numcode' => 662,
            'phonecode' => 1758,
            'currency_id' => '7',
        ]);

        Country::create([
            'country_id' => 182,
            'country_code_char2' => 'PM',
            'country_name' => 'SAINT PIERRE AND MIQUELON',
            'nicename' => 'Saint Pierre and Miquelon',
            'country_code_char3' => 'SPM',
            'numcode' => 666,
            'phonecode' => 508,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 183,
            'country_code_char2' => 'VC',
            'country_name' => 'SAINT VINCENT AND THE GRENADINES',
            'nicename' => 'Saint Vincent and the Grenadines',
            'country_code_char3' => 'VCT',
            'numcode' => 670,
            'phonecode' => 1784,
            'currency_id' => '7',
        ]);

        Country::create([
            'country_id' => 184,
            'country_code_char2' => 'WS',
            'country_name' => 'SAMOA',
            'nicename' => 'Samoa',
            'country_code_char3' => 'WSM',
            'numcode' => 882,
            'phonecode' => 684,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 185,
            'country_code_char2' => 'SM',
            'country_name' => 'SAN MARINO',
            'nicename' => 'San Marino',
            'country_code_char3' => 'SMR',
            'numcode' => 674,
            'phonecode' => 378,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 186,
            'country_code_char2' => 'ST',
            'country_name' => 'SAO TOME AND PRINCIPE',
            'nicename' => 'Sao Tome and Principe',
            'country_code_char3' => 'STP',
            'numcode' => 678,
            'phonecode' => 239,
            'currency_id' => '186',
        ]);

        Country::create([
            'country_id' => 187,
            'country_code_char2' => 'SA',
            'country_name' => 'SAUDI ARABIA',
            'nicename' => 'Saudi Arabia',
            'country_code_char3' => 'SAU',
            'numcode' => 682,
            'phonecode' => 966,
            'currency_id' => '187',
        ]);

        Country::create([
            'country_id' => 188,
            'country_code_char2' => 'SN',
            'country_name' => 'SENEGAL',
            'nicename' => 'Senegal',
            'country_code_char3' => 'SEN',
            'numcode' => 686,
            'phonecode' => 221,
            'currency_id' => '23',
        ]);

        Country::create([
            'country_id' => 189,
            'country_code_char2' => 'CS',
            'country_name' => 'SERBIA AND MONTENEGRO',
            'nicename' => 'Serbia and Montenegro',
            'country_code_char3' => null,
            'numcode' => null,
            'phonecode' => 381,
            'currency_id' => '189',
        ]);

        Country::create([
            'country_id' => 190,
            'country_code_char2' => 'SC',
            'country_name' => 'SEYCHELLES',
            'nicename' => 'Seychelles',
            'country_code_char3' => 'SYC',
            'numcode' => 690,
            'phonecode' => 248,
            'currency_id' => '190',
        ]);

        Country::create([
            'country_id' => 191,
            'country_code_char2' => 'SL',
            'country_name' => 'SIERRA LEONE',
            'nicename' => 'Sierra Leone',
            'country_code_char3' => 'SLE',
            'numcode' => 694,
            'phonecode' => 232,
            'currency_id' => '191',
        ]);

        Country::create([
            'country_id' => 192,
            'country_code_char2' => 'SG',
            'country_name' => 'SINGAPORE',
            'nicename' => 'Singapore',
            'country_code_char3' => 'SGP',
            'numcode' => 702,
            'phonecode' => 65,
            'currency_id' => '32',
        ]);

        Country::create([
            'country_id' => 193,
            'country_code_char2' => 'SK',
            'country_name' => 'SLOVAKIA',
            'nicename' => 'Slovakia',
            'country_code_char3' => 'SVK',
            'numcode' => 703,
            'phonecode' => 421,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 194,
            'country_code_char2' => 'SI',
            'country_name' => 'SLOVENIA',
            'nicename' => 'Slovenia',
            'country_code_char3' => 'SVN',
            'numcode' => 705,
            'phonecode' => 386,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 195,
            'country_code_char2' => 'SB',
            'country_name' => 'SOLOMON ISLANDS',
            'nicename' => 'Solomon Islands',
            'country_code_char3' => 'SLB',
            'numcode' => 90,
            'phonecode' => 677,
            'currency_id' => '195',
        ]);

        Country::create([
            'country_id' => 196,
            'country_code_char2' => 'SO',
            'country_name' => 'SOMALIA',
            'nicename' => 'Somalia',
            'country_code_char3' => 'SOM',
            'numcode' => 706,
            'phonecode' => 252,
            'currency_id' => '196',
        ]);

        Country::create([
            'country_id' => 197,
            'country_code_char2' => 'ZA',
            'country_name' => 'SOUTH AFRICA',
            'nicename' => 'South Africa',
            'country_code_char3' => 'ZAF',
            'numcode' => 710,
            'phonecode' => 27,
            'currency_id' => '197',
        ]);

        Country::create([
            'country_id' => 198,
            'country_code_char2' => 'GS',
            'country_name' => 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS',
            'nicename' => 'South Georgia and the South Sandwich Islands',
            'country_code_char3' => null,
            'numcode' => null,
            'phonecode' => 0,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 199,
            'country_code_char2' => 'ES',
            'country_name' => 'SPAIN',
            'nicename' => 'Spain',
            'country_code_char3' => 'ESP',
            'numcode' => 724,
            'phonecode' => 34,
            'currency_id' => '14',
        ]);

        Country::create([
            'country_id' => 200,
            'country_code_char2' => 'LK',
            'country_name' => 'SRI LANKA',
            'nicename' => 'Sri Lanka',
            'country_code_char3' => 'LKA',
            'numcode' => 144,
            'phonecode' => 94,
            'currency_id' => '200',
        ]);

        Country::create([
            'country_id' => 201,
            'country_code_char2' => 'SD',
            'country_name' => 'SUDAN',
            'nicename' => 'Sudan',
            'country_code_char3' => 'SDN',
            'numcode' => 736,
            'phonecode' => 249,
            'currency_id' => '201',
        ]);

        Country::create([
            'country_id' => 202,
            'country_code_char2' => 'SR',
            'country_name' => 'SURINAME',
            'nicename' => 'Suriname',
            'country_code_char3' => 'SUR',
            'numcode' => 740,
            'phonecode' => 597,
            'currency_id' => '202',
        ]);

        Country::create([
            'country_id' => 203,
            'country_code_char2' => 'SJ',
            'country_name' => 'SVALBARD AND JAN MAYEN',
            'nicename' => 'Svalbard and Jan Mayen',
            'country_code_char3' => 'SJM',
            'numcode' => 744,
            'phonecode' => 47,
            'currency_id' => '160',
        ]);

        Country::create([
            'country_id' => 204,
            'country_code_char2' => 'SZ',
            'country_name' => 'SWAZILAND',
            'nicename' => 'Swaziland',
            'country_code_char3' => 'SWZ',
            'numcode' => 748,
            'phonecode' => 268,
            'currency_id' => '204',
        ]);

        Country::create([
            'country_id' => 205,
            'country_code_char2' => 'SE',
            'country_name' => 'SWEDEN',
            'nicename' => 'Sweden',
            'country_code_char3' => 'SWE',
            'numcode' => 752,
            'phonecode' => 46,
            'currency_id' => '205',
        ]);

        Country::create([
            'country_id' => 206,
            'country_code_char2' => 'CH',
            'country_name' => 'SWITZERLAND',
            'nicename' => 'Switzerland',
            'country_code_char3' => 'CHE',
            'numcode' => 756,
            'phonecode' => 41,
            'currency_id' => '206',
        ]);

        Country::create([
            'country_id' => 207,
            'country_code_char2' => 'SY',
            'country_name' => 'SYRIAN ARAB REPUBLIC',
            'nicename' => 'Syrian Arab Republic',
            'country_code_char3' => 'SYR',
            'numcode' => 760,
            'phonecode' => 963,
            'currency_id' => '207',
        ]);

        Country::create([
            'country_id' => 208,
            'country_code_char2' => 'TW',
            'country_name' => 'TAIWAN, PROVINCE OF CHINA',
            'nicename' => 'Taiwan, Province of China',
            'country_code_char3' => 'TWN',
            'numcode' => 158,
            'phonecode' => 886,
            'currency_id' => '208',
        ]);

        Country::create([
            'country_id' => 209,
            'country_code_char2' => 'TJ',
            'country_name' => 'TAJIKISTAN',
            'nicename' => 'Tajikistan',
            'country_code_char3' => 'TJK',
            'numcode' => 762,
            'phonecode' => 992,
            'currency_id' => '209',
        ]);

        Country::create([
            'country_id' => 210,
            'country_code_char2' => 'TZ',
            'country_name' => 'TANZANIA, UNITED REPUBLIC OF',
            'nicename' => 'Tanzania, United Republic of',
            'country_code_char3' => 'TZA',
            'numcode' => 834,
            'phonecode' => 255,
            'currency_id' => '210',
        ]);

        Country::create([
            'country_id' => 211,
            'country_code_char2' => 'TH',
            'country_name' => 'THAILAND',
            'nicename' => 'Thailand',
            'country_code_char3' => 'THA',
            'numcode' => 764,
            'phonecode' => 66,
            'currency_id' => '211',
        ]);

        Country::create([
            'country_id' => 212,
            'country_code_char2' => 'TL',
            'country_name' => 'TIMOR-LESTE',
            'nicename' => 'Timor-Leste',
            'country_code_char3' => null,
            'numcode' => null,
            'phonecode' => 670,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 213,
            'country_code_char2' => 'TG',
            'country_name' => 'TOGO',
            'nicename' => 'Togo',
            'country_code_char3' => 'TGO',
            'numcode' => 768,
            'phonecode' => 228,
            'currency_id' => '23',
        ]);

        Country::create([
            'country_id' => 214,
            'country_code_char2' => 'TK',
            'country_name' => 'TOKELAU',
            'nicename' => 'Tokelau',
            'country_code_char3' => 'TKL',
            'numcode' => 772,
            'phonecode' => 690,
            'currency_id' => '51',
        ]);

        Country::create([
            'country_id' => 215,
            'country_code_char2' => 'TO',
            'country_name' => 'TONGA',
            'nicename' => 'Tonga',
            'country_code_char3' => 'TON',
            'numcode' => 776,
            'phonecode' => 676,
            'currency_id' => '215',
        ]);

        Country::create([
            'country_id' => 216,
            'country_code_char2' => 'TT',
            'country_name' => 'TRINIDAD AND TOBAGO',
            'nicename' => 'Trinidad and Tobago',
            'country_code_char3' => 'TTO',
            'numcode' => 780,
            'phonecode' => 1868,
            'currency_id' => '216',
        ]);

        Country::create([
            'country_id' => 217,
            'country_code_char2' => 'TN',
            'country_name' => 'TUNISIA',
            'nicename' => 'Tunisia',
            'country_code_char3' => 'TUN',
            'numcode' => 788,
            'phonecode' => 216,
            'currency_id' => '217',
        ]);

        Country::create([
            'country_id' => 218,
            'country_code_char2' => 'TR',
            'country_name' => 'TURKEY',
            'nicename' => 'Turkey',
            'country_code_char3' => 'TUR',
            'numcode' => 792,
            'phonecode' => 90,
            'currency_id' => '218',
        ]);

        Country::create([
            'country_id' => 219,
            'country_code_char2' => 'TM',
            'country_name' => 'TURKMENISTAN',
            'nicename' => 'Turkmenistan',
            'country_code_char3' => 'TKM',
            'numcode' => 795,
            'phonecode' => 7370,
            'currency_id' => '219',
        ]);

        Country::create([
            'country_id' => 220,
            'country_code_char2' => 'TC',
            'country_name' => 'TURKS AND CAICOS ISLANDS',
            'nicename' => 'Turks and Caicos Islands',
            'country_code_char3' => 'TCA',
            'numcode' => 796,
            'phonecode' => 1649,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 221,
            'country_code_char2' => 'TV',
            'country_name' => 'TUVALU',
            'nicename' => 'Tuvalu',
            'country_code_char3' => 'TUV',
            'numcode' => 798,
            'phonecode' => 688,
            'currency_id' => '13',
        ]);

        Country::create([
            'country_id' => 222,
            'country_code_char2' => 'UG',
            'country_name' => 'UGANDA',
            'nicename' => 'Uganda',
            'country_code_char3' => 'UGA',
            'numcode' => 800,
            'phonecode' => 256,
            'currency_id' => '222',
        ]);

        Country::create([
            'country_id' => 223,
            'country_code_char2' => 'UA',
            'country_name' => 'UKRAINE',
            'nicename' => 'Ukraine',
            'country_code_char3' => 'UKR',
            'numcode' => 804,
            'phonecode' => 380,
            'currency_id' => '223',
        ]);

        Country::create([
            'country_id' => 224,
            'country_code_char2' => 'AE',
            'country_name' => 'UNITED ARAB EMIRATES',
            'nicename' => 'United Arab Emirates',
            'country_code_char3' => 'ARE',
            'numcode' => 784,
            'phonecode' => 971,
            'currency_id' => '224',
        ]);

        Country::create([
            'country_id' => 225,
            'country_code_char2' => 'GB',
            'country_name' => 'UNITED KINGDOM',
            'nicename' => 'United Kingdom',
            'country_code_char3' => 'GBR',
            'numcode' => 826,
            'phonecode' => 44,
            'currency_id' => '225',
        ]);

        Country::create([
            'country_id' => 226,
            'country_code_char2' => 'US',
            'country_name' => 'UNITED STATES',
            'nicename' => 'United States',
            'country_code_char3' => 'USA',
            'numcode' => 840,
            'phonecode' => 1,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 227,
            'country_code_char2' => 'UM',
            'country_name' => 'UNITED STATES MINOR OUTLYING ISLANDS',
            'nicename' => 'United States Minor Outlying Islands',
            'country_code_char3' => null,
            'numcode' => null,
            'phonecode' => 1,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 228,
            'country_code_char2' => 'UY',
            'country_name' => 'URUGUAY',
            'nicename' => 'Uruguay',
            'country_code_char3' => 'URY',
            'numcode' => 858,
            'phonecode' => 598,
            'currency_id' => '228',
        ]);

        Country::create([
            'country_id' => 229,
            'country_code_char2' => 'UZ',
            'country_name' => 'UZBEKISTAN',
            'nicename' => 'Uzbekistan',
            'country_code_char3' => 'UZB',
            'numcode' => 860,
            'phonecode' => 998,
            'currency_id' => '229',
        ]);

        Country::create([
            'country_id' => 230,
            'country_code_char2' => 'VU',
            'country_name' => 'VANUATU',
            'nicename' => 'Vanuatu',
            'country_code_char3' => 'VUT',
            'numcode' => 548,
            'phonecode' => 678,
            'currency_id' => '230',
        ]);

        Country::create([
            'country_id' => 231,
            'country_code_char2' => 'VE',
            'country_name' => 'VENEZUELA',
            'nicename' => 'Venezuela',
            'country_code_char3' => 'VEN',
            'numcode' => 862,
            'phonecode' => 58,
            'currency_id' => '231',
        ]);

        Country::create([
            'country_id' => 232,
            'country_code_char2' => 'VN',
            'country_name' => 'VIET NAM',
            'nicename' => 'Viet Nam',
            'country_code_char3' => 'VNM',
            'numcode' => 704,
            'phonecode' => 84,
            'currency_id' => '232',
        ]);

        Country::create([
            'country_id' => 233,
            'country_code_char2' => 'VG',
            'country_name' => 'VIRGIN ISLANDS, BRITISH',
            'nicename' => 'Virgin Islands, British',
            'country_code_char3' => 'VGB',
            'numcode' => 92,
            'phonecode' => 1284,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 234,
            'country_code_char2' => 'VI',
            'country_name' => 'VIRGIN ISLANDS, U.S.',
            'nicename' => 'Virgin Islands, U.s.',
            'country_code_char3' => 'VIR',
            'numcode' => 850,
            'phonecode' => 1340,
            'currency_id' => '8',
        ]);

        Country::create([
            'country_id' => 235,
            'country_code_char2' => 'WF',
            'country_name' => 'WALLIS AND FUTUNA',
            'nicename' => 'Wallis and Futuna',
            'country_code_char3' => 'WLF',
            'numcode' => 876,
            'phonecode' => 681,
            'currency_id' => '152',
        ]);

        Country::create([
            'country_id' => 236,
            'country_code_char2' => 'EH',
            'country_name' => 'WESTERN SAHARA',
            'nicename' => 'Western Sahara',
            'country_code_char3' => 'ESH',
            'numcode' => 732,
            'phonecode' => 212,
            'currency_id' => '144',
        ]);

        Country::create([
            'country_id' => 237,
            'country_code_char2' => 'YE',
            'country_name' => 'YEMEN',
            'nicename' => 'Yemen',
            'country_code_char3' => 'YEM',
            'numcode' => 887,
            'phonecode' => 967,
            'currency_id' => '237',
        ]);

        Country::create([
            'country_id' => 238,
            'country_code_char2' => 'ZM',
            'country_name' => 'ZAMBIA',
            'nicename' => 'Zambia',
            'country_code_char3' => 'ZMB',
            'numcode' => 894,
            'phonecode' => 260,
            'currency_id' => '238',
        ]);

        Country::create([
            'country_id' => 239,
            'country_code_char2' => 'ZW',
            'country_name' => 'ZIMBABWE',
            'nicename' => 'Zimbabwe',
            'country_code_char3' => 'ZWE',
            'numcode' => 716,
            'phonecode' => 263,
            'currency_id' => '239',
        ]);
    }
}
