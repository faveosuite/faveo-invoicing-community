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
        if (! Schema::hasColumn('autorenewal_days', 'postexpiry_days')) {
            Schema::table('expiry_mail_days', function (Blueprint $table) {
                $table->string('autorenewal_days')->nullable();
                $table->string('postexpiry_days')->nullable();
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
            $table->dropColumn('autorenewal_days');
            $table->dropColumn('postexpiry_days');
        });
    }
};
