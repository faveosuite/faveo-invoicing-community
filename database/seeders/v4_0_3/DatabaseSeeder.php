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
        $currencies = collect(require database_path('seeders/v4_0_3/currencies.php'));
        $countries = collect(require database_path('seeders/v4_0_3/countries.php'));
        $states = collect(require database_path('seeders/v4_0_3/states.php'));

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate tables
        State::truncate();
        Country::truncate();
        Currency::truncate();

        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Chunked bulk inserts for currencies
        $currencies->chunk(500)->each(function($chunk){
            DB::table('currencies')->insert(
                $chunk->map(function($c){
                    return [
                        'id' => $c['id'],
                        'code' => $c['code'],
                        'name' => $c['name'],
                        'symbol' => $c['symbol'],
                        'dashboard_currency' => $c['code'] === 'USD' ? 1 : 0,
                        'status' => $c['code'] === 'USD' ? 1 : 0,
                    ];
                })->toArray()
            );
        });

        // Chunked bulk inserts for countries
        $countries->chunk(500)->each(function($chunk){
            DB::table('countries')->insert(
                $chunk->map(function($c){
                    return [
                        'country_id' => $c['country_id'],
                        'country_code_char2' => $c['country_code_char2'],
                        'country_code_char3' => $c['country_code_char3'],
                        'country_name' => $c['country_name'],
                        'numcode' => $c['numcode'],
                        'phonecode' => $c['phonecode'],
                        'capital' => $c['capital'],
                        'latitude' => $c['latitude'],
                        'longitude' => $c['longitude'],
                        'emoji' => $c['emoji'],
                        'emojiU' => $c['emojiU'],
                        'currency_id' => $c['currency_id'],
                        'status' => $c['country_code_char2'] === 'AQ' ? 0 : 1,
                    ];
                })->toArray()
            );
        });

        // Chunked bulk inserts for states
        $states->chunk(500)->each(function($chunk){
            DB::table('states_subdivisions')->insert(
                $chunk->map(function($s){
                    return [
                        'state_subdivision_id' => $s['state_subdivision_id'],
                        'state_subdivision_name' => $s['state_subdivision_name'],
                        'country_code' => $s['country_code'],
                        'iso2' => $s['iso2'],
                        'primary_level_name' => $s['primary_level_name'],
                        'latitude' => $s['latitude'],
                        'longitude' => $s['longitude'],
                        'country_id' => $s['country_id'],
                    ];
                })->toArray()
            );
        });

        // Update users in a single query
        DB::table('users')
            ->whereNotNull('state')
            ->update([
                'state' => DB::raw("SUBSTRING_INDEX(state, '-', -1)")
            ]);
    }
}