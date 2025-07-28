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
        if (! Schema::hasTable('tax_by_states')) {
            Schema::create('tax_by_states', function (Blueprint $table) {
                $table->increments('id');
                $table->string('state');
                $table->string('c_gst');
                $table->string('s_gst');
                $table->string('i_gst');
                $table->string('ut_gst');

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
        Schema::dropIfExists('tax_by_states');
    }
};
