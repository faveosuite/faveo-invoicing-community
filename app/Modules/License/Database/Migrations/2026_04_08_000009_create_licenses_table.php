<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Licenses table (from afl_licenses).
     * Core license records with foreign keys to products and users.
     */
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id');
            $table->string('license_code')->index();
            $table->string('license_order_number')->nullable();
            $table->string('license_ip')->nullable();
            $table->string('license_domain')->nullable();
            $table->boolean('license_require_domain')->default(false);
            $table->integer('license_limit')->default(1);
            $table->timestamp('license_date')->nullable();
            $table->timestamp('license_cancel_date')->nullable();
            $table->timestamp('license_expire_date')->nullable();
            $table->timestamp('license_updates_date')->nullable();
            $table->timestamp('license_support_date')->nullable();
            $table->text('license_comments')->nullable();
            $table->string('license_status')->default('active');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['license_code', 'license_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
