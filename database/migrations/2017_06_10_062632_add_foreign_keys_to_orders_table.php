<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            // Fetch all indexes for the orders table
            $indexes = DB::select('SHOW INDEX FROM orders');

            // Helper function to check if a specific index exists
            $indexExists = function ($indexName) use ($indexes): bool {
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
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign('orders_client_foreign');
            $table->dropForeign('orders_product_foreign');
        });
    }
};
