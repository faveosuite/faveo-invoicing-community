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
        if (! Schema::hasColumn('status_settings', 'use_streams')) {
            Schema::table('status_settings', function (Blueprint $table) {
                $table->boolean('use_streams')->default(0)->after('license_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('status_settings', function (Blueprint $table) {
            $table->dropColumn('use_streams');
        });
    }
};
