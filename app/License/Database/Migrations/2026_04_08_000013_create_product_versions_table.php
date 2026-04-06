<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Product versions table (from afu_versions).
     * Product version releases for auto-update.
     */
    public function up(): void
    {
        Schema::create('product_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('product_id');
            $table->string('version_number');
            $table->string('version_install_file')->nullable();
            $table->string('version_upgrade_file')->nullable();
            $table->text('version_changelog')->nullable();
            $table->timestamp('version_date')->nullable();
            $table->timestamp('version_expire_date')->nullable();
            $table->string('version_status')->default('active');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->index(['product_id', 'version_status']);
            $table->unique(['product_id', 'version_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_versions');
    }
};
