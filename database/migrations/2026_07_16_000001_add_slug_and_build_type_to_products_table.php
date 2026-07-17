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
            // Stable identity shared across a product/plugin's build variants
            // (e.g. 'adhoc-approval' for both its obfuscated and source
            // rows) — matched against the `path` already embedded in a
            // shipped plugin's own config.php.
            $table->string('slug')->nullable()->after('product_sku');

            // Which build variant this row is (e.g. 'obfuscated', 'source').
            // Deliberately generic — not tied to ionCube or any one vendor's
            // build pipeline.
            $table->string('build_type')->nullable()->after('slug');

            $table->unique(['slug', 'build_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique(['slug', 'build_type']);
            $table->dropColumn(['slug', 'build_type']);
        });
    }
};
