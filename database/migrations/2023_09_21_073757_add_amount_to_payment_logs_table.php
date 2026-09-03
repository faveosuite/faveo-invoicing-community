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
        if (! Schema::hasColumn('amount', 'payment_type')) {
            Schema::table('payment_logs', function (Blueprint $table): void {
                $table->string('amount')->nullable();
                $table->string('payment_type')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_logs', function (Blueprint $table): void {
            $table->dropColumn(['amount', 'payment_type']);
        });
    }
};
