<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expiry_mail_days', function (Blueprint $table) {
            $table->integer('installation_logs_expire_days')->nullable();
            $table->integer('license_reports_cleanup_days')->nullable();
            $table->integer('license_callbacks_cleanup_days')->nullable();
            $table->integer('license_crack_reports_cleanup_days')->nullable();
            $table->integer('license_system_reports_cleanup_days')->nullable();
            $table->integer('license_versions_cleanup_days')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('expiry_mail_days', function (Blueprint $table) {
            $table->dropColumn([
                'installation_logs_expire_days',
                'license_reports_cleanup_days',
                'license_callbacks_cleanup_days',
                'license_crack_reports_cleanup_days',
                'license_system_reports_cleanup_days',
                'license_versions_cleanup_days',
            ]);
        });
    }
};
