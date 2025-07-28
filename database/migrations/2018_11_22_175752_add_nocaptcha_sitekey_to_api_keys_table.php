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
        Schema::table('api_keys', function (Blueprint $table) {
            if (! Schema::hasColumn('api_keys', 'nocaptcha_sitekey')) {
                $table->string('nocaptcha_sitekey', 255)->nullable();
            }
            if (! Schema::hasColumn('api_keys', 'captcha_secretCheck')) {
                $table->string('captcha_secretCheck', 255)->nullable();
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
        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropColumn('nocaptcha_sitekey');
            $table->dropColumn('captcha_secretCheck');
        });
    }
};
