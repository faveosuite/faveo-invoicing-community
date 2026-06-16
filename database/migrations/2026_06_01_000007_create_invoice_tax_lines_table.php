<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Itemised tax breakdown per invoice (replaces the brittle bifurcateTax()
 * string parsing). One row per applied rate per line item, carrying enough
 * detail to display and report taxes without re-deriving them from labels.
 */
return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('invoice_tax_lines')) {
            Schema::create('invoice_tax_lines', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('invoice_id');
                $table->unsignedInteger('invoice_item_id')->nullable();
                $table->unsignedInteger('tax_rate_id')->nullable();
                $table->string('label');                       // rate name at time of sale
                $table->decimal('rate', 12, 4)->default(0);    // percentage applied
                $table->boolean('compound')->default(false);
                $table->decimal('amount', 16, 4)->default(0);  // tax amount for this line
                $table->timestamps();

                $table->index('invoice_id');
                $table->index('invoice_item_id');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('invoice_tax_lines');
    }
};
