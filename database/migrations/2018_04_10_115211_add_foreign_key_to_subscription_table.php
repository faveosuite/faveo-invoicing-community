<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Schema::table('subscriptions', function (Blueprint $table) {
        //     $table->foreign('product_id')->references('id')->on('products');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // $table->dropForeign('subscriptions_product_id_foreign');
    }
};
