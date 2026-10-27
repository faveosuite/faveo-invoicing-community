<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('prices')) {
            Schema::create('prices', function (Blueprint $table): void {
                $table->increments('id');
                $table->integer('product_id')->unsigned()->index('prices_product_id_foreign');
                $table->string('currency');
                $table->integer('subscription')->unsigned()->index('prices_subscription_foreign');
                $table->string('price');
                $table->string('sales_price');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('prices');
    }
};
