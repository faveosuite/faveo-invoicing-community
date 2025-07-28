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
        if (! Schema::hasColumn('free_trail_expired', 'Free_trail_gonna_expired')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->string('free_trail_expired')->nullable();
                $table->string('Free_trail_gonna_expired')->nullable();
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
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('free_trail_expired');
            $table->dropColumn('Free_trail_gonna_expired');
        });
    }
};
