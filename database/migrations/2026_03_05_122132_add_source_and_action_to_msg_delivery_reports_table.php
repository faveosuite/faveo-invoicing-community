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
            $table->dropUnique(['request_id']);
            $table->index('request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('msg_delivery_reports', function (Blueprint $table) {
            $table->dropColumn(['source', 'action']);
            $table->dropIndex(['request_id']);
            $table->unique('request_id');
        });
    }
};
