<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Plans no longer set a starting product quantity: it always starts at 1, and customers change
 * it in the cart when the product allows it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('plan_prices', 'product_quantity')) {
            Schema::table('plan_prices', fn (Blueprint $table) => $table->dropColumn('product_quantity'));
        }
    }

    public function down(): void
    {
        Schema::table('plan_prices', fn (Blueprint $table) => $table->string('product_quantity')->nullable());
    }
};
