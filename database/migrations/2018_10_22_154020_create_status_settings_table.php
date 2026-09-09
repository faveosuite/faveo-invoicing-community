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
        if (! Schema::hasTable('status_settings')) {
            Schema::create('status_settings', function (Blueprint $table): void {
                $table->increments('id');
                $table->boolean('expiry_mail');
                $table->boolean('activity_log_delete');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_settings');
    }
};
