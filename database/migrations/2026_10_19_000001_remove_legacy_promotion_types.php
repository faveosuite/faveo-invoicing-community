<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * "Price Override" (3) and "Free Setup" (4) lost their discount logic in 2020
 * (105cc49d2a) but stayed in the dropdown, silently acting as an uncapped Fixed
 * Amount. Fresh installs already seed only 1 and 2; this cleans older databases.
 * A type still referenced by a promotion is left alone rather than orphaning it.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('promotion_types')
            ->whereIn('id', [3, 4])
            ->whereNotIn('id', DB::table('promotions')->select('type'))
            ->delete();
    }

    public function down(): void
    {
        DB::table('promotion_types')->insertOrIgnore([
            ['id' => 3, 'name' => 'Price Override'],
            ['id' => 4, 'name' => 'Free Setup'],
        ]);
    }
};
