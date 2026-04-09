<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Version installations table (from afu_installations).
     * Update installation tracking with FKs to products, users, and product_versions.
     */
    public function up(): void
    {
        Schema::create('version_installations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('version_id');
            $table->timestamp('installation_date')->nullable();
            $table->string('installation_status')->default('active');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('version_id')->references('id')->on('product_versions')->onDelete('cascade');
            $table->index(['product_id', 'user_id', 'version_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('version_installations');
    }
};
