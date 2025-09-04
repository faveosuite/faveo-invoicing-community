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
        Schema::table('status_settings', function (Blueprint $table) {
            $table->dropColumn(['v3_recaptcha_status', 'v3_v2_recaptcha_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('status_settings', function (Blueprint $table) {
            $table->boolean('v3_recaptcha_status')->nullable();
            $table->boolean('v3_v2_recaptcha_status')->nullable();
        });
    }
};
