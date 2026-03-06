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
        Schema::table('msg_delivery_reports', function (Blueprint $table) {
            $table->string('source')->nullable()->after('country_iso');
            $table->string('action')->nullable()->after('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('msg_delivery_reports', function (Blueprint $table) {
            $table->dropColumn(['source', 'action']);
        });
    }
};
