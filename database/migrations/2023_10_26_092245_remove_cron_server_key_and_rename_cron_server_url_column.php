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
        Schema::table('faveo_cloud', function (Blueprint $table) {
            $table->dropColumn('cron_server_key');
            $table->renameColumn('cron_server_url', 'cloud_cname');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('faveo_cloud', function (Blueprint $table) {
            $table->string('cron_server_key')->nullable(); // Define the column as needed
            $table->renameColumn('cloud_cname', 'cron_server_url'); // Reverse the column rename
        });
    }
};
