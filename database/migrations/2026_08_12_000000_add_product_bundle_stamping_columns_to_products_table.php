<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            // The key this product's own build authenticates itself with, and
            // the salt/config path used to stamp that identity into a shared
            // canonical zip on every download (see ProductBundleStampingService).
            $table->string('product_key')->nullable();
            $table->string('apl_salt')->nullable();
            $table->string('config_file_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['product_key', 'apl_salt', 'config_file_path']);
        });
    }
};
