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
            if (! Schema::hasColumn('api_keys', 'update_api_url')) {
                $table->string('update_api_url', 255)->nullable();
            }
            if (! Schema::hasColumn('api_keys', 'update_api_secret')) {
                $table->string('update_api_secret', 255)->nullable();
            }
            if (! Schema::hasColumn('api_keys', 'terms_url')) {
                $table->string('terms_url', 255)->nullable();
            }
            if (! Schema::hasColumn('api_keys', 'pipedrive_api_key')) {
                $table->string('pipedrive_api_key', 255)->nullable();
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
            $table->dropColumn(['update_api_url', 'update_api_secret', 'terms_url', 'pipedrive_api_key']);
        });
    }
};
