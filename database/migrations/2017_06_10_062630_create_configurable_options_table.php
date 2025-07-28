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
        if (! Schema::hasTable('configurable_options')) {
            Schema::create('configurable_options', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('group_id')->unsigned()->index('configurable_options_group_id_foreign');
                $table->integer('type');
                $table->string('title');
                $table->string('options');
                $table->integer('price');
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
        Schema::drop('configurable_options');
    }
};
