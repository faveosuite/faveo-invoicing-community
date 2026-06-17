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
        Schema::table('tax_product_relations', function (Blueprint $table): void {
            // Fetch all indexes for the tax_product_relations table
            $indexes = DB::select('SHOW INDEX FROM tax_product_relations');

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
            if (! $indexExists('tax_product_relations_product_id_foreign')) {
                $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->onUpdate('RESTRICT')
                    ->onDelete('RESTRICT');
            }

            // Check and add foreign key for tax_class_id
            if (! $indexExists('tax_product_relations_tax_class_id_foreign')) {
                $table->foreign('tax_class_id')
                    ->references('id')
                    ->on('tax_classes')
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
        Schema::table('tax_product_relations', function (Blueprint $table): void {
            $table->dropForeign('tax_product_relations_product_id_foreign');
            $table->dropForeign('tax_product_relations_tax_id_foreign');
        });
    }
};
