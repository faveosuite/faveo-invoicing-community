<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('status_settings', function (Blueprint $table): void {
            $table->tinyInteger('stripe_auto_renewal')->default(0)->after('subs_expirymail');
            $table->tinyInteger('razorpay_auto_renewal')->default(0)->after('stripe_auto_renewal');
        });
    }

    public function down(): void
    {
        Schema::table('status_settings', function (Blueprint $table): void {
            $table->dropColumn(['stripe_auto_renewal', 'razorpay_auto_renewal']);
        });
    }
};
