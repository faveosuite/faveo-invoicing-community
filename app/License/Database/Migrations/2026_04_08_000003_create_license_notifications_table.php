<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * License notifications table (from afl_notifications).
     * Response templates for license check callbacks.
     */
    public function up(): void
    {
        Schema::create('license_notifications', function (Blueprint $table): void {
            $table->id();
            $table->string('notification_product_not_found', 250)->nullable();
            $table->string('notification_product_inactive', 250)->nullable();
            $table->string('notification_license_ok', 250)->nullable();
            $table->string('notification_license_not_found', 250)->nullable();
            $table->string('notification_invalid_ip', 250)->nullable();
            $table->string('notification_invalid_domain', 250)->nullable();
            $table->string('notification_domain_required', 250)->nullable();
            $table->string('notification_domain_in_use', 250)->nullable();
            $table->string('notification_license_suspended', 250)->nullable();
            $table->string('notification_license_expired', 250)->nullable();
            $table->string('notification_updates_expired', 250)->nullable();
            $table->string('notification_support_expired', 250)->nullable();
            $table->string('notification_license_cancelled', 250)->nullable();
            $table->string('notification_license_limit', 250)->nullable();
            $table->string('notification_installation_not_found', 250)->nullable();
            $table->string('notification_invalid_signature', 250)->nullable();
            $table->string('notification_host_banned', 250)->nullable();
            $table->string('notification_unknown_error', 250)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_notifications');
    }
};
