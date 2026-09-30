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
        Schema::table('chat_scripts', function (Blueprint $table): void {
            $table->boolean('on_registration')->default(0);
            $table->boolean('on_every_page')->default(1);
            $table->boolean('google_analytics')->default(1);
            $table->string('google_analytics_tag')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_scripts', function (Blueprint $table): void {
            $table->dropColumn('on_registration');
            $table->dropColumn('on_every_page');
            $table->dropColumn('google_analytics');
            $table->dropColumn('google_analytics_tag');
        });
    }
};
