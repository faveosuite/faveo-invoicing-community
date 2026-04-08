<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * License callbacks table (from afl_callbacks).
     * License verification event logs.
     */
    public function up(): void
    {
        Schema::create('license_callbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('license_code')->index();
            $table->string('callback_ip')->nullable();
            $table->string('callback_domain')->nullable();
            $table->timestamp('callback_date_time')->useCurrent();
            $table->string('callback_status')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['license_code', 'callback_date_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_callbacks');
    }
};
