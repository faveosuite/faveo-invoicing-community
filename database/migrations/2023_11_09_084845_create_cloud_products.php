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
        Schema::create('cloud_products', function (Blueprint $table) {
            $table->id();
            $table->integer('cloud_product')->unsigned();
            $table->integer('cloud_free_plan')->unsigned();
            $table->foreign('cloud_product')->references('id')->on('products');
            $table->foreign('cloud_free_plan')->references('id')->on('plans');
            $table->string('cloud_product_key');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cloud_products');
    }
};
