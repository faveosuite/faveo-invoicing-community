<?php

namespace Database\Seeders\v4_0_3;

use App\Model\Common\Country;
use App\Model\Common\State;
use App\Model\Payment\Currency;
use App\User;
use DB;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->countrySeeder();
    }

    public function countrySeeder(): void
    {
        $currencies = require database_path('seeders/v4_0_3/currencies.php');
        $countries = require database_path('seeders/v4_0_3/countries.php');
        $states = require database_path('seeders/v4_0_3/states.php');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        State::truncate();
        Country::truncate();
        Currency::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($currencies as $currency) {
            Currency::create([
                'id' => $currency['id'],
                'code' => $currency['code'],
                'name' => $currency['name'],
                'symbol' => $currency['symbol'],
                'dashboard_currency' => 0,
                'status' => 0,
            ]);
        }

        foreach ($countries as $country) {
            Country::create([
                'country_id' => $country['country_id'],
                'country_code_char2' => $country['country_code_char2'],
                'country_code_char3' => $country['country_code_char3'],
                'country_name' => $country['country_name'],
                'numcode' => $country['numcode'],
                'phonecode' => $country['phonecode'],
                'capital' => $country['capital'],
                'latitude' => $country['latitude'],
                'longitude' => $country['longitude'],
                'emoji' => $country['emoji'],
                'emojiU' => $country['emojiU'],
                'currency_id' => $country['currency_id'],
            ]);
        }

        foreach ($states as $state) {
            State::create([
                'state_subdivision_id' => $state['state_subdivision_id'],
                'state_subdivision_name' => $state['state_subdivision_name'],
                'country_code' => $state['country_code'],
                'iso2' => $state['iso2'],
                'primary_level_name' => $state['primary_level_name'],
                'latitude' => $state['latitude'],
                'longitude' => $state['longitude'],
                'country_id' => $state['country_id'],
            ]);
        }

        $users = User::all();

        foreach ($users as $user) {
            $parts = explode('-', $user->state ?? '');
            $user->state = $parts[1] ?? null;
            $user->save();
        }

    }
}