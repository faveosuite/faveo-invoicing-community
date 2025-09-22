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
        Schema::table('expiry_mail_days', function (Blueprint $table) {
            $table->integer('system_logs_days')->nullable()->after('msg91_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expiry_mail_days', function (Blueprint $table) {
            $table->dropColumn('system_logs_days');
        });
    }
};
