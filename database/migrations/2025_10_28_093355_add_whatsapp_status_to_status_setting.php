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
        Schema::table('status_settings', function (Blueprint $table): void {
            $table->boolean('whatsapp_status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('status_settings', function (Blueprint $table): void {
            $table->dropColumn('whatsapp_status');
        });
    }
};
