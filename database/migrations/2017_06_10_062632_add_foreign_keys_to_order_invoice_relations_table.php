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
        Schema::table('order_invoice_relations', function (Blueprint $table) {
            // Get all indexes for the order_invoice_relations table
            $indexes = DB::select('SHOW INDEX FROM order_invoice_relations');

            // Helper to check if a specific index exists
            $indexExists = function ($indexName) use ($indexes) {
                foreach ($indexes as $index) {
                    if ($index->Key_name === $indexName) {
                        return true;
                    }
                }

                return false;
            };

            // Check and add foreign key for invoice_id
            if (! $indexExists('order_invoice_relations_invoice_id_foreign')) {
                $table->foreign('invoice_id')
                    ->references('id')
                    ->on('invoices')
                    ->onUpdate('RESTRICT')
                    ->onDelete('RESTRICT');
            }

            // Check and add foreign key for order_id
            if (! $indexExists('order_invoice_relations_order_id_foreign')) {
                $table->foreign('order_id')
                    ->references('id')
                    ->on('orders')
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
        Schema::table('order_invoice_relations', function (Blueprint $table) {
            $table->dropForeign('order_invoice_relations_invoice_id_foreign');
            $table->dropForeign('order_invoice_relations_order_id_foreign');
        });
    }
};
