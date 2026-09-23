<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Single-row auto-ban settings for the license server: an on/off toggle plus
     * the failed-attempts threshold, mirroring the original product's admin-
     * configurable BANNED_HOSTS / FAILED_LICENSINGS_LIMIT (both off by default).
     */
    public function up(): void
    {
        Schema::create('license_security_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('auto_ban_enabled')->default(0);
            $table->unsignedTinyInteger('failed_licensings_limit')->default(0);
            $table->timestamps();
        });

        DB::table('license_security_settings')->insert([
            'auto_ban_enabled' => 0,
            'failed_licensings_limit' => 0,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_security_settings');
    }
};
