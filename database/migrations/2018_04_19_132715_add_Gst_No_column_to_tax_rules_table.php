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
        if (! Schema::hasColumn('tax_rules', 'Gst_No')) {
            Schema::table('tax_rules', function (Blueprint $table): void {
                $table->string('Gst_No');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_rules', function (Blueprint $table): void {
            $table->dropColumn('Gst_No');
        });
    }
};
