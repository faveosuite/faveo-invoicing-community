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
        Schema::create('installation_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('license_code')->index();
            $table->string('version_number')->nullable();
            $table->string('installation_ip')->nullable();
            $table->string('installation_domain')->nullable();
            $table->timestamp('installation_last_active_date')->nullable();
            $table->boolean('installation_status')->default(0);
            $table->timestamps();
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
