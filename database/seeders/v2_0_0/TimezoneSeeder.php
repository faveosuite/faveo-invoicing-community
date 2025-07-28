<?php

namespace Database\Seeders\v2_0_0;

use App\Model\Common\Timezone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimezoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('timezone')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Timezone::create([
            'id' => 1,
            'name' => 'Pacific/Midway',
            'location' => '(GMT-11:00) Midway Island',
        ]);

        Timezone::create([
            'id' => 2,
            'name' => 'US/Samoa',
            'location' => '(GMT-11:00) Samoa',
        ]);

        Timezone::create([
            'id' => 3,
            'name' => 'US/Hawaii',
            'location' => '(GMT-10:00) Hawaii',
        ]);

        Timezone::create([
            'id' => 4,
            'name' => 'US/Alaska',
            'location' => '(GMT-09:00) Alaska',
        ]);

        Timezone::create([
            'id' => 5,
            'name' => 'US/Pacific',
            'location' => '(GMT-08:00) Pacific Time (US & Canada)',
        ]);

        Timezone::create([
            'id' => 6,
            'name' => 'America/Tijuana',
            'location' => '(GMT-08:00) Tijuana',
        ]);

        Timezone::create([
            'id' => 7,
            'name' => 'US/Arizona',
            'location' => '(GMT-07:00) Arizona',
        ]);

        Timezone::create([
            'id' => 8,
            'name' => 'US/Mountain',
            'location' => '(GMT-07:00) Mountain Time (US & Canada)',
        ]);

        Timezone::create([
            'id' => 9,
            'name' => 'America/Chihuahua',
            'location' => '(GMT-07:00) Chihuahua',
        ]);

        Timezone::create([
            'id' => 10,
            'name' => 'America/Mazatlan',
            'location' => '(GMT-07:00) Mazatlan',
        ]);

        Timezone::create([
            'id' => 11,
            'name' => 'America/Mexico_City',
            'location' => '(GMT-06:00) Mexico City',
        ]);

        Timezone::create([
            'id' => 12,
            'name' => 'America/Monterrey',
            'location' => '(GMT-06:00) Monterrey',
        ]);

        Timezone::create([
            'id' => 13,
            'name' => 'Canada/Saskatchewan',
            'location' => '(GMT-06:00) Saskatchewan',
        ]);

        Timezone::create([
            'id' => 14,
            'name' => 'US/Central',
            'location' => '(GMT-06:00) Central Time (US & Canada)',
        ]);

        Timezone::create([
            'id' => 15,
            'name' => 'US/Eastern',
            'location' => '(GMT-05:00) Eastern Time (US & Canada)',
        ]);

        Timezone::create([
            'id' => 16,
            'name' => 'US/East-Indiana',
            'location' => '(GMT-05:00) Indiana (East)',
        ]);

        Timezone::create([
            'id' => 17,
            'name' => 'America/Bogota',
            'location' => '(GMT-05:00) Bogota',
        ]);

        Timezone::create([
            'id' => 18,
            'name' => 'America/Lima',
            'location' => '(GMT-05:00) Lima',
        ]);

        Timezone::create([
            'id' => 19,
            'name' => 'America/Caracas',
            'location' => '(GMT-04:30) Caracas',
        ]);

        Timezone::create([
            'id' => 20,
            'name' => 'Canada/Atlantic',
            'location' => '(GMT-04:00) Atlantic Time (Canada)',
        ]);

        Timezone::create([
            'id' => 21,
            'name' => 'America/La_Paz',
            'location' => '(GMT-04:00) La Paz',
        ]);

        Timezone::create([
            'id' => 22,
            'name' => 'America/Santiago',
            'location' => '(GMT-04:00) Santiago',
        ]);

        Timezone::create([
            'id' => 23,
            'name' => 'Canada/Newfoundland',
            'location' => '(GMT-03:30) Newfoundland',
        ]);

        Timezone::create([
            'id' => 24,
            'name' => 'America/Buenos_Aires',
            'location' => '(GMT-03:00) Buenos Aires',
        ]);

        Timezone::create([
            'id' => 25,
            'name' => 'Greenland',
            'location' => '(GMT-03:00) Greenland',
        ]);

        Timezone::create([
            'id' => 26,
            'name' => 'Atlantic/Stanley',
            'location' => '(GMT-02:00) Stanley',
        ]);

        Timezone::create([
            'id' => 27,
            'name' => 'Atlantic/Azores',
            'location' => '(GMT-01:00) Azores',
        ]);

        Timezone::create([
            'id' => 28,
            'name' => 'Atlantic/Cape_Verde',
            'location' => '(GMT-01:00) Cape Verde Is.',
        ]);

        Timezone::create([
            'id' => 29,
            'name' => 'Africa/Casablanca',
            'location' => '(GMT) Casablanca',
        ]);

        Timezone::create([
            'id' => 30,
            'name' => 'Europe/Dublin',
            'location' => '(GMT) Dublin',
        ]);

        Timezone::create([
            'id' => 31,
            'name' => 'Europe/Lisbon',
            'location' => '(GMT) Lisbon',
        ]);

        Timezone::create([
            'id' => 32,
            'name' => 'Europe/London',
            'location' => '(GMT) London',
        ]);

        Timezone::create([
            'id' => 33,
            'name' => 'Africa/Monrovia',
            'location' => '(GMT) Monrovia',
        ]);

        Timezone::create([
            'id' => 34,
            'name' => 'Europe/Amsterdam',
            'location' => '(GMT+01:00) Amsterdam',
        ]);

        Timezone::create([
            'id' => 35,
            'name' => 'Europe/Belgrade',
            'location' => '(GMT+01:00) Belgrade',
        ]);

        Timezone::create([
            'id' => 36,
            'name' => 'Europe/Berlin',
            'location' => '(GMT+01:00) Berlin',
        ]);

        Timezone::create([
            'id' => 37,
            'name' => 'Europe/Bratislava',
            'location' => '(GMT+01:00) Bratislava',
        ]);

        Timezone::create([
            'id' => 38,
            'name' => 'Europe/Brussels',
            'location' => '(GMT+01:00) Brussels',
        ]);

        Timezone::create([
            'id' => 39,
            'name' => 'Europe/Budapest',
            'location' => '(GMT+01:00) Budapest',
        ]);

        Timezone::create([
            'id' => 40,
            'name' => 'Europe/Copenhagen',
            'location' => '(GMT+01:00) Copenhagen',
        ]);

        Timezone::create([
            'id' => 41,
            'name' => 'Europe/Ljubljana',
            'location' => '(GMT+01:00) Ljubljana',
        ]);

        Timezone::create([
            'id' => 42,
            'name' => 'Europe/Madrid',
            'location' => '(GMT+01:00) Madrid',
        ]);

        Timezone::create([
            'id' => 43,
            'name' => 'Europe/Paris',
            'location' => '(GMT+01:00) Paris',
        ]);

        Timezone::create([
            'id' => 44,
            'name' => 'Europe/Prague',
            'location' => '(GMT+01:00) Prague',
        ]);

        Timezone::create([
            'id' => 45,
            'name' => 'Europe/Rome',
            'location' => '(GMT+01:00) Rome',
        ]);

        Timezone::create([
            'id' => 46,
            'name' => 'Europe/Sarajevo',
            'location' => '(GMT+01:00) Sarajevo',
        ]);

        Timezone::create([
            'id' => 47,
            'name' => 'Europe/Skopje',
            'location' => '(GMT+01:00) Skopje',
        ]);

        Timezone::create([
            'id' => 48,
            'name' => 'Europe/Stockholm',
            'location' => '(GMT+01:00) Stockholm',
        ]);

        Timezone::create([
            'id' => 49,
            'name' => 'Europe/Vienna',
            'location' => '(GMT+01:00) Vienna',
        ]);

        Timezone::create([
            'id' => 50,
            'name' => 'Europe/Warsaw',
            'location' => '(GMT+01:00) Warsaw',
        ]);

        Timezone::create([
            'id' => 51,
            'name' => 'Europe/Zagreb',
            'location' => '(GMT+01:00) Zagreb',
        ]);

        Timezone::create([
            'id' => 52,
            'name' => 'Europe/Athens',
            'location' => '(GMT+02:00) Athens',
        ]);

        Timezone::create([
            'id' => 53,
            'name' => 'Europe/Bucharest',
            'location' => '(GMT+02:00) Bucharest',
        ]);

        Timezone::create([
            'id' => 54,
            'name' => 'Africa/Cairo',
            'location' => '(GMT+02:00) Cairo',
        ]);

        Timezone::create([
            'id' => 55,
            'name' => 'Africa/Harare',
            'location' => '(GMT+02:00) Harare',
        ]);

        Timezone::create([
            'id' => 56,
            'name' => 'Europe/Helsinki',
            'location' => '(GMT+02:00) Helsinki',
        ]);

        Timezone::create([
            'id' => 57,
            'name' => 'Europe/Istanbul',
            'location' => '(GMT+02:00) Istanbul',
        ]);

        Timezone::create([
            'id' => 58,
            'name' => 'Asia/Jerusalem',
            'location' => '(GMT+02:00) Jerusalem',
        ]);

        Timezone::create([
            'id' => 59,
            'name' => 'Europe/Kiev',
            'location' => '(GMT+02:00) Kyiv',
        ]);

        Timezone::create([
            'id' => 60,
            'name' => 'Europe/Minsk',
            'location' => '(GMT+02:00) Minsk',
        ]);

        Timezone::create([
            'id' => 61,
            'name' => 'Europe/Riga',
            'location' => '(GMT+02:00) Riga',
        ]);

        Timezone::create([
            'id' => 62,
            'name' => 'Europe/Sofia',
            'location' => '(GMT+02:00) Sofia',
        ]);

        Timezone::create([
            'id' => 63,
            'name' => 'Europe/Tallinn',
            'location' => '(GMT+02:00) Tallinn',
        ]);

        Timezone::create([
            'id' => 64,
            'name' => 'Europe/Vilnius',
            'location' => '(GMT+02:00) Vilnius',
        ]);

        Timezone::create([
            'id' => 65,
            'name' => 'Asia/Baghdad',
            'location' => '(GMT+03:00) Baghdad',
        ]);

        Timezone::create([
            'id' => 66,
            'name' => 'Asia/Kuwait',
            'location' => '(GMT+03:00) Kuwait',
        ]);

        Timezone::create([
            'id' => 67,
            'name' => 'Africa/Nairobi',
            'location' => '(GMT+03:00) Nairobi',
        ]);

        Timezone::create([
            'id' => 68,
            'name' => 'Asia/Riyadh',
            'location' => '(GMT+03:00) Riyadh',
        ]);

        Timezone::create([
            'id' => 69,
            'name' => 'Asia/Tehran',
            'location' => '(GMT+03:30) Tehran',
        ]);

        Timezone::create([
            'id' => 70,
            'name' => 'Europe/Moscow',
            'location' => '(GMT+04:00) Moscow',
        ]);

        Timezone::create([
            'id' => 71,
            'name' => 'Asia/Baku',
            'location' => '(GMT+04:00) Baku',
        ]);

        Timezone::create([
            'id' => 72,
            'name' => 'Europe/Volgograd',
            'location' => '(GMT+04:00) Volgograd',
        ]);

        Timezone::create([
            'id' => 73,
            'name' => 'Asia/Muscat',
            'location' => '(GMT+04:00) Muscat',
        ]);

        Timezone::create([
            'id' => 74,
            'name' => 'Asia/Tbilisi',
            'location' => '(GMT+04:00) Tbilisi',
        ]);

        Timezone::create([
            'id' => 75,
            'name' => 'Asia/Yerevan',
            'location' => '(GMT+04:00) Yerevan',
        ]);

        Timezone::create([
            'id' => 76,
            'name' => 'Asia/Kabul',
            'location' => '(GMT+04:30) Kabul',
        ]);

        Timezone::create([
            'id' => 77,
            'name' => 'Asia/Karachi',
            'location' => '(GMT+05:00) Karachi',
        ]);

        Timezone::create([
            'id' => 78,
            'name' => 'Asia/Tashkent',
            'location' => '(GMT+05:00) Tashkent',
        ]);

        Timezone::create([
            'id' => 79,
            'name' => 'Asia/Kolkata',
            'location' => '(GMT+05:30) Kolkata',
        ]);

        Timezone::create([
            'id' => 80,
            'name' => 'Asia/Kathmandu',
            'location' => '(GMT+05:45) Kathmandu',
        ]);

        Timezone::create([
            'id' => 81,
            'name' => 'Asia/Yekaterinburg',
            'location' => '(GMT+06:00) Ekaterinburg',
        ]);

        Timezone::create([
            'id' => 82,
            'name' => 'Asia/Almaty',
            'location' => '(GMT+06:00) Almaty',
        ]);

        Timezone::create([
            'id' => 83,
            'name' => 'Asia/Dhaka',
            'location' => '(GMT+06:00) Dhaka',
        ]);

        Timezone::create([
            'id' => 84,
            'name' => 'Asia/Novosibirsk',
            'location' => '(GMT+07:00) Novosibirsk',
        ]);

        Timezone::create([
            'id' => 85,
            'name' => 'Asia/Bangkok',
            'location' => '(GMT+07:00) Bangkok',
        ]);

        Timezone::create([
            'id' => 86,
            'name' => 'Asia/Ho_Chi_Minh',
            'location' => '(GMT+07.00) Ho Chi Minh',
        ]);

        Timezone::create([
            'id' => 87,
            'name' => 'Asia/Jakarta',
            'location' => '(GMT+07:00) Jakarta',
        ]);

        Timezone::create([
            'id' => 88,
            'name' => 'Asia/Krasnoyarsk',
            'location' => '(GMT+08:00) Krasnoyarsk',
        ]);

        Timezone::create([
            'id' => 89,
            'name' => 'Asia/Chongqing',
            'location' => '(GMT+08:00) Chongqing',
        ]);

        Timezone::create([
            'id' => 90,
            'name' => 'Asia/Hong_Kong',
            'location' => '(GMT+08:00) Hong Kong',
        ]);

        Timezone::create([
            'id' => 91,
            'name' => 'Asia/Kuala_Lumpur',
            'location' => '(GMT+08:00) Kuala Lumpur',
        ]);

        Timezone::create([
            'id' => 92,
            'name' => 'Australia/Perth',
            'location' => '(GMT+08:00) Perth',
        ]);

        Timezone::create([
            'id' => 93,
            'name' => 'Asia/Singapore',
            'location' => '(GMT+08:00) Singapore',
        ]);

        Timezone::create([
            'id' => 94,
            'name' => 'Asia/Taipei',
            'location' => '(GMT+08:00) Taipei',
        ]);

        Timezone::create([
            'id' => 95,
            'name' => 'Asia/Ulaanbaatar',
            'location' => '(GMT+08:00) Ulaan Bataar',
        ]);

        Timezone::create([
            'id' => 96,
            'name' => 'Asia/Urumqi',
            'location' => '(GMT+08:00) Urumqi',
        ]);

        Timezone::create([
            'id' => 97,
            'name' => 'Asia/Irkutsk',
            'location' => '(GMT+09:00) Irkutsk',
        ]);

        Timezone::create([
            'id' => 98,
            'name' => 'Asia/Seoul',
            'location' => '(GMT+09:00) Seoul',
        ]);

        Timezone::create([
            'id' => 99,
            'name' => 'Asia/Tokyo',
            'location' => '(GMT+09:00) Tokyo',
        ]);

        Timezone::create([
            'id' => 100,
            'name' => 'Australia/Adelaide',
            'location' => '(GMT+09:30) Adelaide',
        ]);

        Timezone::create([
            'id' => 101,
            'name' => 'Australia/Darwin',
            'location' => '(GMT+09:30) Darwin',
        ]);

        Timezone::create([
            'id' => 102,
            'name' => 'Asia/Yakutsk',
            'location' => '(GMT+10:00) Yakutsk',
        ]);

        Timezone::create([
            'id' => 103,
            'name' => 'Australia/Brisbane',
            'location' => '(GMT+10:00) Brisbane',
        ]);

        Timezone::create([
            'id' => 104,
            'name' => 'Australia/Canberra',
            'location' => '(GMT+10:00) Canberra',
        ]);

        Timezone::create([
            'id' => 105,
            'name' => 'Pacific/Guam',
            'location' => '(GMT+10:00) Guam',
        ]);

        Timezone::create([
            'id' => 106,
            'name' => 'Australia/Hobart',
            'location' => '(GMT+10:00) Hobart',
        ]);

        Timezone::create([
            'id' => 107,
            'name' => 'Australia/Melbourne',
            'location' => '(GMT+10:00) Melbourne',
        ]);

        Timezone::create([
            'id' => 108,
            'name' => 'Pacific/Port_Moresby',
            'location' => '(GMT+10:00) Port Moresby',
        ]);

        Timezone::create([
            'id' => 109,
            'name' => 'Australia/Sydney',
            'location' => '(GMT+10:00) Sydney',
        ]);

        Timezone::create([
            'id' => 110,
            'name' => 'Asia/Vladivostok',
            'location' => '(GMT+11:00) Vladivostok',
        ]);

        Timezone::create([
            'id' => 111,
            'name' => 'Asia/Magadan',
            'location' => '(GMT+12:00) Magadan',
        ]);

        Timezone::create([
            'id' => 112,
            'name' => 'Pacific/Auckland',
            'location' => '(GMT+12:00) Auckland',
        ]);

        Timezone::create([
            'id' => 113,
            'name' => 'Pacific/Fiji',
            'location' => '(GMT+12:00) Fiji',
        ]);

        Timezone::create([
            'id' => 114,
            'name' => 'UTC',
            'location' => '',
        ]);
    }
}
