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
        Schema::table('api_keys', function (Blueprint $table): void {
            if (! Schema::hasColumn('api_keys', 'license_api_secret')) {
                $table->string('license_api_secret', 255)->nullable();
            }

            if (! Schema::hasColumn('api_keys', 'license_api_url')) {
                $table->string('license_api_url', 255)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table): void {
            $table->dropColumn(['license_api_url', 'license_api_secret']);
        });
    }
};
