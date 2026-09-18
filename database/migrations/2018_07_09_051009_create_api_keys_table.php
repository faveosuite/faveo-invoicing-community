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
        if (! Schema::hasTable('api_keys')) {
            Schema::create('api_keys', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('rzp_key', 255)->nullable();
                $table->string('rzp_secret', 255)->nullable();
                $table->string('apilayer_key', 255)->nullable();
                $table->string('bugsnag_api_key', 255)->nullable();
                $table->string('zoho_api_key', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
