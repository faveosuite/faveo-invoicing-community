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
        if (! Schema::hasColumn('status_settings', 'cloud_mail_status')) {
            Schema::table('status_settings', function (Blueprint $table): void {
                $table->boolean('cloud_mail_status')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('status_settings', function (Blueprint $table): void {
            $table->dropColumn('cloud_mail_status');
        });
    }
};
