<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('status_settings', function (Blueprint $table) {
            $table->boolean('installation_logs_status')->default(0);
            $table->boolean('license_reports_cleanup_status')->default(0);
            $table->boolean('license_callbacks_cleanup_status')->default(0);
            $table->boolean('license_crack_reports_cleanup_status')->default(0);
            $table->boolean('license_system_reports_cleanup_status')->default(0);
            $table->boolean('license_versions_cleanup_status')->default(0);
        });

        // Seed default schedule conditions
        $jobs = [
            'installationLogs' => 'everyMinute',
            'licenseReportsCleanup' => 'daily',
            'licenseCallbacksCleanup' => 'daily',
            'licenseCrackReportsCleanup' => 'daily',
            'licenseSystemReportsCleanup' => 'daily',
            'licenseVersionsCleanup' => 'daily',
        ];

        foreach ($jobs as $job => $condition) {
            DB::table('conditions')->insertOrIgnore([
                'job' => $job,
                'value' => $condition,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('status_settings', function (Blueprint $table) {
            $table->dropColumn([
                'installation_logs_status',
                'license_reports_cleanup_status',
                'license_callbacks_cleanup_status',
                'license_crack_reports_cleanup_status',
                'license_system_reports_cleanup_status',
                'license_versions_cleanup_status',
            ]);
        });

        DB::table('conditions')->whereIn('job', [
            'installationLogs',
            'licenseReportsCleanup',
            'licenseCallbacksCleanup',
            'licenseCrackReportsCleanup',
            'licenseSystemReportsCleanup',
            'licenseVersionsCleanup',
        ])->delete();
    }
};
