<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('products', function (Blueprint $table) {
        //     // $table->foreign('group')->references('id')->on('product_groups')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        //     // $table->foreign('type')->references('id')->on('license_types')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('products', function (Blueprint $table) {
        //     $table->dropForeign('products_group_foreign');
        //     $table->dropForeign('products_type_foreign');
        // });
    }
};
