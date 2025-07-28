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
        if (! Schema::hasTable('githubs')) {
            Schema::create('githubs', function (Blueprint $table) {
                $table->increments('id');
                $table->string('client_id')->nullable();
                $table->string('client_secret')->nullable();
                $table->string('username')->nullable();
                $table->string('password', 255)->nullable();
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
        Schema::drop('githubs');
    }
};
