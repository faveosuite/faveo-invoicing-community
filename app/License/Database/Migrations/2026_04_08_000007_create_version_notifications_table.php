<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Version notifications table (from afu_notifications).
     * Response templates for update check callbacks.
     */
    public function up(): void
    {
        Schema::create('version_notifications', function (Blueprint $table): void {
            $table->id();
            $table->string('notification_operation_ok', 250)->nullable();
            $table->string('notification_product_not_found', 250)->nullable();
            $table->string('notification_product_inactive', 250)->nullable();
            $table->string('notification_product_no_versions', 250)->nullable();
            $table->string('notification_version_not_found', 250)->nullable();
            $table->string('notification_version_inactive', 250)->nullable();
            $table->string('notification_version_expired', 250)->nullable();
            $table->string('notification_install_limit_reached', 250)->nullable();
            $table->string('notification_upgrade_limit_reached', 250)->nullable();
            $table->string('notification_install_archive_not_found', 250)->nullable();
            $table->string('notification_install_query_not_found', 250)->nullable();
            $table->string('notification_upgrade_archive_not_found', 250)->nullable();
            $table->string('notification_upgrade_query_not_found', 250)->nullable();
            $table->string('notification_raw_install_query_not_found', 250)->nullable();
            $table->string('notification_raw_upgrade_query_not_found', 250)->nullable();
            $table->string('notification_installation_not_verified', 250)->nullable();
            $table->string('notification_invalid_parameter', 250)->nullable();
            $table->string('notification_invalid_signature', 250)->nullable();
            $table->string('notification_host_banned', 250)->nullable();
            $table->string('notification_unknown_error', 250)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('version_notifications');
    }
};
