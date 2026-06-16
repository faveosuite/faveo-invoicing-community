<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('countries', function (Blueprint $table) {
        //     if (! Schema::hasColumn('countries', 'currency_id')) {
        //         $table->integer('currency_id')->unsigned();

        //         $table->foreign('currency_id')->references('id')->on('currencies');
        //     }
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('countries', function (Blueprint $table) {
        //     $table->dropColumn('currency_id');
        // });
    }
};
