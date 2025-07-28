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
        if (! Schema::hasTable('states_subdivisions')) {
            Schema::create('states_subdivisions', function (Blueprint $table) {
                $table->smallInteger('state_subdivision_id')->unsigned()->primary();
                $table->string('country_code_char2', 2);
                $table->string('country_code_char3', 3);
                $table->string('state_subdivision_name', 80)->nullable();
                $table->string('state_subdivision_alternate_names', 200)->nullable();
                $table->string('primary_level_name', 80)->nullable();
                $table->string('state_subdivision_code', 50);
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
        Schema::drop('states_subdivisions');
    }
};
