<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add columns from license system's afl_products and afu_products to billing's products table.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('product_url_homepage')->nullable()->after('product_description');
            $table->string('product_url_download')->nullable()->after('product_url_homepage');
            $table->string('product_envato_id')->nullable()->after('product_url_download');
            $table->string('product_key')->nullable()->after('product_envato_id');
            $table->integer('product_max_active_versions')->default(0)->after('product_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn([
                'product_url_homepage',
                'product_url_download',
                'product_envato_id',
                'product_key',
                'product_max_active_versions',
            ]);
        });
    }
};
