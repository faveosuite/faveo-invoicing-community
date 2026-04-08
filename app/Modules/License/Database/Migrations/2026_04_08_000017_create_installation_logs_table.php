<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Installation logs table (from installation_logs).
     * Detailed installation activity logs indexed by license_code.
     */
    public function up(): void
    {
        Schema::create('installation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('license_code')->index();
            $table->text('log_data')->nullable();
            $table->string('log_type')->nullable();
            $table->timestamp('log_date_time')->useCurrent();
            $table->timestamps();
            $table->index(['license_code', 'log_date_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installation_logs');
    }
};
