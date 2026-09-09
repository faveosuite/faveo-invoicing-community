<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Installations table (from afl_installations).
     * Where licenses are installed with foreign keys to products and users.
     */
    public function up(): void
    {
        Schema::create('installations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('license_code')->index();
            $table->string('installation_ip')->nullable();
            $table->string('installation_domain')->nullable();
            $table->string('installation_path')->nullable();
            $table->timestamp('installation_date')->nullable();
            $table->boolean('installation_status')->default(1);
            $table->string('installation_hash')->nullable();
            $table->string('version')->nullable();
            $table->boolean('installation_disable_ip_verification')->default(0);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['license_code', 'installation_domain']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installations');
    }
};
