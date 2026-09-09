<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Generic, WooCommerce-style tax rates.
 *
 * A rate applies to a location (country/state, optionally narrowed by
 * postcode/city via tax_rate_locations) and a tax class. Within a single
 * location only ONE rate per `priority` is applied; `compound` rates are
 * stacked on top of the running total. The engine is intentionally agnostic
 * of any specific tax regime (no GST/VAT special-casing).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tax_rates')) {
            Schema::create('tax_rates', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('name');                          // display label e.g. "VAT", "GST", "Sales Tax"
                $table->string('country', 2)->default('');       // ISO-2, '' = applies to every country
                $table->string('state', 200)->default('');       // '' = applies to every state
                $table->decimal('rate', 12, 4)->default(0);      // percentage, e.g. 18.0000
                $table->unsignedInteger('priority')->default(1); // one rate per priority wins per location
                $table->boolean('compound')->default(value: false);     // stacks on top of other rates
                $table->string('tax_class', 200)->default('');   // slug, '' = standard class
                $table->unsignedInteger('display_order')->default(0);
                $table->boolean('active')->default(value: true);
                $table->timestamps();

                $table->index('country');
                $table->index('tax_class');
                $table->index('priority');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
