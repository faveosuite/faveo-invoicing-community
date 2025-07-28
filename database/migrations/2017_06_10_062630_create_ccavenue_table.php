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
        if (! Schema::hasTable('ccavenue')) {
            Schema::create('ccavenue', function (Blueprint $table) {
                $table->increments('id');
                $table->string('merchant_id');
                $table->string('access_code');
                $table->string('working_key');
                $table->string('currencies', 225);
                $table->string('redirect_url');
                $table->string('cancel_url');
                $table->string('ccavanue_url');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ccavenue');
    }
};
