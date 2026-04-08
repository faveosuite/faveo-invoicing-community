<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * License plugins table (from license_plugins).
     * Plugin-license associations with FKs to licenses and products.
     */
    public function up(): void
    {
        Schema::create('license_plugins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('license_id');
            $table->unsignedBigInteger('product_id');
            $table->timestamps();

            $table->foreign('license_id')->references('id')->on('licenses')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unique(['license_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_plugins');
    }
};
