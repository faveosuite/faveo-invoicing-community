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
        if (! Schema::hasColumn('free_trail_expired', 'Free_trail_gonna_expired')) {
            Schema::table('settings', function (Blueprint $table): void {
                $table->string('free_trail_expired')->nullable();
                $table->string('Free_trail_gonna_expired')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn('free_trail_expired');
            $table->dropColumn('Free_trail_gonna_expired');
        });
    }
};
