<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_scripts', function (Blueprint $table): void {
            $table->dropColumn(['google_analytics', 'google_analytics_tag']);
        });
    }

    public function down(): void
    {
        Schema::table('chat_scripts', function (Blueprint $table): void {
            $table->boolean('google_analytics')->default(1);
            $table->string('google_analytics_tag')->nullable();
        });
    }
};
