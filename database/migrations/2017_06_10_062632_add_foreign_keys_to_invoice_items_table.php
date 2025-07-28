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
        Schema::table('invoice_items', function (Blueprint $table) {
            // Get all indexes for the invoice_items table
            $indexes = DB::select('SHOW INDEX FROM invoice_items');

            // Check if the specific foreign key already exists
            $foreignKeyExists = false;
            foreach ($indexes as $index) {
                if ($index->Key_name === 'invoice_items_invoice_id_foreign') {
                    $foreignKeyExists = true;
                    break;
                }
            }

            // If the foreign key does not exist, add it
            if (! $foreignKeyExists) {
                $table->foreign('invoice_id')
                    ->references('id')
                    ->on('invoices')
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
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign('invoice_items_invoice_id_foreign');
        });
    }
};
