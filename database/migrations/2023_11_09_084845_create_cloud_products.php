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
        Schema::create('cloud_products', function (Blueprint $table): void {
            $table->id();
            $table->integer('cloud_product')->unsigned();
            $table->integer('cloud_free_plan')->unsigned();
            $table->foreign('cloud_product')->references('id')->on('products');
            $table->foreign('cloud_free_plan')->references('id')->on('plans');
            $table->string('cloud_product_key');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_products');
    }
};
