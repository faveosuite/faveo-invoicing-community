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
        if (! Schema::hasTable('order_invoice_relations')) {
            Schema::create('order_invoice_relations', function (Blueprint $table): void {
                $table->increments('id');
                $table->integer('order_id')->unsigned()->index('order_invoice_relations_order_id_foreign');
                $table->integer('invoice_id')->unsigned()->index('order_invoice_relations_invoice_id_foreign');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('order_invoice_relations');
    }
};
