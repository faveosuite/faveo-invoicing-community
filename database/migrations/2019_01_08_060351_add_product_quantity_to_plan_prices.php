<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plan_prices', function (Blueprint $table) {
            if (! Schema::hasColumn('plan_prices', 'price_description')) {
                $table->string('price_description', 225)->nullable();
            }
            if (! Schema::hasColumn('plan_prices', 'product_quantity')) {
                $table->string('product_quantity')->nullable();
            }
            if (! Schema::hasColumn('plan_prices', 'no_of_agents')) {
                $table->string('no_of_agents')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plan_prices', function (Blueprint $table) {
            $table->dropColumn([
                'price_description',
                'product_quantity',
                'no_of_agents',
            ]);
        });
    }
};
