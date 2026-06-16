<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_keys', function (Blueprint $table): void {
            $table->string('stripe_webhook_secret')->nullable()->after('stripe_secret');
            $table->string('razorpay_webhook_secret')->nullable()->after('rzp_secret');
        });
    }

    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table): void {
            $table->dropColumn(['stripe_webhook_secret', 'razorpay_webhook_secret']);
        });
    }
};
