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
        if (! Schema::hasColumn('expiry_mail_days', 'cloud_days')) {
            Schema::table('expiry_mail_days', function (Blueprint $table): void {
                $table->string('cloud_days')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expiry_mail_days', function (Blueprint $table): void {
            $table->dropColumn('cloud_days');
        });
    }
};
