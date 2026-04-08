<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Version callbacks table (from afu_callbacks).
     * Update callback logs with foreign keys to products and product_versions.
     */
    public function up(): void
    {
        Schema::create('version_callbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('version_id');
            $table->string('callback_type');
            $table->string('callback_ip')->nullable();
            $table->string('callback_path')->nullable();
            $table->timestamp('callback_date_time')->useCurrent();
            $table->string('callback_status')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('version_id')->references('id')->on('product_versions')->onDelete('cascade');
            $table->index(['product_id', 'callback_date_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('version_callbacks');
    }
};
