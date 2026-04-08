<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * License banned hosts table (from afl_banned_hosts).
     * IP ban list for license verification.
     */
    public function up(): void
    {
        Schema::create('license_banned_hosts', function (Blueprint $table) {
            $table->id();
            $table->string('banned_host_ip');
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->unique('banned_host_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_banned_hosts');
    }
};
