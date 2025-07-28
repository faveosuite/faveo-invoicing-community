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
        if (! Schema::hasColumn('expiry_mail_days', 'cloud_days')) {
            Schema::table('expiry_mail_days', function (Blueprint $table) {
                $table->string('cloud_days')->nullable();
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
        Schema::table('expiry_mail_days', function (Blueprint $table) {
            $table->dropColumn('cloud_days');
        });
    }
};
