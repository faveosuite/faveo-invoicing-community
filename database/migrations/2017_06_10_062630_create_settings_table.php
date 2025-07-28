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
        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->increments('id');
                $table->string('company')->default('Ladybird web solution');
                $table->string('website')->default('http://www.ladybirdweb.com/');
                $table->string('phone');
                $table->string('logo')->default('faveo.png');
                $table->string('address');
                $table->string('driver', 225);
                $table->string('host');
                $table->integer('port');
                $table->string('encryption');
                $table->string('email');
                $table->string('password', 255);
                $table->integer('error_log');
                $table->integer('invoice');
                $table->integer('subscription_over');
                $table->integer('subscription_going_to_end');
                $table->integer('forgot_password');
                $table->integer('order_mail');
                $table->integer('welcome_mail');
                $table->integer('download');
                $table->integer('invoice_template');
                $table->string('error_email');
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
        Schema::drop('settings');
    }
};
