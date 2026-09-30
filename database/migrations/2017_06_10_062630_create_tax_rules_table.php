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
        if (! Schema::hasTable('tax_rules')) {
            Schema::create('tax_rules', function (Blueprint $table): void {
                $table->increments('id');
                $table->integer('tax_enable');
                $table->integer('inclusive');
                $table->integer('shop_inclusive');
                $table->integer('cart_inclusive');
                $table->integer('rounding');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('tax_rules');
    }
};
