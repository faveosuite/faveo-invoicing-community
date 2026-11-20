<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Faveo v9.4.3.9.B.2 merged each plugin's "Pro" and non-Pro products into one product
     * (see config/merged_products.php). A license issued for the retired product is moved
     * onto the current one the first time a B.2+ client asks for it; these columns mark that
     * it was moved — and from which product — so it is never moved twice, and so a site
     * still on an older Faveo, which keeps asking for the retired product, is still served.
     *
     * licenses: an add-on bought on its own. license_plugins: a plugin bundled into a license.
     */
    public function up(): void
    {
        foreach (['licenses', 'license_plugins'] as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->unsignedInteger('migrated_from_product_id')->nullable()->index();
                $table->timestamp('product_migrated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['licenses', 'license_plugins'] as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->dropIndex(['migrated_from_product_id']);
                $table->dropColumn(['migrated_from_product_id', 'product_migrated_at']);
            });
        }
    }
};
