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
        if (! Schema::hasTable('plans_periods_relation')) {
            Schema::create('plans_periods_relation', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('plan_id');
                $table->unsignedInteger('period_id');

                $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');
                $table->foreign('period_id')->references('id')->on('periods')->onDelete('cascade');
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
        Schema::dropIfExists('plans_periods_relation');
    }
};
