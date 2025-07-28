<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('api_keys', function (Blueprint $table) {
            if (! Schema::hasColumn('api_keys', 'license_client_id')) {
                $table->string('license_client_id', 255)->nullable();
            }
            if (! Schema::hasColumn('api_keys', 'license_client_secret')) {
                $table->string('license_client_secret', 255)->nullable();
            }
            if (! Schema::hasColumn('api_keys', 'license_grant_type')) {
                $table->string('license_grant_type', 255)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropColumn(['license_client_id', 'license_client_secret', 'license_grant_type']);
        });
    }
};
