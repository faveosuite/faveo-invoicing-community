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
        Schema::table('verification_attempts', function (Blueprint $table) {
            $table->renameColumn('type', 'mobile_attempt')->nullabe();
            $table->renameColumn('attempt_count', 'email_attempt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('verification_attempts', function (Blueprint $table) {
            $table->renameColumn('mobile_attempt', 'type');
            $table->renameColumn('email_attempt', 'attempt_count');
        });
    }
};
