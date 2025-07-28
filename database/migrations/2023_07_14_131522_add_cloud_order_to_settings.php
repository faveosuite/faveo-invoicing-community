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
        if (! Schema::hasColumn('cloud_order', 'cloud_deleted', 'from_name')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->string('cloud_order')->nullable();
                $table->string('cloud_deleted')->nullable();
                $table->string('from_name')->nullable();
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
            $table->dropColumn('cloud_order');
            $table->dropColumn('cloud_deleted');
            $table->dropColumn('from_name');
        });
    }
};
