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
            if (! Schema::hasColumn('api_keys', 'msg91_auth_key')) {
                $table->string('msg91_auth_key', 255)->nullable();
            }
            if (! Schema::hasColumn('api_keys', 'msg91_sender')) {
                $table->string('msg91_sender', 50)->nullable();
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
            $table->dropColumn('msg91_auth_key');
            $table->dropColumn('msg91_sender');
        });
    }
};
