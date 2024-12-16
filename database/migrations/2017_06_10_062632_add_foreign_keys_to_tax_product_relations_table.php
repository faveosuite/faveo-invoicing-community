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
        Schema::table('tax_product_relations', function (Blueprint $table) {
            // Fetch all indexes for the tax_product_relations table
            $indexes = DB::select("SHOW INDEX FROM tax_product_relations");

            // Helper function to check if a specific index exists
            $indexExists = function ($indexName) use ($indexes) {
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
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tax_product_relations', function (Blueprint $table) {
            $table->dropForeign('tax_product_relations_product_id_foreign');
            $table->dropForeign('tax_product_relations_tax_id_foreign');
        });
    }
};
