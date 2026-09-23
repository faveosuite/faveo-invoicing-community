<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tracks failed license verify/install attempts per IP (from the original
     * product's apl_failed_licensings), so LicenseValidator::recordFailedLicensing()
     * can auto-ban an IP once it crosses the failed-attempt threshold.
     */
    public function up(): void
    {
        Schema::create('license_failed_licensings', function (Blueprint $table): void {
            $table->id();
            $table->string('failed_licensing_ip')->unique();
            $table->unsignedInteger('failed_licensing_attempts')->default(1);
            $table->date('failed_licensing_last_attempt_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_failed_licensings');
    }
};
