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
        if (! Schema::hasTable('product_groups')) {
            Schema::create('product_groups', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->string('headline')->nullable();
                $table->string('tagline')->nullable();
                $table->string('available_payment')->nullable();
                $table->integer('hidden')->nullable();
                $table->string('cart_link')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('product_groups');
    }
};
