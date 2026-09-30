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
            $table->boolean('non_authenticated')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_scripts', function (Blueprint $table): void {
            $table->dropColumn('non_authenticated');
        });
    }
};
