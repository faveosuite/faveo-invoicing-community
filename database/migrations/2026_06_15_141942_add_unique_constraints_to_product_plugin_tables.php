<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicate rows before adding constraints
        \DB::statement('DELETE t1 FROM product_plugin_group t1
            INNER JOIN product_plugin_group t2
            WHERE t1.id > t2.id AND t1.product_id = t2.product_id AND t1.plugin_id = t2.plugin_id');

        \DB::statement('DELETE t1 FROM plugin_compatible_with_products t1
            INNER JOIN plugin_compatible_with_products t2
            WHERE t1.id > t2.id AND t1.plugin_id = t2.plugin_id AND t1.product_id = t2.product_id');

        Schema::table('product_plugin_group', function (Blueprint $table) {
            $table->unique(['product_id', 'plugin_id']);
        });

        Schema::table('plugin_compatible_with_products', function (Blueprint $table) {
            $table->unique(['plugin_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::table('product_plugin_group', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'plugin_id']);
        });

        Schema::table('plugin_compatible_with_products', function (Blueprint $table) {
            $table->dropUnique(['plugin_id', 'product_id']);
        });
    }
};
