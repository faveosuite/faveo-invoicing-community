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
        if (! Schema::hasColumn('auto_renewals', 'invoice_number')) {
            Schema::table('auto_renewals', function (Blueprint $table): void {
                $table->string('invoice_number')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auto_renewals', function (Blueprint $table): void {
            $table->dropColumn('invoice_number');
        });
    }
};
