<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_bundle_relations', function (Blueprint $table): void {
            $table->foreign('bundle_id')->references('id')->on('product_bundles')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('product_id')->references('id')->on('products')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_bundle_relations', function (Blueprint $table): void {
            $table->dropForeign('product_bundle_relations_bundle_id_foreign');
            $table->dropForeign('product_bundle_relations_product_id_foreign');
        });
    }
};
