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
        Schema::table('api_keys', function (Blueprint $table): void {
            if (! Schema::hasColumn('api_keys', 'nocaptcha_sitekey')) {
                $table->string('nocaptcha_sitekey', 255)->nullable();
            }

            if (! Schema::hasColumn('api_keys', 'captcha_secretCheck')) {
                $table->string('captcha_secretCheck', 255)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table): void {
            $table->dropColumn('nocaptcha_sitekey');
            $table->dropColumn('captcha_secretCheck');
        });
    }
};
