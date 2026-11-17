<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A Fixed Amount coupon is an amount of money, so it needs a currency: "500 off" meant in INR
 * took 500 USD off a USD cart. Percentage coupons leave it empty (they work in any currency).
 * Existing fixed coupons get the default currency in the v4.0.2.7.RC.1 seeder.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table): void {
            $table->string('currency', 10)->nullable()->after('value');
        });
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table): void {
            $table->dropColumn('currency');
        });
    }
};
