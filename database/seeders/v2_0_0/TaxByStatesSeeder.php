<?php

namespace Database\Seeders\v2_0_0;

use App\Model\Payment\TaxByState;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxByStatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('tax_by_states')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        TaxByState::create([
            'id' => 1,
            'country' => 'IN',
            'state_code' => 'IN-AN',
            'state' => 'Andaman Nicobar Islands',
            'c_gst' => '9',
            's_gst' => 'NULL',
            'i_gst' => '18',
            'ut_gst' => '9',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 2,
            'country' => 'IN',
            'state_code' => 'IN-AP',
            'state' => 'Andhra Pradesh',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 3,
            'country' => 'IN',
            'state_code' => 'IN-AR',
            'state' => 'Arunachal Pradesh',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 4,
            'country' => 'IN',
            'state_code' => 'IN-AS',
            'state' => 'Assam',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 5,
            'country' => 'IN',
            'state_code' => 'IN-BR',
            'state' => 'Bihar',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 6,
            'country' => 'IN',
            'state_code' => 'IN-CH',
            'state' => 'Chandigarh',
            'c_gst' => '9',
            's_gst' => 'NULL',
            'i_gst' => '18',
            'ut_gst' => '9',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 7,
            'country' => 'IN',
            'state_code' => 'IN-CT',
            'state' => 'Chhattisgarh',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 8,
            'country' => 'IN',
            'state_code' => 'IN-DN',
            'state' => 'Dadra and Nagar Haveli',
            'c_gst' => '9',
            's_gst' => 'NULL',
            'i_gst' => '18',
            'ut_gst' => '9',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 9,
            'country' => 'IN',
            'state_code' => 'IN-DD',
            'state' => 'Daman and Diu',
            'c_gst' => '9',
            's_gst' => 'NULL',
            'i_gst' => '18',
            'ut_gst' => '9',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 10,
            'country' => 'IN',
            'state_code' => 'IN-DL',
            'state' => 'Delhi',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 11,
            'country' => 'IN',
            'state_code' => 'IN-GA',
            'state' => 'Goa',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 12,
            'country' => 'IN',
            'state_code' => 'IN-GJ',
            'state' => 'Gujarat',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 13,
            'country' => 'IN',
            'state_code' => 'IN-HR',
            'state' => 'Haryana',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 14,
            'country' => 'IN',
            'state_code' => 'IN-HP',
            'state' => 'Himachal pradesh',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 15,
            'country' => 'IN',
            'state_code' => 'IN-JK',
            'state' => 'Jammu and Kashmir',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 16,
            'country' => 'IN',
            'state_code' => 'IN-JH',
            'state' => 'Jharkand',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 17,
            'country' => 'IN',
            'state_code' => 'IN-KA',
            'state' => 'Karnataka',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 18,
            'country' => 'IN',
            'state_code' => 'IN-KL',
            'state' => 'Kerala',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 19,
            'country' => 'IN',
            'state_code' => 'IN-LD',
            'state' => 'Lakshadweep',
            'c_gst' => '9',
            's_gst' => 'NULL',
            'i_gst' => '18',
            'ut_gst' => '9',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 20,
            'country' => 'IN',
            'state_code' => 'IN-MP',
            'state' => 'Madhya Pradesh',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 21,
            'country' => 'IN',
            'state_code' => 'IN-MH',
            'state' => 'Maharashtra',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 22,
            'country' => 'IN',
            'state_code' => 'IN-MN',
            'state' => 'Manipur',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 23,
            'country' => 'IN',
            'state_code' => 'IN-ML',
            'state' => 'Megalaya',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 24,
            'country' => 'IN',
            'state_code' => 'IN-MZ',
            'state' => 'Mizoram',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 25,
            'country' => 'IN',
            'state_code' => 'IN-NL',
            'state' => 'Nagaland',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 26,
            'country' => 'IN',
            'state_code' => 'IN-OR',
            'state' => 'Orissa',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 27,
            'country' => 'IN',
            'state_code' => 'IN-OR',
            'state' => 'Orissa',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 28,
            'country' => 'IN',
            'state_code' => 'IN-PY',
            'state' => 'Pondichery',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 29,
            'country' => 'IN',
            'state_code' => 'IN-PB',
            'state' => 'Punjab',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 30,
            'country' => 'IN',
            'state_code' => 'IN-RJ',
            'state' => 'Rajastan',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 31,
            'country' => 'IN',
            'state_code' => 'IN-SK',
            'state' => 'Sikkim',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 32,
            'country' => 'IN',
            'state_code' => 'IN-TN',
            'state' => 'Tamilnadu',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 33,
            'country' => 'IN',
            'state_code' => 'IN-TS',
            'state' => 'Telangana',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 34,
            'country' => 'IN',
            'state_code' => 'IN-TR',
            'state' => 'Tripura',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 35,
            'country' => 'IN',
            'state_code' => 'IN-UP',
            'state' => 'Uttar Pradesh',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 36,
            'country' => 'IN',
            'state_code' => 'IN-UL',
            'state' => 'Uttaranchal',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);

        TaxByState::create([
            'id' => 37,
            'country' => 'IN',
            'state_code' => 'IN-WB',
            'state' => 'West Bengal',
            'c_gst' => '9',
            's_gst' => '9',
            'i_gst' => '18',
            'ut_gst' => 'NULL',
            'created_at' => '2018-04-13 12:53:58',
            'updated_at' => '2018-04-13 12:53:58',
        ]);
    }
}
