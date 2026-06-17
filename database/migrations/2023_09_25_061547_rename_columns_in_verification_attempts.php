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
        Schema::table('verification_attempts', function (Blueprint $table): void {
            $table->renameColumn('type', 'mobile_attempt')->nullabe();
            $table->renameColumn('attempt_count', 'email_attempt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verification_attempts', function (Blueprint $table): void {
            $table->renameColumn('mobile_attempt', 'type');
            $table->renameColumn('email_attempt', 'attempt_count');
        });
    }
};
