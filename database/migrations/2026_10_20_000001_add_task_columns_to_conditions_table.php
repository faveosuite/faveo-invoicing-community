<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Makes each `conditions` row a complete cron task: what runs (command), whether it is on (active),
 * whether it runs in the background (is_background) and its day settings (days), next to how often
 * it runs (value). Until now on/off lived in one status_settings column per task and days in one
 * expiry_mail_days / activity_log_days column per task. The v4.0.2.7.RC.1 seeder copies those
 * values here and drops the old columns; that has to happen in the seeder because older seeders
 * still write the old tables on a fresh install.
 */
return new class extends Migration
{
    public function up(): void
    {
        // The unique index below needs one row per job; keep the newest.
        $keep = DB::table('conditions')->selectRaw('MAX(id) as id')->groupBy('job')->pluck('id');
        DB::table('conditions')->whereNotIn('id', $keep)->delete();

        Schema::table('conditions', function (Blueprint $table): void {
            $table->string('command')->nullable()->after('value');
            $table->boolean('active')->default(false)->after('command');
            $table->boolean('is_background')->default(false)->after('active');
            $table->json('days')->nullable()->after('is_background');
            $table->unique('job');
        });
    }

    public function down(): void
    {
        Schema::table('conditions', function (Blueprint $table): void {
            $table->dropUnique(['job']);
            $table->dropColumn(['command', 'active', 'is_background', 'days']);
        });
    }
};
