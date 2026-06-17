<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Schema::table('products', function (Blueprint $table) {
        //     // $table->foreign('group')->references('id')->on('product_groups')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        //     // $table->foreign('type')->references('id')->on('license_types')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('products', function (Blueprint $table) {
        //     $table->dropForeign('products_group_foreign');
        //     $table->dropForeign('products_type_foreign');
        // });
    }
};
