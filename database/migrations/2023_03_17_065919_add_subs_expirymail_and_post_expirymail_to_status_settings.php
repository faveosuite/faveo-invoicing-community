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
        if (! Schema::hasColumn('subs_expirymail', 'post_expirymail')) {
            Schema::table('status_settings', function (Blueprint $table) {
                $table->boolean('subs_expirymail')->default(0);
                $table->boolean('post_expirymail')->default(0);
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
        Schema::table('status_settings', function (Blueprint $table) {
            $table->dropColumn(['subs_expirymail', 'post_expirymail']);
        });
    }
};
