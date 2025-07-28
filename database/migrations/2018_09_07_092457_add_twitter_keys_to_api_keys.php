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
            if (! Schema::hasColumn('api_keys', 'twitter_consumer_key')) {
                $table->string('twitter_consumer_key', 255)->nullable();
            }
            if (! Schema::hasColumn('api_keys', 'twitter_consumer_secret')) {
                $table->string('twitter_consumer_secret', 255)->nullable();
            }
            if (! Schema::hasColumn('api_keys', 'twitter_access_token')) {
                $table->string('twitter_access_token', 255)->nullable();
            }
            if (! Schema::hasColumn('api_keys', 'twitter_access_token')) {
                $table->string('access_tooken_secret', 255)->nullable();
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
            $table->dropColumn('twitter_consumer_key');
            $table->dropColumn('twitter_consumer_secret');
            $table->dropColumn('twitter_access_token');
            $table->dropColumn('access_tooken_secret');
        });
    }
};
