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
        if (! Schema::hasTable('tax_product_relations')) {
            Schema::create('tax_product_relations', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('product_id')->unsigned()->index('tax_product_relations_product_id_foreign');
                $table->integer('tax_class_id')->unsigned()->index('tax_product_relations_tax_id_foreign');
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
        Schema::drop('tax_product_relations');
    }
};
