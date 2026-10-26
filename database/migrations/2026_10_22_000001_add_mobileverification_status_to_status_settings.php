<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Splits "verify users' mobile numbers" (Contact Options) from "MSG91 is on" (third-party
 * integration), which until now shared msg91_status — so switching Mobile on in Contact Options
 * re-activated a deactivated MSG91. Mirrors emailverification_status for email.
 * Left null here: the v4.0.2.7.RC.1 seeder fills it from msg91_status, so upgrades keep their behaviour.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('status_settings', function (Blueprint $table): void {
            $table->boolean('mobileverification_status')->nullable()->after('msg91_status');
        });
    }

    public function down(): void
    {
        Schema::table('status_settings', function (Blueprint $table): void {
            $table->dropColumn('mobileverification_status');
        });
    }
};
