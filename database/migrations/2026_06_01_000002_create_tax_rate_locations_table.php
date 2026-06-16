<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional postcode/city narrowing for a tax rate (WooCommerce model).
 *
 * A rate with no rows here applies to the whole country/state. Rows of
 * location_type 'postcode' support wildcards ("12*") and ranges
 * ("12000...12999"); 'city' rows match an exact (case-insensitive) city.
 */
return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('tax_rate_locations')) {
            Schema::create('tax_rate_locations', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('tax_rate_id');
                $table->string('location_code', 200);
                $table->string('location_type', 40); // 'postcode' | 'city'
                $table->timestamps();

                $table->index('tax_rate_id');
                $table->index(['location_type', 'location_code']);
                $table->foreign('tax_rate_id')
                    ->references('id')->on('tax_rates')
                    ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('tax_rate_locations');
    }
};
