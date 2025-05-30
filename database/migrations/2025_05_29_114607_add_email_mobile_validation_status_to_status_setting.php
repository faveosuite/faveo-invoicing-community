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
        Schema::table('status_setting', function (Blueprint $table) {
            Schema::table('status_settings', function (Blueprint $table) {
                $table->boolean('email_validation_status')->default(0)->after('v3_recaptcha_status');
                $table->boolean('mobile_validation_status')->default(0)->after('email_validation_status');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('status_setting', function (Blueprint $table) {
            $table->dropColumn('email_validation_status');
            $table->dropColumn('mobile_validation_status');
        });
    }
};
