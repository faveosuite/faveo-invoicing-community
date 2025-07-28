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
        if (! Schema::hasColumn('expiry_mail_days', 'invoice_days')) {
            Schema::table('expiry_mail_days', function (Blueprint $table) {
                $table->string('invoice_days')->default(2);
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
            $table->dropColumn('invoice_days');
        });
    }
};
