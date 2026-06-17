<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
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
     */
    public function down(): void
    {
        // Schema::table('countries', function (Blueprint $table) {
        //     $table->dropColumn('currency_id');
        // });
    }
};
