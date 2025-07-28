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
        Schema::table('product_bundle_relations', function (Blueprint $table) {
            $table->foreign('bundle_id')->references('id')->on('product_bundles')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('product_id')->references('id')->on('products')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_bundle_relations', function (Blueprint $table) {
            $table->dropForeign('product_bundle_relations_bundle_id_foreign');
            $table->dropForeign('product_bundle_relations_product_id_foreign');
        });
    }
};
