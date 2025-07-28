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
        if (! Schema::hasTable('group_features')) {
            Schema::create('group_features', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('group_id')->unsigned()->index('group_features_group_id_foreign');
                $table->string('features');
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
        Schema::drop('group_features');
    }
};
