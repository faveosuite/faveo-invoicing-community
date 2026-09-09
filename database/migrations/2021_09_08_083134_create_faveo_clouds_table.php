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
        Schema::create('faveo_cloud', function (Blueprint $table): void {
            $table->id();
            $table->string('cloud_central_domain')->nullable();
            $table->string('cron_server_url')->nullable();
            $table->string('cron_server_key')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faveo_cloud');
    }
};
