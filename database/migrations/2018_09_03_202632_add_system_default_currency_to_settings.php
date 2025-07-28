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
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'default_currency')) {
                $table->string('default_currency', 255)->nullable();
            }
            if (! Schema::hasColumn('settings', 'default_symbol')) {
                $table->string('default_symbol', 255)->nullable();
            }
            if (! Schema::hasColumn('settings', 'file_storage')) {
                $table->string('file_storage', 255)->nullable();
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
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('file_storage');
            $table->dropColumn('default_symbol');
            $table->dropColumn('default_currency');
        });
    }
};
