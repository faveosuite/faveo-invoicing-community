<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Remove inter-app OAuth credentials from api_keys table.
     * Remove license_status toggle from status_settings table.
     */
    public function up(): void
    {
        Schema::table('api_keys', function (Blueprint $table): void {
            $table->dropColumn([
                'license_api_secret',
                'license_api_url',
                'license_client_id',
                'license_client_secret',
                'license_grant_type',
            ]);
        });

        Schema::table('status_settings', function (Blueprint $table): void {
            $table->dropColumn('license_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table): void {
            $table->string('license_api_secret')->nullable()->after('twitter_access_token');
            $table->string('license_api_url')->nullable()->after('license_api_secret');
            $table->string('license_client_id')->nullable()->after('stripe_secret');
            $table->string('license_client_secret')->nullable()->after('license_client_id');
            $table->string('license_grant_type')->nullable()->after('license_client_secret');
        });

        Schema::table('status_settings', function (Blueprint $table): void {
            $table->integer('license_status')->after('activity_log_delete');
        });
    }
};
