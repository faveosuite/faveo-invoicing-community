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
        Schema::table('settings_filesystem', function (Blueprint $table): void {
            $table->string('chrome_path')->nullable();
            $table->string('pdf_driver')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings_filesystem', function (Blueprint $table): void {
            $table->dropColumn([
                'chrome_path',
                'pdf_driver',
            ]);
        });
    }
};
