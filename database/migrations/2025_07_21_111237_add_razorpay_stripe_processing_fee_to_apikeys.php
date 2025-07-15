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
        Schema::table('api_keys', function (Blueprint $table) {
            $table->integer('razorpay_processing_fee')->default(0);
            $table->integer('stripe_processing_fee')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropColumn('razorpay_processing_fee');
            $table->dropColumn('stripe_processing_fee');
        });
    }
};
