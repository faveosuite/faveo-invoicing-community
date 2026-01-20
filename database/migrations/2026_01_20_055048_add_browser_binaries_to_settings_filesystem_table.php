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
        Schema::table('settings_filesystem', function (Blueprint $table) {
            $table->string('node_path')->nullable();
            $table->string('npm_path')->nullable();
            $table->string('chrome_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings_filesystem', function (Blueprint $table) {
            $table->dropColumn([
                'node_path',
                'npm_path',
                'chrome_path',
            ]);
        });
    }
};
