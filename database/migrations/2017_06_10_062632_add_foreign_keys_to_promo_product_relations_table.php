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
        Schema::table('promo_product_relations', function (Blueprint $table): void {
            $table->foreign('product_id')->references('id')->on('products')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('promotion_id')->references('id')->on('promotions')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promo_product_relations', function (Blueprint $table): void {
            $table->dropForeign('promo_product_relations_product_id_foreign');
            $table->dropForeign('promo_product_relations_promotion_id_foreign');
        });
    }
};
