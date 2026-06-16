<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extend global tax settings (tax_rules / TaxOption) for the generic engine.
 *
 *  - tax_based_on:      which customer address drives rate lookup
 *                       ('billing' | 'base'). No shipping address exists in
 *                       this billing app, so 'shipping' is not offered.
 *  - round_at_subtotal: round tax once at the subtotal vs. per line.
 *
 * The existing `inclusive` flag (prices entered with tax) and `rounding`
 * (round to integer vs 2 decimals) are reused as-is.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('tax_rules', function (Blueprint $table): void {
            if (! Schema::hasColumn('tax_rules', 'tax_based_on')) {
                $table->string('tax_based_on', 20)->default('billing')->after('inclusive');
            }

            if (! Schema::hasColumn('tax_rules', 'round_at_subtotal')) {
                $table->boolean('round_at_subtotal')->default(false)->after('tax_based_on');
            }
        });
    }

    public function down()
    {
        Schema::table('tax_rules', function (Blueprint $table): void {
            foreach (['tax_based_on', 'round_at_subtotal'] as $column) {
                if (Schema::hasColumn('tax_rules', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
