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
        Schema::create('recaptcha_settings', function (Blueprint $table) {
            $table->id();

            // Keys
            $table->string('v2_site_key')->nullable();
            $table->string('v2_secret_key')->nullable();
            $table->string('v3_site_key')->nullable();
            $table->string('v3_secret_key')->nullable();

            // Core config
            $table->enum('captcha_version', ['v2_checkbox', 'v2_invisible', 'v3_invisible']);
            $table->enum('failover_action', ['none', 'v2_checkbox']);
            $table->decimal('score_threshold', 2, 1)->default(0.5);

            // UI config
            $table->string('error_message')->default('Please solve the CAPTCHA to proceed');
            $table->enum('theme', ['light', 'dark'])->default('light');
            $table->enum('size', ['normal', 'compact'])->default('normal');
            $table->enum('badge_position', ['bottomright', 'bottomleft', 'inline'])->default('bottomright');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recaptcha_settings');
    }
};
