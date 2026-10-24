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
        if (! Schema::hasColumn('plan_prices', 'offer_price')) {
            Schema::table('plan_prices', function (Blueprint $table): void {
                $table->string('offer_price')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_prices', function (Blueprint $table): void {
            $table->dropColumn('offer_price');
        });
    }
};
