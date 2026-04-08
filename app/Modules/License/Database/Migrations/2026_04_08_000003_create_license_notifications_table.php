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
        Schema::create('license_notifications', function (Blueprint $table) {
            $table->id();
            $table->text('notification_product_not_found')->nullable();
            $table->text('notification_license_ok')->nullable();
            $table->text('notification_license_not_found')->nullable();
            $table->text('notification_license_expired')->nullable();
            $table->text('notification_license_suspended')->nullable();
            $table->text('notification_license_limit_exceeded')->nullable();
            $table->text('notification_installation_ok')->nullable();
            $table->text('notification_installation_failed')->nullable();
            $table->text('notification_updates_ok')->nullable();
            $table->text('notification_updates_not_found')->nullable();
            $table->text('notification_support_expired')->nullable();
            $table->text('notification_domain_mismatch')->nullable();
            $table->text('notification_ip_mismatch')->nullable();
            $table->text('notification_invalid_request')->nullable();
            $table->text('notification_banned_host')->nullable();
            $table->text('notification_connection_ok')->nullable();
            $table->text('notification_connection_failed')->nullable();
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
