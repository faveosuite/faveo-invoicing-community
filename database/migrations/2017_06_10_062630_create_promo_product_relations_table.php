<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('promo_product_relations')) {
            Schema::create('promo_product_relations', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('promotion_id')->unsigned()->index('promo_product_relations_promotion_id_foreign');
                $table->integer('product_id')->unsigned()->index('promo_product_relations_product_id_foreign');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('promo_product_relations');
    }
};
