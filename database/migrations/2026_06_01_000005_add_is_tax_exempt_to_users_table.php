<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-customer tax exemption. When set, the engine short-circuits to zero tax
 * for that user (e.g. a validated GSTIN/VAT number). The existing `gstin`
 * column on users serves as the customer's tax number.
 */
return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('users', 'is_tax_exempt')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_tax_exempt')->default(false)->after('gstin');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'is_tax_exempt')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_tax_exempt');
            });
        }
    }
};
