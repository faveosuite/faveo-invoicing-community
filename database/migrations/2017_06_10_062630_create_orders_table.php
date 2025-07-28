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
        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('number')->unique('number');
                $table->integer('invoice_id');
                $table->integer('invoice_item_id');
                $table->integer('client')->unsigned()->index('orders_client_foreign');
                $table->string('order_status');
                $table->string('serial_key', 255)->nullable();
                $table->integer('product')->unsigned()->nullable()->index('orders_product_foreign');
                $table->string('domain');
                $table->string('price_override');
                $table->string('qty');
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
        Schema::drop('orders');
    }
};
