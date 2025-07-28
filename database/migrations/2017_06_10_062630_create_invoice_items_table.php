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
        if (! Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('invoice_id')->unsigned()->index('invoice_items_invoice_id_foreign');
                $table->string('product_name');
                $table->string('regular_price');
                $table->string('quantity');
                $table->string('discount');
                $table->string('tax_name');
                $table->string('tax_percentage');
                $table->string('tax_code');
                $table->string('discount_mode');
                $table->string('subtotal', 225);
                $table->string('domain', 225);
                $table->integer('plan_id')->nullable();
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
        Schema::drop('invoice_items');
    }
};
