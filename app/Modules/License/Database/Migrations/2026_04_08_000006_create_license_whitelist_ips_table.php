<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * License whitelist IPs table (from afl_whitelist_ips).
     * IP whitelist for license verification.
     */
    public function up(): void
    {
        Schema::create('license_whitelist_ips', function (Blueprint $table) {
            $table->id();
            $table->string('whitelist_host_ip');
            $table->timestamps();
            $table->unique('whitelist_host_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_whitelist_ips');
    }
};
