<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reference the tax rate that produced an invoice line's tax, so it can be
 * traced/recomputed. The full itemised breakdown lives in invoice_tax_lines;
 * the existing tax_name / tax_percentage strings are kept for display and for
 * historical (frozen) invoices.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('invoice_items', 'tax_rate_id')) {
            Schema::table('invoice_items', function (Blueprint $table): void {
                $table->unsignedInteger('tax_rate_id')->nullable()->after('tax_code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('invoice_items', 'tax_rate_id')) {
            Schema::table('invoice_items', function (Blueprint $table): void {
                $table->dropColumn('tax_rate_id');
            });
        }
    }
};
