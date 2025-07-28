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
        Schema::table('countries', function (Blueprint $table) {
            if (! Schema::hasColumn('countries', 'currency_code')) {
                $table->string('currency_code', 255)->nullable();
            }
            if (! Schema::hasColumn('countries', 'currency_symbol')) {
                $table->string('currency_symbol', 255)->nullable();
            }
            if (! Schema::hasColumn('countries', 'currency_name')) {
                $table->string('currency_name', 255)->nullable();
            }
            if (! Schema::hasColumn('countries', 'currency_id')) {
                $table->integer('currency_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('currency_code');
            $table->dropColumn('currency_symbol');
            $table->dropColumn('currency_name');
            $table->dropColumn('currency_id');
        });
    }
};
