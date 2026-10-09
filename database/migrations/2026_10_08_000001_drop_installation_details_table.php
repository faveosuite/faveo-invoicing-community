<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * installation_details was the old external license manager's "site → order" table.
 * The built-in License module never wrote it; orders now reach their installs through
 * licenses (installations for background lookups, installation_logs for the order page).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('installation_details');
    }

    public function down(): void
    {
        Schema::create('installation_details', function (Blueprint $table): void {
            $table->id();
            $table->string('installation_path')->nullable();
            $table->string('installation_ip')->nullable();
            $table->string('version')->nullable();
            $table->timestamp('last_active')->nullable();
            $table->unsignedInteger('order_id')->index();
            $table->timestamps();
        });
    }
};
