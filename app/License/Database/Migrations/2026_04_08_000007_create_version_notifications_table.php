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
     * Response templates for update checks.
     */
    public function up(): void
    {
        Schema::create('version_notifications', function (Blueprint $table) {
            $table->id();
            $table->text('notification_version_ok')->nullable();
            $table->text('notification_version_not_found')->nullable();
            $table->text('notification_update_available')->nullable();
            $table->text('notification_no_update')->nullable();
            $table->text('notification_update_failed')->nullable();
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
        Schema::dropIfExists('version_notifications');
    }
};
