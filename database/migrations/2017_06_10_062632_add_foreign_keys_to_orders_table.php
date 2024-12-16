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
        Schema::table('orders', function (Blueprint $table) {
            // Fetch all indexes for the orders table
            $indexes = DB::select("SHOW INDEX FROM orders");

            // Helper function to check if a specific index exists
            $indexExists = function ($indexName) use ($indexes) {
                foreach ($indexes as $index) {
                    if ($index->Key_name === $indexName) {
                        return true;
                    }
                }
                return false;
            };

            // Check and add foreign key for client
            if (! $indexExists('orders_client_foreign')) {
                $table->foreign('client')
                    ->references('id')
                    ->on('users')
                    ->onUpdate('RESTRICT')
                    ->onDelete('RESTRICT');
            }

            // Check and add foreign key for product
            if (! $indexExists('orders_product_foreign')) {
                $table->foreign('product')
                    ->references('id')
                    ->on('products')
                    ->onUpdate('RESTRICT')
                    ->onDelete('RESTRICT');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_client_foreign');
            $table->dropForeign('orders_product_foreign');
        });
    }
};
