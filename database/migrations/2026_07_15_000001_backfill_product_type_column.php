<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $pluginTypeId = DB::table('license_types')->where('name', 'plugin')->value('id');

        if ($pluginTypeId) {
            DB::table('products')->where('type', $pluginTypeId)->update(['product_type' => 'addon']);
        }

        DB::table('products')->whereNull('product_type')->update(['product_type' => 'independent']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('products')->update(['product_type' => null]);
    }
};
