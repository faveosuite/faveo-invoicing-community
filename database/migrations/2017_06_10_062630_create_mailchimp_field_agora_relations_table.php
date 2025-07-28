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
        if (! Schema::hasTable('mailchimp_field_agora_relations')) {
            Schema::create('mailchimp_field_agora_relations', function (Blueprint $table) {
                $table->increments('id');
                $table->string('first_name')->nullble();
                $table->string('last_name')->nullble();
                $table->string('company')->nullble();
                $table->string('mobile')->nullble();
                $table->string('address')->nullble();
                $table->string('country')->nullble();
                $table->string('town')->nullble();
                $table->string('state')->nullble();
                $table->string('zip')->nullble();
                $table->string('active', 225)->nullble();
                $table->string('role')->nullble();
                $table->string('source')->nullble();
                $table->string('is_paid_yes')->nullble();
                $table->string('is_paid_no')->nullble();
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
        Schema::drop('mailchimp_field_agora_relations');
    }
};
