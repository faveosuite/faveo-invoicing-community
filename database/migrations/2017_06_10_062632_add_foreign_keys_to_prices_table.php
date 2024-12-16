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
        Schema::table('prices', function (Blueprint $table) {
            // Fetch all indexes for the prices table
            $indexes = DB::select("SHOW INDEX FROM prices");

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
            if (! $indexExists('prices_product_id_foreign')) {
                $table->foreign('product_id')
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
        Schema::table('prices', function (Blueprint $table) {
            $table->dropForeign('prices_product_id_foreign');
        });
    }
};
