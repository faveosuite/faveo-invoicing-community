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
        Schema::create('faveo_cloud', function (Blueprint $table) {
            $table->id();
            $table->string('cloud_central_domain')->nullable();
            $table->string('cron_server_url')->nullable();
            $table->string('cron_server_key')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('faveo_cloud');
    }
};
