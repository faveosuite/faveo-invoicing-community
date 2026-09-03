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
        Schema::table('expiry_mail_days', function (Blueprint $table): void {
            $table->string('reoon_logs_days')->nullable()->after('invoice_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expiry_mail_days', function (Blueprint $table): void {
            $table->dropColumn('reoon_logs_days');
        });
    }
};
