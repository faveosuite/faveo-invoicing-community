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
        Schema::table('prices', function (Blueprint $table): void {
            // Fetch all indexes for the prices table
            $indexes = DB::select('SHOW INDEX FROM prices');

            // Helper function to check if a specific index exists
            $indexExists = function ($indexName) use ($indexes): bool {
                foreach ($indexes as $index) {
                    if ($index->Key_name === $indexName) {
                        return true;
                    }
                }

                return false;
            };

            // Check and add foreign key for product_id
            if (! $indexExists('prices_product_id_foreign')) {
                $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prices', function (Blueprint $table): void {
            $table->dropForeign('prices_product_id_foreign');
        });
    }
};
