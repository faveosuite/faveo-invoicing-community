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
        if (! Schema::hasTable('paypal')) {
            Schema::create('paypal', function (Blueprint $table) {
                $table->increments('id');
                $table->string('business');
                $table->string('cmd');
                $table->string('currencies', 225);
                $table->string('paypal_url');
                $table->string('image_url');
                $table->string('success_url');
                $table->string('cancel_url');
                $table->string('notify_url');
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
        Schema::drop('paypal');
    }
};
