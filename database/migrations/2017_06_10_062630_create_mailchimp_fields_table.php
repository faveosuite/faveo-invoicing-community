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
        if (! Schema::hasTable('mailchimp_fields')) {
            Schema::create('mailchimp_fields', function (Blueprint $table) {
                $table->increments('id');
                $table->string('list_id');
                $table->string('merge_id')->unique('merge_id');
                $table->string('name');
                $table->string('tag', 225);
                $table->string('type');
                $table->string('options');
                $table->string('required', 225);
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
        Schema::drop('mailchimp_fields');
    }
};
